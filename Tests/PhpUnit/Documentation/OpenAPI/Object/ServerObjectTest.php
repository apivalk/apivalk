<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\ServerObject;

class ServerObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $server = new ServerObject('https://api.example.com', 'Production server');
        
        $expected = [
            'url' => 'https://api.example.com',
            'description' => 'Production server'
        ];

        $this->assertEquals($expected, $server->toArray());
    }

    public function testToArrayMinimal(): void
    {
        $server = new ServerObject('https://api.example.com');
        
        $expected = [
            'url' => 'https://api.example.com',
            'description' => null
        ];

        $this->assertEquals($expected, $server->toArray());
    }
}
