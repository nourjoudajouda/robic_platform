<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // إعدادات إضافية للاختبارات
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }
}

