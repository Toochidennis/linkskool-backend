<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Academics\ClassModel;
use V3\App\Models\Portal\Academics\Level;
use V3\App\Models\Portal\Academics\SchoolSettings;
use V3\App\Models\Portal\Payments\Transaction;

class IncomeService
{
    private Transaction $transaction;
    private ClassModel $classModel;
    private Level $level;
    private SchoolSettings $schoolSettings;

    public function __construct(\PDO $pdo)
    {
        $this->transaction = new Transaction($pdo);
        $this->classModel = new ClassModel($pdo);
        $this->level = new Level($pdo);
        $this->schoolSettings = new SchoolSettings($pdo);
    }

    public function report(array $filters): array
    {
        $classes = $this->getClasses();
        $levels = $this->getLevels();
        $groupBy = $filters['group_by'] ?? null;

        if ($groupBy) {
            $rows = $this->buildFilteredQuery($filters)->get();
            $rows = $this->normalizeTransactionNames($rows);

            return [
                'summary' => [
                    'total_amount' => array_sum(array_column($rows, 'amount')),
                    'total_transactions' => \count($rows),
                    'unique_students' => \count(array_unique(array_column($rows, 'reg_no'))),
                    'average_amount' => \count($rows) > 0
                        ? round(array_sum(array_column($rows, 'amount')) / \count($rows), 2)
                        : 0,
                ],
                'chart_data' => $this->buildChartData($rows, $groupBy, $classes, $levels),
                'transactions' => $this->applyGrouping($rows, $groupBy, $classes, $levels),
                'meta' => null,
            ];
        }

        $page = max(1, (int) ($filters['page'] ?? 1));
        $limit = max(1, min(100, (int) ($filters['limit'] ?? 20)));

        $aggregate = $this->buildFilteredQuery($filters, [
            'SUM(amount_paid) AS total_amount',
            'COUNT(*) AS total_transactions',
            'COUNT(DISTINCT cref) AS unique_students',
            'AVG(amount_paid) AS average_amount',
        ])->first();

        $result = $this->buildFilteredQuery($filters)
            ->orderBy(['date' => 'DESC'])
            ->paginate($page, $limit);

        $chartRows = $this->buildFilteredQuery($filters, ['date', 'amount_paid AS amount'])
            ->orderBy(['date' => 'ASC'])
            ->get();

        $transactions = array_map(function ($row) use ($classes, $levels) {
            $row['class_name'] = $classes[$row['class_id']] ?? null;
            $row['level_name'] = $levels[$row['level_id']] ?? null;
            $row['name'] = $this->normalizeName($row['name'] ?? null);
            $row['items_paid'] = json_decode($row['description'], true) ?? [];
            unset($row['description']);
            return $row;
        }, $result['data']);

        return [
            'summary' => [
                'total_amount' => $aggregate['total_amount'] ?? 0,
                'total_transactions' => (int) ($aggregate['total_transactions'] ?? 0),
                'unique_students' => (int) ($aggregate['unique_students'] ?? 0),
                'average_amount' => round((float) ($aggregate['average_amount'] ?? 0), 2),
            ],
            'chart_data' => $this->buildChartData($chartRows, null, $classes, $levels),
            'transactions' => $transactions,
            'meta' => $result['meta'],
        ];
    }

