<?php

namespace App\Services;

use Carbon\Carbon;

class ReportService
{
    public function __construct(private DataRepository $repo)
    {
    }

    public function generate(string $studentId, string $type): string
    {
        $student = $this->repo->findStudent($studentId);
        if (!$student) {
            throw new \RuntimeException("Student not found: {$studentId}");
        }

        $completed = $this->repo->getCompletedResponsesByStudent($studentId);
        if (empty($completed)) {
            throw new \RuntimeException('No completed assessments for this student.');
        }

        return match ($type) {
            'diagnostic' => $this->diagnostic($student, $completed),
            'progress'   => $this->progress($student, $completed),
            'feedback'   => $this->feedback($student, $completed),
            default      => throw new \InvalidArgumentException('Unsupported report type'),
        };
    }

    private function diagnostic(array $student, array $completed): string
    {
        $latest = end($completed);
        $assessment = $this->repo->getAssessment($latest['assessmentId']);
        $assessmentName = $assessment['name'] ?? $latest['assessmentId'];
        $dt = $latest['completed_carbon'];
        $header = sprintf(
            "%s %s recently completed %s assessment on %s\n",
            $student['firstName'],
            $student['lastName'],
            $assessmentName,
            $dt->format('jS F Y h:i A')
        );

        [$correct, $total] = $this->score($latest);
        $out = $header;
        $out .= sprintf("He got %d questions right out of %d. Details by strand given below:\n\n", $correct, $total);

        $byStrand = $this->scoreByStrand($latest);
        foreach ($byStrand as $strand => [$c, $t]) {
            $out .= sprintf("%s: %d out of %d correct\n", $strand, $c, $t);
        }
        return rtrim($out) . "\n";
    }

    private function progress(array $student, array $completed): string
    {
        $assessment = $this->repo->getAssessment($completed[0]['assessmentId']);
        $assessmentName = $assessment['name'] ?? $completed[0]['assessmentId'];
        $count = count($completed);
        $out = sprintf(
            "%s %s has completed %s assessment %d %s in total. Date and raw score given below:\n\n",
            $student['firstName'],
            $student['lastName'],
            $assessmentName,
            $count,
            $count === 1 ? 'time' : 'times'
        );

        foreach ($completed as $r) {
            $dt = $r['completed_carbon'];
            $raw = $r['results']['rawScore'] ?? null;
            [$_, $total] = $this->score($r); // fall back to computed total if raw missing
            $out .= sprintf("Date: %s, Raw Score: %s out of %d\n",
                $dt->format('jS F Y'),
                $raw !== null ? $raw : $this->score($r)[0],
                $total
            );
        }

        $firstScore = $completed[0]['results']['rawScore'] ?? $this->score($completed[0])[0];
        $lastResp = end($completed);
        $lastScore  = $lastResp['results']['rawScore'] ?? $this->score($lastResp)[0];
        $delta = $lastScore - $firstScore;

        $out .= "\n" . sprintf(
            "%s %s got %d more correct in the recent completed assessment than the oldest\n",
            $student['firstName'],
            $student['lastName'],
            $delta
        );
        return $out;
    }

    private function feedback(array $student, array $completed): string
    {
        $latest = end($completed);
        $assessment = $this->repo->getAssessment($latest['assessmentId']);
        $assessmentName = $assessment['name'] ?? $latest['assessmentId'];
        $dt = $latest['completed_carbon'];

        [$correct, $total] = $this->score($latest);
        $out = sprintf(
            "%s %s recently completed %s assessment on %s\n",
            $student['firstName'], $student['lastName'], $assessmentName, $dt->format('jS F Y h:i A')
        );
        $out .= sprintf("He got %d questions right out of %d. Feedback for wrong answers given below\n\n", $correct, $total);

        foreach ($latest['responses'] as $resp) {
            $q = $this->repo->getQuestion($resp['questionId']);
            if (!$q) { continue; }
            $correctId = $q['config']['key'];
            if ($resp['response'] === $correctId) { continue; }

            [$studentLabel, $studentValue] = $this->optionLabelValue($q, $resp['response']);
            [$rightLabel, $rightValue]     = $this->optionLabelValue($q, $correctId);

            $out .= sprintf("Question: %s\n", $q['stem']);
            $out .= sprintf("Your answer: %s with value %s\n", $studentLabel, $studentValue);
            $out .= sprintf("Right answer: %s with value %s\n", $rightLabel, $rightValue);
            $out .= sprintf("Hint: %s\n\n", $q['config']['hint']);
        }
        return rtrim($out) . "\n";
    }

    private function score(array $response): array
    {
        $total = 0; $correct = 0;
        foreach ($response['responses'] as $resp) {
            $q = $this->repo->getQuestion($resp['questionId']);
            if (!$q) { continue; }
            $total++;
            if ($resp['response'] === $q['config']['key']) {
                $correct++;
            }
        }
        return [$correct, $total];
    }

    private function scoreByStrand(array $response): array
    {
        $by = [];
        foreach ($response['responses'] as $resp) {
            $q = $this->repo->getQuestion($resp['questionId']);
            if (!$q) { continue; }
            $strand = $q['strand'];
            $by[$strand] = $by[$strand] ?? [0, 0];
            $by[$strand][1]++;
            if ($resp['response'] === $q['config']['key']) {
                $by[$strand][0]++;
            }
        }
        return $by; // [strand => [correct, total]]
    }

    private function optionLabelValue(array $question, string $optionId): array
    {
        foreach ($question['config']['options'] as $opt) {
            if ($opt['id'] === $optionId) {
                return [$opt['label'], $opt['value']];
            }
        }
        return ['?', '?'];
    }
}
