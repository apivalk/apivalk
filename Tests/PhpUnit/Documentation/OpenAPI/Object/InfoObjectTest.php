<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\ContactObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\LicenseObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\InfoObject;

class InfoObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $contact = $this->createMock(ContactObject::class);
        $contact->method('toArray')->willReturn([
            'name' => 'Name',
            'url' => 'https://example.com',
            'email' => 'test@example.com'
        ]);
        
        $license = $this->createMock(LicenseObject::class);
        $license->method('toArray')->willReturn([
            'name' => 'MIT',
            'identifier' => 'MIT',
            'url' => 'https://opensource.org/licenses/MIT'
        ]);
        
        $info = new InfoObject(
            'Title',
            '1.0.0',
            'Summary',
            'Description',
            'https://example.com/terms',
            $contact,
            $license
        );

        $expected = [
            'title' => 'Title',
            'summary' => 'Summary',
            'version' => '1.0.0',
            'description' => 'Description',
            'termsOfService' => 'https://example.com/terms',
            'contact' => [
                'name' => 'Name',
                'url' => 'https://example.com',
                'email' => 'test@example.com'
            ],
            'license' => [
                'name' => 'MIT',
                'identifier' => 'MIT',
                'url' => 'https://opensource.org/licenses/MIT'
            ]
        ];

        $this->assertEquals($expected, $info->toArray());
        $this->assertEquals('Title', $info->getTitle());
        $this->assertEquals('1.0.0', $info->getVersion());
        $this->assertEquals('Summary', $info->getSummary());
        $this->assertEquals('Description', $info->getDescription());
        $this->assertEquals('https://example.com/terms', $info->getTermsOfService());
        $this->assertSame($contact, $info->getContact());
        $this->assertSame($license, $info->getLicense());
    }

    public function testToArrayMinimal(): void
    {
        $info = new InfoObject('Title', '1.0.0');

        $expected = [
            'title' => 'Title',
            'version' => '1.0.0'
        ];

        $this->assertEquals($expected, $info->toArray());
    }
}
