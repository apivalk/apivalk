<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;

class TagObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $tag = new TagObject('User', 'Operations about user');
        
        $expected = [
            'name' => 'User',
            'description' => 'Operations about user'
        ];

        $this->assertEquals($expected, $tag->toArray());
    }

    public function testToArrayMinimal(): void
    {
        $tag = new TagObject('User');
        
        $expected = [
            'name' => 'User',
            'description' => null
        ];

        $this->assertEquals($expected, $tag->toArray());
    }
}
