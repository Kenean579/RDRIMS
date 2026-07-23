<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should be run before each test.
     */
    protected bool $seed = true;

    /**
     * The database seeder class to use.
     */
    protected string $seeder = \Database\Seeders\DatabaseSeeder::class;

    /**
     * Set up the test environment before each test.
     * Disable foreign key checks for the entire test lifecycle to avoid truncate errors.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable foreign key checks after app is initialized but before RefreshDatabase runs
        if ($this->app->make('db')->getDriverName() === 'mysql') {
            $this->app->make('db')->statement('SET FOREIGN_KEY_CHECKS=0');
        }
    }

    /**
     * Clean up after each test.
     * Re-enable foreign key checks.
     */
    protected function tearDown(): void
    {
        // Re-enable foreign key checks before tearing down
        if ($this->app && $this->app->make('db')->getDriverName() === 'mysql') {
            $this->app->make('db')->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        parent::tearDown();
    }
}

