<?php

namespace DucCnzj\EsBuilder\Tests\BuilderTest;

use DucCnzj\EsBuilder\Builder;
use DucCnzj\EsBuilder\Tests\User;
use DucCnzj\EsBuilder\Tests\TestCase;

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
