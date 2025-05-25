<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config([
            'app.readonly' => false, // avoid conflict by confirming default
        ]);
    }
}
