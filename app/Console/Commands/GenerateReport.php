<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;

class GenerateReport extends Command
{
    protected $signature = 'report:generate {--student=} {--type=}';
    protected $description = 'Generate student assessment report (Diagnostic|Progress|Feedback)';

    public function handle(ReportService $service): int
    {
        $studentId = $this->option('student') ?: $this->ask('Student ID');

        $typeInput = $this->option('type') ?: $this->ask(
            'Report to generate (1 for Diagnostic, 2 for Progress, 3 for Feedback)'
        );

        try {
            $type = $this->normalizeType($typeInput);
        } catch (\InvalidArgumentException $e) {
            $this->error('Invalid report type. Use 1/2/3 or diagnostic/progress/feedback.');
            return self::FAILURE;
        }

        try {
            $output = $service->generate($studentId, $type);
            $this->line($output);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function normalizeType(string $input): string
    {
        $map = [
            '1' => 'diagnostic',
            '2' => 'progress',
            '3' => 'feedback',
        ];
        $t = strtolower(trim($input));
        if (isset($map[$t])) {
            return $map[$t];
        }
        if (in_array($t, array_values($map), true)) {
            return $t;
        }
        throw new \InvalidArgumentException('Unknown type');
    }
}
