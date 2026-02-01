<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Documentation\Response\ValidationErrorObject;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;

class BadValidationApivalkResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $error = new ValidationErrorObject();
        $error->populate('email', new ValidatorResult(false, ValidatorResult::VALUE_DOES_NOT_MATCH_PATTERN));

        $response = new BadValidationApivalkResponse([$error]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals([$error], $response->getErrors());

        $expected = [
            'errors' => [
                [
                    'parameter' => 'email',
                    'message' => 'This value does not match the required pattern.',
                    'key' => 'value_does_not_match_pattern',
                ]
            ]
        ];

        $this->assertEquals($expected, $response->toArray());
    }

    public function testGetDocumentation(): void
    {
        $doc = BadValidationApivalkResponse::getDocumentation();
        $this->assertEquals('Request validation failed', $doc->getDescription());
        $this->assertCount(1, $doc->getProperties());
    }
}
