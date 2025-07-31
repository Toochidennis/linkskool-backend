<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Payments\Transaction;

class StudentPaymentService
{
    private Transaction $transaction;

    public function __construct(\PDO $pdo)
    {
        $this->transaction = new Transaction($pdo);
    }

    public function getInvoiceAndTransactionHistory(int $studentId)
    {
        //tid,trans_type,ref as reference,cid as student_id,cref as reg_no,name,description,amount,date,class,level
        //,year,term FROM transactions WHERE cid='$id' AND approved=1 ORDER BY year DESC, term DESC "
        $formatted = [];
        $transactions = $this->transaction
            ->select([
                'tid AS id',
                'trans_type AS type',
                'ref AS reference',
                'cref AS reg_no',
                'description',
                'amount',
                'date',
            ])
            ->where('cid', '=', $studentId)
            ->where('approved', '=', 1)
            ->orderBy(['year' => 'DESC', 'term' => 'DESC'])
            ->get();

        foreach ($transactions as $trans) {
            $type = $trans['type'];

            if($type === 'invoice'){
                $trans['description'] = json_decode($trans['description'], true);
            }

            $formatted[$type][] = $trans;
        }
    }
}
