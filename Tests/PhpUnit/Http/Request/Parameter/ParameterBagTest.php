<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Parameter;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class ParameterBagTest extends TestCase
{
    public function testBag(): void
    {
        $bag = new ParameterBag();
        $param = new Parameter('test', 'value');
        $bag->set($param);

        $this->assertTrue($bag->has('test'));
        $this->assertFalse($bag->has('other'));
        $this->assertSame($param, $bag->get('test'));
        $this->assertEquals('value', $bag->test);
        $this->assertNull($bag->other);
        $this->assertCount(1, $bag);

        foreach ($bag as $name => $p) {
            $this->assertEquals('test', $name);
            $this->assertSame($param, $p);
        }
    }
}
