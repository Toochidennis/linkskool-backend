<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Academics\Level;
use V3\App\Models\Portal\Payments\Transaction;

class StudentPaymentService
{
    private Transaction $transaction;
    private Level $level;

    public function __construct(\PDO $pdo)
    {
        $this->transaction = new Transaction($pdo);
        $this->level = new Level($pdo);
    }

    public function getInvoiceAndTransactionHistory(int $studentId): array
    {
        $formatted = [];
        $levelNames = [];

        $transactions = $this->transaction
            ->select([
                'tid AS id',
                'trans_type AS type',
                'ref AS reference',
                'cref AS reg_no',
                'cid AS student_id',
                'description',
                'name',
                'amount',
                'amount_due',
                'date',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
                'status',
            ])
            ->where('cid', '=', $studentId)
            ->where('approved', '=', 1)
            ->orderBy(['year' => 'DESC', 'term' => 'DESC'])
            ->get();

        $levels = $this->level
            ->select(['id', 'level_name'])
            ->get();

        foreach ($levels as $level) {
            $levelNames[$level['id']] = $level['level_name'];
        }

        foreach ($transactions as $trans) {
            $type = $trans['type'];
            $levelId = $trans['level_id'];
            $levelName = $levelNames[$levelId] ?? 'Unknown Level';

            if ($type === 'invoice') {
                $invoiceDetails = json_decode($trans['description'], true);
                $formatted[$type][] = [
                    'id' => $trans['id'],
                    'invoice_details' => $invoiceDetails,
                    'amount' => $trans['amount_due'],
                    'year' => $trans['year'],
                    'term' => $trans['term'],
                ];
            } else {
                unset($trans['type']);
                unset($trans['amount_due']);
                $formatted['payments'][] = [
                    ...$trans,
                    'level_name' => $levelName,
                ];
            }
        }

        return $formatted;
    }

    public function addPayment(array $data): bool
    {
        $status = 0;

        if ($data['type'] === 'offline') {
            $status = 1;
        } else {
            $verify = $this->verifyPayment($data['reference']);

            if (!$verify['success']) {
                $status = 0;
            } else {
                $txStatus = $verify['status'];

                if ($txStatus === 'success') {
                    $status = 1;
                } elseif (in_array($txStatus, ['failed', 'abandoned'])) {
                    $status = 0;
                    // keep defaults
                } elseif ($txStatus === 'pending') {
                    $_SESSION['pending_payments'][$data['reference']] = [
                        'timestamp' => time(),
                        'data' => $data,
                    ];
                }
            }
        }

        // Always create receipt (even if failed)
        $description = 'School Fees Receipt for ' . $data['year'] . ' ' . $data['term'] . ' term';

        $payload = [
            'trans_type' => 'receipts',
            'memo' => $description,
            'c_type' => 1,
            'ref' => $data['reference'],
            'cid' => $data['student_id'],
            'cref' => $data['reg_no'],
            'name' => $data['name'],
            'quantity' => 1,
            'it_id' => 1,
            'amount' => $data['amount'],
            'date' => date('Y-m-d'),
            'account' => 1980,
            'account_name' => 'Income',
            'approved' => 1,
            'status' => $status,
            'sub' => 0,
            'class' => $data['class_id'],
            'level' => $data['level_id'],
            'year' => $data['year'],
            'term' => $data['term'],
        ];

        $this->transaction->insert($payload);

        // Update invoice only if successful
        if ($status === 1) {
            return $this->updateInvoice($data);
        }
        return false;
    }

    private function updateInvoice(array $data): bool
    {
        $tid = $data['invoice_id'];

        $invoice = $this->transaction
            ->select(['IFNULL(amount_due, 0) as amount_due'])
            ->where('tid', '=', $tid)
            ->first();

        if (!$invoice) {
            return false;
        }

        $amountDue = (float) $invoice['amount_due'];
        $payment   = (float) $data['amount'];

        if ($payment <= 0) {
            return false;
        }

        if ($payment > $amountDue) {
            $payment = $amountDue; // Cap payment to amount due
        }

        $remainingBalance =  $amountDue - $payment;

        if ($remainingBalance > 0) {
            return $this->transaction
                ->where('tid', '=', $tid)
                ->update([
                    'description' => json_encode($data['invoice_details']),
                    'amount_due' => $remainingBalance,
                    'net_due' => $invoice['amount_due']
                ]);
        } else {
            return $this->transaction
                ->where('tid', '=', $tid)
                ->update(['approved' => 0, 'sub' => 0]);
        }
    }

    private function verifyPayment(string $reference): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/$reference",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . getenv('PAYSTACK_SECRET_KEY'),
                "Cache-Control: no-cache",
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return [
                'success' => false,
                'message' => "cURL Error: $error",
                'status' => null
            ];
        }

        $result = json_decode($response, true);

        if (!$result['status']) {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Verification failed',
                'status' => null
            ];
        }

        return [
            'success' => true,
            'status' => $result['data']['status'], // success | failed | abandoned | pending
            'amount' => $result['data']['amount'] / 100, // Convert kobo to Naira
        ];
    }

    public function retryPendingTransactions(): void
    {
        if (!isset($_SESSION['pending_payments'])) {
            return;
        }

        foreach ($_SESSION['pending_payments'] as $reference => $pending) {
            $elapsed = time() - $pending['timestamp'];

            // Retry only after 3 minutes
            if ($elapsed < 180) {
                continue;
            }

            $data = $pending['data'];
            $verify = $this->verifyPayment($reference);

            if ($verify['success'] && $verify['status'] === 'success') {
                $this->transaction
                    ->where('ref', '=', $reference)
                    ->update([
                        'status' => 1,
                        'approved' => 1,
                    ]);

                $this->updateInvoice($data);
                unset($_SESSION['pending_payments'][$reference]);
            } elseif (in_array($verify['status'], ['failed', 'abandoned'])) {
                $this->transaction
                    ->where('ref', '=', $reference)
                    ->update([
                        'status' => 0,
                        'approved' => 1,
                    ]);
                unset($_SESSION['pending_payments'][$reference]);
            } elseif ($elapsed > 1800) {
                // Remove if pending for too long (e.g., 30 mins)
                unset($_SESSION['pending_payments'][$reference]);
            }
        }
    }
}
