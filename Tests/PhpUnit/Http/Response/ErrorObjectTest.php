<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\ErrorObject;

class ErrorObjectTest extends TestCase
{
    public function testErrorObject(): void
    {
        $error = new ErrorObject('field', 'Invalid value');
        $this->assertEquals('field', $error->getKey());
        $this->assertEquals('Invalid value', $error->getMessage());
    }
}
