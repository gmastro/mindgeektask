<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use CreatesApplication;

    protected $seed = true;

    /**
     * Storage Name Property
     *
     * Usage of a default storage container name to hold downloaded, created or any other file that **SHOULD** be
     * removed right after the test.
     *
     * @access  protected
     * @static
     * @var     string STORAGE
     */
    protected const STORAGE = 'moufa';
}
