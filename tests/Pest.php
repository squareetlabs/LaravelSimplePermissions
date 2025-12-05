<?php

use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;

// Pest 1.x uses uses(), Pest 2.x+ uses pest()
if (function_exists('pest')) {
    // Pest 2.x and 3.x syntax
    pest()
        ->extend(TestCase::class)
        ->in('tests/Unit')
        ->in('tests/Feature');
} else {
    // Pest 1.x syntax
    uses(TestCase::class)->in('tests/Unit');
    uses(TestCase::class)->in('tests/Feature');
}
