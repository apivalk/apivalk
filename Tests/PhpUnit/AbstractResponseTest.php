<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use PHPUnit\Framework\TestCase;

abstract class AbstractResponseTest extends TestCase
{
    abstract public function getExpectedStatusCode(): int;

    abstract public function getResponse(): AbstractApivalkResponse;

    public function testExtendsAbstractApivalkResponse(): void
    {
        $this->assertTrue(is_subclass_of(\get_class($this->getResponse()), AbstractApivalkResponse::class));
    }

    public function testHasRequiredMethods(): void
    {
        $responseClass = \get_class($this->getResponse());

        $this->assertTrue(method_exists($responseClass, 'getDocumentation'));
        $this->assertTrue(method_exists($responseClass, 'getStatusCode'));
        $this->assertTrue(method_exists($responseClass, 'toArray'));
    }

    public function testDocumentationReturnsCorrectInstance(): void
    {
        /** @var ApivalkResponseDocumentation $documentation */
        $documentation = call_user_func([\get_class($this->getResponse()), 'getDocumentation']);

        $this->assertInstanceOf(ApivalkResponseDocumentation::class, $documentation);
    }

    public function testResponseStatusCode(): void
    {
        $status = $this->getExpectedStatusCode();
        $this->assertSame($status, $this->getResponse()::getStatusCode());
    }

    public function testArrayStructureMatchesDocumentation(): void
    {
        $responseArray = $this->getResponse()->toArray();
        $documentation = $this->getResponse()::getDocumentation();

        foreach ($documentation->getProperties() as $property) {
            $propertyName = $property->getPropertyName();

            $this->assertArrayHasKey($propertyName, $responseArray);
        }
    }
}
