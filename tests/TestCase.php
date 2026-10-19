<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // View dùng @vite: không phụ thuộc việc đã chạy npm run build khi test
        $this->withoutVite();
    }
}
