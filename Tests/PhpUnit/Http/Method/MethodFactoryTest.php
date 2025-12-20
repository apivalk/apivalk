<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Method;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Method\MethodFactory;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Method\PostMethod;
use apivalk\apivalk\Http\Method\DeleteMethod;
use apivalk\apivalk\Http\Method\PatchMethod;
use apivalk\apivalk\Http\Method\PutMethod;

class MethodFactoryTest extends TestCase
{
    /**
     * @dataProvider methodProvider
     */
    public function testCreate(string $name, string $expectedClass): void
    {
        $method = MethodFactory::create($name);
        $this->assertInstanceOf($expectedClass, $method);
        $this->assertEquals(strtoupper($name), $method->getName());
    }

    public function methodProvider(): array
    {
        return [
            ['GET', GetMethod::class],
            ['get', GetMethod::class],
            ['POST', PostMethod::class],
            ['post', PostMethod::class],
            ['DELETE', DeleteMethod::class],
            ['delete', DeleteMethod::class],
            ['PATCH', PatchMethod::class],
            ['patch', PatchMethod::class],
            ['PUT', PutMethod::class],
            ['put', PutMethod::class],
        ];
    }

    public function testCreateInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Method "INVALID" is not supported');
        MethodFactory::create('INVALID');
    }
}
