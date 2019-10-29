<?php

namespace Tests\BuilderTest;

use Tests\User;
use Tests\TestCase;
use DucCnzj\EsBuilder\Builder;

class BuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_macroable()
    {
        Builder::macro('foo', function () {
            return 'bar';
        });

        $builder = new Builder($model = \Mockery::mock(User::class), 'zonda');
        $this->assertEquals(
            'bar',
            $builder->foo()
        );
    }
}