    public function getReceiptDetail(string $reference): array
    {
        $receipt = $this->transaction
            ->select([
                'tid AS id',
                'it_id',
                'ref AS reference',
                'cref AS reg_no',
                'cid AS student_id',
                'name',
                'amount',
                'description',
                'memo',
                'date',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
                'status',
            ])
            ->where('ref', '=', $reference)
            ->where('trans_type', '=', 'receipt')
            ->first();

        if (empty($receipt)) {
            return [];
        }

        $invoiceId = (int) $receipt['it_id'];
        $receiptOut = $receipt;
        $receiptOut['name'] = $this->normalizeName($receiptOut['name'] ?? null);
        $receiptOut['items_paid'] = json_decode($receipt['description'], true) ?? [];
        unset($receiptOut['description'], $receiptOut['it_id']);

        $invoice = $this->transaction
            ->select([
                'tid AS id',
                'description',
                'amount',
                'amount_paid',
                'amount_due',
                'year',
                'term',
                'status',
            ])
            ->where('tid', '=', $invoiceId)
            ->where('trans_type', '=', 'invoice')
            ->first();

        if (empty($invoice)) {
            return [
                'receipt' => $receiptOut,
                'invoice' => null,
                'payment_history' => [],
            ];
        }

        $allItems = json_decode($invoice['description'], true) ?? [];

        $allReceipts = $this->transaction
            ->select(['tid AS id', 'ref AS reference', 'amount', 'description', 'date', 'status'])
            ->where('it_id', '=', $invoiceId)
            ->where('trans_type', '=', 'receipt')
            ->where('status', '=', 1)
            ->orderBy(['date' => 'ASC'])
            ->get();

        $paidItems = [];
        $paymentHistory = [];

        foreach ($allReceipts as $r) {
            $items = json_decode($r['description'], true) ?? [];
            foreach ($items as $item) {
                $paidItems[$item['fee_id']] = $item;
            }
            $paymentHistory[] = [
                'id' => $r['id'],
                'reference' => $r['reference'],
                'amount' => $r['amount'],
                'items_paid' => $items,
                'date' => $r['date'],
                'status' => $r['status'],
            ];
        }

        $paidItemsList = array_values($paidItems);
        $outstandingItems = array_values(
            array_filter($allItems, fn($item) => !isset($paidItems[$item['fee_id']]))
        );

        return [
            'receipt' => $receiptOut,
            'invoice' => [
                'id' => $invoice['id'],
                'invoice_details' => $allItems,
                'amount' => $invoice['amount'],
                'amount_paid' => $invoice['amount_paid'],
                'amount_due' => $invoice['amount_due'],
                'year' => $invoice['year'],
                'term' => $invoice['term'],
                'status' => $invoice['status'],
            ],
            'paid_summary' => [
                'items' => $paidItemsList,
                'total' => array_sum(array_column($paidItemsList, 'amount')),
            ],
            'outstanding_summary' => [
                'items' => $outstandingItems,
                'total' => array_sum(array_column($outstandingItems, 'amount')),
            ],
            'payment_history' => $paymentHistory,
        ];
    }

    private function buildFilteredQuery(array $filters, ?array $select = null): Transaction
    {
        $defaultSelect = [
            'tid AS id',
            'it_id',
            'ref AS reference',
            'cref AS reg_no',
            'memo',
            'description',
            'name',
            'amount',
            'date',
            'year',
            'term',
            'level AS level_id',
            'class AS class_id',
            'status',
        ];

        $query = $this->transaction
            ->select($select ?? $defaultSelect)
            ->where('status', '=', 1)
            ->where('trans_type', '=', 'receipt');

        $query = $this->applyDateFilters($query, $filters);
        $query = $this->applyOtherFilters($query, $filters);

        return $query;
    }

    private function getLevels(): array
    {
        $levels = $this->level
            ->select(['id', 'level_name'])
            ->get();

        $map = [];
        foreach ($levels as $level) {
            $map[$level['id']] = $level['level_name'];
        }

        return $map;
    }

    private function getClasses(): array
    {
        $classes = $this->classModel
            ->select(['id', 'class_name'])
            ->get();

        $map = [];
        foreach ($classes as $class) {
            $map[$class['id']] = $class['class_name'];
        }

        return $map;
    }

    private function getSchoolSettings(): array
    {
        return $this->schoolSettings
            ->select(['year', 'term'])
            ->first();
    }

    private function applyDateFilters($query, array $filters)
    {
        $reportType = $filters['report_type'];
        $customType = $filters['custom_type'] ?? null;
        $settings = $this->getSchoolSettings();

        if ($reportType === 'termly') {
            $query->where('term', '=', $settings['term']);
        } elseif ($reportType === 'session') {
            $query->where('year', '=', $settings['year']);
        } elseif ($reportType === 'monthly') {
            $month = date('m');
            $year  = date('Y');
            $query->whereRaw("MONTH(date) = ? AND YEAR(date) = ?", [$month, $year]);
        } elseif ($reportType === 'custom') {
            switch ($customType) {
                case 'today':
                    $query->where('date', '=', date('Y-m-d'));
                    break;

                case 'yesterday':
                    $query->where('date', '=', date('Y-m-d', strtotime('-1 day')));
                    break;

                case 'this_week':
                    $start = date('Y-m-d', strtotime('monday this week'));
                    $end = date('Y-m-d', strtotime('sunday this week'));
                    $query->whereBetween('date', $start, $end);
                    break;

                case 'last_week':
                    $start = date('Y-m-d', strtotime('monday last week'));
                    $end = date('Y-m-d', strtotime('sunday last week'));
                    $query->whereBetween('date', $start, $end);
                    break;

                case 'last_30_days':
                    $start = date('Y-m-d', strtotime('-30 days'));
                    $end = date('Y-m-d');
                    $query->whereBetween('date', $start, $end);
                    break;

                case 'this_month':
                    $query->whereRaw("MONTH(date) = ? AND YEAR(date) = ?", [date('m'), date('Y')]);
                    break;

                case 'last_month':
                    $lastMonth = date('m', strtotime('-1 month'));
                    $year = date('Y', strtotime('-1 month'));
                    $query->whereRaw("MONTH(date) = ? AND YEAR(date) = ?", [$lastMonth, $year]);
                    break;

                case 'range':
                    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                        $query->whereBetween('date', $filters['start_date'], $filters['end_date']);
                    }
                    break;
            }
        }

