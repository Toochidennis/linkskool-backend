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

        $query =  $this->transaction
            ->select([
                'tid AS id',
                'ref AS reference',
                'cref AS reg_no',
                'memo as description',
                'name',
                'amount',
                'date',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
                'status',
            ])
            ->where('status', '=', 1)
            ->where('trans_type', '=', 'receipts');

        $query = $this->applyDateFilters($query, $filters);
        $query = $this->applyOtherFilters($query, $filters);

        $rows = $query->get();
        $groupBy = $filters['group_by'] ?? null;

        $transactions = $groupBy ?
            $this->applyGrouping($rows, $groupBy, $classes, $levels)
            :
            array_map(function ($row) use ($classes, $levels) {
                $row['class_name'] = $classes[$row['class_id']] ?? null;
                $row['level_name'] = $levels[$row['level_id']] ?? null;
                return $row;
            }, $rows);

        return [
            'summary' => [
                'total_amount' => array_sum(array_column($rows, 'amount')),
                'total_transactions' => count($rows),
                'unique_students' => count(array_unique(array_column($rows, 'reg_no'))),
            ],
            'chart_data' => $this->buildChartData($rows, $groupBy, $classes, $levels),
            'transactions' => $transactions,
        ];
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

        // xQuick reports
        if ($reportType === 'termly') {
            $query->where('term', '=', $settings['term']);
        }

        if ($reportType === 'session') {
            $query->where('year', '=', $settings['year']);
        }

        if ($reportType === 'monthly') {
            $month = date('m');
            $year  = date('Y');
            $query->whereRaw("MONTH(date) = ? AND YEAR(date) = ?", [$month, $year]);
        }

        // Custom reports
        if ($reportType === 'custom') {
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
        if (!empty($appliedFilters['levels'])) {
            $query->in('level', $appliedFilters['levels']);
        }

        // Classes
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

        // finalize unique_students count
        foreach ($grouped as &$g) {
            $g['unique_students'] = count($g['unique_students']);
        }

        return array_values($grouped);
    }

    private function buildChartData(array $rows, ?string $groupBy, array $classes, array $levels): array
    {
        $chart = [];

        if ($groupBy) {
            // Chart data for grouped reports
            foreach ($rows as $row) {
                switch ($groupBy) {
                    case 'class':
                        $key = $classes[$row['class_id']] ?? 'Unknown Class';
                        break;
                    case 'level':
                        $key = $levels[$row['level_id']] ?? 'Unknown Level';
                        break;
                    case 'month':
                        $key = date('Y-m', strtotime($row['date']));
                        break;
                    default:
                        $key = 'Unknown';
                }

                if (!isset($chart[$key])) {
                    $chart[$key] = 0;
                }

                $chart[$key] += $row['amount'];
            }

            return array_map(
                fn($x, $y) => ['x' => $x, 'y' => $y],
                array_keys($chart),
                $chart
            );
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
