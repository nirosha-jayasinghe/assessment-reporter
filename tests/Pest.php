<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

// Uses Laravel's testing traits globally
uses(Tests\TestCase::class, RefreshDatabase::class, WithFaker::class)->in('Feature', 'Unit');