        return $query;
    }

    private function applyOtherFilters($query, array $filters)
    {
        $appliedFilters = $filters['filters'] ?? [];

        if (!empty($appliedFilters['terms'])) {
            $query->in('term', $appliedFilters['terms']);
        }

        if (!empty($appliedFilters['sessions'])) {
            $query->in('year', $appliedFilters['sessions']);
        }

        if (!empty($appliedFilters['levels'])) {
            $query->in('level', $appliedFilters['levels']);
        }

        if (!empty($appliedFilters['classes'])) {
            $query->in('class', $appliedFilters['classes']);
        }

        return $query;
    }

    private function applyGrouping(array $rows, string $groupBy, array $classes, array $levels): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            switch ($groupBy) {
                case 'class':
                    $key = $row['class_id'];
                    $name = $classes[$row['class_id']] ?? 'Unknown Class';
                    break;

                case 'level':
                    $key = $row['level_id'];
                    $name = $levels[$row['level_id']] ?? 'Unknown Level';
                    break;

                case 'month':
                    $key = date('Y-m', strtotime($row['date']));
                    $name = $this->formatDate($key);
                    break;

                default:
                    $key = null;
                    $name = null;
            }

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'id' => $key,
                    'name' => $name,
                    'total_amount' => 0,
                    'total_transactions' => 0,
                    'unique_students' => [],
                ];
            }

            $grouped[$key]['total_amount'] += $row['amount'];
            $grouped[$key]['total_transactions']++;
            $grouped[$key]['unique_students'][$row['reg_no']] = true;
        }

        foreach ($grouped as &$g) {
            $g['unique_students'] = \count($g['unique_students']);
        }

        return array_values($grouped);
    }

    private function buildChartData(array $rows, ?string $groupBy, array $classes, array $levels): array
    {
        $chart = [];

        if ($groupBy) {
            foreach ($rows as $row) {
                switch ($groupBy) {
                    case 'class':
                        $key = $classes[$row['class_id']] ?? 'Unknown Class';
                        break;
                    case 'level':
                        $key = $levels[$row['level_id']] ?? 'Unknown Level';
                        break;
                    case 'month':
                        $key = $this->formatDate(date('Y-m', strtotime($row['date'])));
                        break;
                    default:
                        $key = 'Unknown';
                }

                $chart[$key] = ($chart[$key] ?? 0) + $row['amount'];
            }

            return array_map(
                fn($x, $y) => ['x' => $x, 'y' => $y],
                array_keys($chart),
                $chart
            );
        }

        foreach ($rows as $row) {
            $date = date('Y-m-d', strtotime($row['date']));
            $chart[$date] = ($chart[$date] ?? 0) + $row['amount'];
        }

        return array_map(
            fn($x, $y) => ['x' => $x, 'y' => $y],
            array_keys($chart),
            $chart
        );
    }

    private function formatDate($date): string
    {
        try {
            $dt = \DateTime::createFromFormat('Y-m', $date);
            if ($dt) {
                return $dt->format('M Y');
            }
        } catch (\Exception $e) {
            return $date;
        }

        return $date;
    }

    private function normalizeTransactionNames(array $rows): array
    {
        return array_map(function (array $row) {
            $row['name'] = $this->normalizeName($row['name'] ?? null);

            return $row;
        }, $rows);
    }

    private function normalizeName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $name = trim($name);

        return $name === '' ? $name : ucwords(strtolower($name));
    }
}
