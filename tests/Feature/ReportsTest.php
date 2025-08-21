<?php

namespace Tests\Feature;

use App\Services\DataRepository;
use App\Services\ReportService;

it('generates progress report and ignores incomplete assessments', function () {
    $repo = new DataRepository();
    $svc  = new ReportService($repo);

    $text = $svc->generate('student1', 'progress');

    expect($text)->toContain('has completed Numeracy assessment 3 times');
    expect($text)->toContain('16th December 2019');
    expect($text)->toContain('16th December 2020');
    expect($text)->toContain('16th December 2021');
    expect($text)->not->toContain('2022');
});

it('generates feedback for incorrect answers', function () {
    $repo = new DataRepository();
    $svc  = new ReportService($repo);

    $text = $svc->generate('student1', 'feedback');

    // Tony got 15/16 with the median question wrong
    expect($text)->toContain("He got 15 questions right out of 16");
    expect($text)->toContain("median");
    expect($text)->toContain("Right answer: B with value 9");
});
