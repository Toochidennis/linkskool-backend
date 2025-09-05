<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Academics\ClassModel;
use V3\App\Models\Portal\Academics\SchoolSettings;
use V3\App\Models\Portal\Payments\Transaction;

class ExpenditureService
{
    private Transaction $transaction;
    private SchoolSettings $schoolSettings;

    public function __construct(\PDO $pdo)
    {
        $this->transaction = new Transaction($pdo);
    }

    public function addExpenditure(array $data)
    {
        $description = [
            'amount' => $data['amount'],
            'desc' => $data['description']
        ];

        $payload = [
            'trans_type' => 'expenditure',
            'c_type' => 2,
            'memo' => $data['description'],
            'cid' => $data['customer_id'],
            'cref' => $data['customer_reference'],
            'name' => $data['customer_name'],
            'description' => json_encode($description),
            'quantity' => 1,
            'it_id' => 1,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'account' => $data['account_number'],
            'account_name' => $data['account_name'],
            'approved' => 1,
            'sub' => 0,
            'status' => 1,
            'year' => $data['year'],
            'term' => $data['term'],
        ];

        return $this->transaction->insert($payload);
    }

    public function updateExpenditure(array $data): bool
    {
        $description = [
            'amount' => $data['amount'],
            'desc' => $data['description']
        ];

        $payload = [
            'trans_type' => 'expenditure',
            'memo' => $data['description'],
            'cid' => $data['customer_id'],
            'cref' => $data['customer_reference'],
            'name' => $data['customer_name'],
            'description' => json_encode($description),
            'amount' => $data['amount'],
            'account' => $data['account_number'],
            'account_name' => $data['account_name'],
            'year' => $data['year'],
            'term' => $data['term'],
        ];

        $existing = $this->transaction
            ->where('tid', '=', $data['id'])
            ->first();

        if ($existing) {
            return $this->transaction
                ->where('tid', '=', $data['id'])
                ->update($payload);
        }

        return false;
    }

    public function report(array $filters): array
    {
        $query =  $this->transaction
            ->select([
                'tid AS id',
                'cid AS customer_id',
                'cref AS reference',
                'memo as description',
                'name',
                'account AS account_number',
                'account_name',
                'amount',
                'date',
                'year',
                'term',
                'status',
            ])
            ->where('status', '=', 1)
            ->where('trans_type', '=', 'expenditure');

        $query = $this->applyDateFilters($query, $filters);
        $query = $this->applyOtherFilters($query, $filters);

        $rows = $query->get();
        $groupBy = $filters['group_by'] ?? null;

        $transactions = $groupBy ?
            $this->applyGrouping($rows, $groupBy)
            :
            $rows;

        return [
            'summary' => [
                'total_amount' => array_sum(array_column($rows, 'amount')),
                'total_transactions' => count($rows),
                'unique_vendors' => count(array_unique(array_column($rows, 'customer_id'))),
            ],
            'chart_data' => $this->buildChartData($rows, $groupBy),
            'transactions' => $transactions,
        ];
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

        // Quick reports
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

        // Terms
        if (!empty($appliedFilters['terms'])) {
            $query->in('term', $appliedFilters['terms']);
        }

        // Sessions (years)
        if (!empty($appliedFilters['sessions'])) {
            $query->in('year', $appliedFilters['sessions']);
        }

        // Levels
        if (!empty($appliedFilters['vendors'])) {
            $query->in('cid', $appliedFilters['vendors']);
        }

        return $query;
    }

    private function applyGrouping(array $rows, string $groupBy): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            switch ($groupBy) {
                case 'vendor':
                    $key = $row['customer_id'];
                    $name = $row['name'];
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
            $grouped[$key]['unique_vendors'][$row['customer_id']] = true;
        }

        // finalize unique_vendors count
        foreach ($grouped as &$g) {
            $g['unique_vendors'] = count($g['unique_vendors']);
        }

        return array_values($grouped);
    }

    private function buildChartData(array $rows, ?string $groupBy): array
    {
        $chart = [];

        if ($groupBy) {
            foreach ($rows as $row) {
                switch ($groupBy) {
                    case 'vendor':
                        $key = $row['customer_id'];
                        $label = $row['name'];
                        break;
                    case 'month':
                        $key = date('Y-m', strtotime($row['date']));
                        $label = $this->formatDate($key);
                        break;
                    default:
                        $key = 'Unknown';
                        $label = 'Unknown';
                }

                if (!isset($chart[$key])) {
                    $chart[$key] = ['x' => $label, 'y' => 0];
                }

                $chart[$key]['y'] += $row['amount'];
            }

            return array_values($chart);
        }

        // Chart data for normal reports (x = date)
        foreach ($rows as $row) {
            $date = date('Y-m-d', strtotime($row['date']));
            if (!isset($chart[$date])) {
                $chart[$date] = 0;
            }
            $chart[$date] += $row['amount'];
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
                return $dt->format('M Y'); // Sep 2025
            }
        } catch (\Exception $e) {
            // fallback if parsing fails
            return $date;
        }

        return $date;
    }
}
