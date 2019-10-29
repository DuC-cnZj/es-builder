<?php

namespace DucCnzj\EsBuilder\Tests;

use DucCnzj\EsBuilder\BuilderServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getPackageProviders($app)
    {
        return [BuilderServiceProvider::class];
    }
}
