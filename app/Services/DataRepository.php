<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class DataRepository
{
    private array $students;
    private array $assessments;
    private array $questions;
    private array $responses;

    public function __construct()
    {
        $this->students    = $this->load('students.json');
        $this->assessments = $this->load('assessments.json');
        $this->questions   = $this->load('questions.json');
        $this->responses   = $this->load('student-responses.json');
    }

    private function load(string $file): array
    {
        $path = base_path('data/' . $file);
        if (!File::exists($path)) {
            throw new \RuntimeException("Data file missing: {$file}");
        }
        $json = json_decode(File::get($path), true);
        if ($json === null) {
            throw new \RuntimeException("Invalid JSON in {$file}");
        }
        return $json;
    }

    public function findStudent(string $id): ?array
    {
        return collect($this->students)->firstWhere('id', $id);
    }

    public function getAssessment(string $id): ?array
    {
        return collect($this->assessments)->firstWhere('id', $id);
    }

    public function getQuestion(string $id): ?array
    {
        return collect($this->questions)->firstWhere('id', $id);
    }

    public function getCompletedResponsesByStudent(string $studentId): array
    {
        return collect($this->responses)
            ->filter(fn ($r) => Arr::get($r, 'student.id') === $studentId && !empty($r['completed']))
            ->map(function ($r) {
                $r['completed_carbon'] = Carbon::createFromFormat('d/m/Y H:i:s', $r['completed']);
                return $r;
            })
            ->sortBy('completed_carbon')
            ->values()
            ->all();
    }
}
