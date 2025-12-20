<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

abstract class AbstractRequestTest extends TestCase
{
    abstract public function getRequestClass(): string;

    public function testExtendsAbstractApivalkRequest(): void
    {
        $this->assertTrue(is_subclass_of($this->getRequestClass(), AbstractApivalkRequest::class));
    }

    public function testHasGetDocumentationMethod(): void
    {
        $this->assertTrue(method_exists($this->getRequestClass(), 'getDocumentation'));
    }

    public function testDocumentationIsValid(): void
    {
        $reflection = new ReflectionClass($this->getRequestClass());

        if (!$reflection->isInstantiable()) {
            $this->markTestSkipped(
                \sprintf('The class %s is not instantiable and will not be tested', $this->getRequestClass())
            );

            return;
        }

        /** @var AbstractApivalkRequest $request */
        $request = $reflection->newInstance();

        $documentation = $request::getDocumentation();

        $this->assertInstanceOf(ApivalkRequestDocumentation::class, $documentation);

        $requestShapes = $this->extractShapesFromRequest($reflection);

        if ($requestShapes['body'] === null) {
            $this->markTestSkipped('No body shape found in request documentation');
        }

        if ($requestShapes['query'] === null) {
            $this->markTestSkipped('No query shape found in request documentation');
        }

        if ($requestShapes['path'] === null) {
            $this->markTestSkipped('No path shape found in request documentation');
        }

        $bodyProperties = $this->extractShapeProperties($requestShapes['body']);
        $queryProperties = $this->extractShapeProperties($requestShapes['query']);
        $pathProperties = $this->extractShapeProperties($requestShapes['path']);

        foreach ($documentation->getBodyProperties() as $property) {
            $this->assertContains($property->getPropertyName(), $bodyProperties);
        }

        foreach ($documentation->getQueryProperties() as $property) {
            $this->assertContains($property->getPropertyName(), $queryProperties);
        }

        foreach ($documentation->getPathProperties() as $property) {
            $this->assertContains($property->getPropertyName(), $pathProperties);
        }
    }

    /**
     * @return string[]
     */
    private function extractShapeProperties(string $shapeInterface): array
    {
        if (!interface_exists($shapeInterface) && !class_exists($shapeInterface)) {
            throw new \RuntimeException("Shape {$shapeInterface} does not exist");
        }

        $reflection = new \ReflectionClass($shapeInterface);
        $doc = $reflection->getDocComment();

        if ($doc === false) {
            return [];
        }

        $properties = [];

        preg_match_all(
            '/@property-read\s+[^\s]+\s+\$(\w+)/',
            $doc,
            $matches
        );

        foreach ($matches[1] as $propertyName) {
            $properties[] = $propertyName;
        }

        return $properties;
    }

    /**
     * @return array{}
     */
    private function extractShapesFromRequest(\ReflectionClass $reflection): array
    {
        $doc = $reflection->getDocComment();

        if ($doc === false) {
            return ['query' => null, 'path' => null, 'body' => null];
        }

        $shapes = [
            'query' => null,
            'path' => null,
            'body' => null,
        ];

        preg_match_all(
            '/@method\s+([^\s]+)\s+(query|path|body)\s*\(\)/',
            $doc,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            [$full, $returnTypes, $section] = $match;

            foreach (explode('|', $returnTypes) as $type) {
                $type = ltrim($type, '\\');

                if (!str_ends_with($type, 'ParameterBag')) {
                    $shapes[$section] = '\\' . $type;
                }
            }
        }

        return $shapes;
    }
}
