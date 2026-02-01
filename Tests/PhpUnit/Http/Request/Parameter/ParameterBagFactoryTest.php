<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Parameter;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Documentation\Property\NumberProperty;
use apivalk\apivalk\Router\Route;

class ParameterBagFactoryTest extends TestCase
{
    private $serverBackup;
    private $getBackup;
    private $postBackup;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $this->getBackup = $_GET;
        $this->postBackup = $_POST;

        $_GET = [];
        $_POST = [];
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        $_GET = $this->getBackup;
        $_POST = $this->postBackup;
    }

    public function testCreateHeaderBag(): void
    {
        $_SERVER['HTTP_X_TEST'] = 'value';
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $bag = ParameterBagFactory::createHeaderBag();
        $this->assertEquals('value', $bag->X_TEST);
        $this->assertNull($bag->CONTENT_TYPE);
    }

    public function testCreateQueryBag(): void
    {
        $doc = new ApivalkRequestDocumentation();
        $doc->addQueryProperty(new StringProperty('name'));
        
        $_GET['name'] = 'John';
        $_GET['age'] = '25'; // Not in documentation

        $bag = ParameterBagFactory::createQueryBag($doc);
        $this->assertEquals('John', $bag->name);
        $this->assertNull($bag->age);
    }

    public function testCreatePathBag(): void
    {
        $doc = new ApivalkRequestDocumentation();
        $doc->addPathProperty(new NumberProperty('id', '', NumberProperty::FORMAT_INT32));

        $route = $this->createMock(Route::class);
        $route->method('getUrl')->willReturn('/users/{id}');

        $_SERVER['REQUEST_URI'] = '/users/123';

        $bag = ParameterBagFactory::createPathBag($route, $doc);
        $this->assertEquals(123, $bag->id);
        $this->assertIsInt($bag->id);
    }

    public function testCreateBodyBag(): void
    {
        $doc = new ApivalkRequestDocumentation();
        $doc->addBodyProperty(new StringProperty('name'));
        $doc->addBodyProperty(new NumberProperty('age', '', NumberProperty::FORMAT_INT32));

        $_POST['name'] = 'Jane';
        $_POST['age'] = '30';

        $bag = ParameterBagFactory::createBodyBag($doc);
        $this->assertEquals('Jane', $bag->name);
        $this->assertEquals(30, $bag->age);
        $this->assertIsInt($bag->age);
    }

    public function testTypeCastValue(): void
    {
        $prop = new NumberProperty('test', '', NumberProperty::FORMAT_INT32);
        $this->assertEquals(123, ParameterBagFactory::typeCastValueByProperty('123', $prop));

        $prop = new \apivalk\apivalk\Documentation\Property\BooleanProperty('test', '', false);
        $this->assertTrue(ParameterBagFactory::typeCastValueByProperty('1', $prop));
        
        $prop = new StringProperty('test');
        $this->assertEquals('123', ParameterBagFactory::typeCastValueByProperty(123, $prop));
    }

    public function testTypeCastDateTimeValue(): void
    {
        $prop = new StringProperty('test');
        $prop->setFormat(StringProperty::FORMAT_DATE);

        $result = ParameterBagFactory::typeCastValueByProperty('2023-12-20', $prop);
        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertEquals('2023-12-20', $result->format('Y-m-d'));

        $prop->setFormat(StringProperty::FORMAT_DATE_TIME);
        $result = ParameterBagFactory::typeCastValueByProperty('2023-12-20T14:00:00+00:00', $prop);
        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertEquals('2023-12-20T14:00:00+00:00', $result->format(\DateTime::ATOM));
    }
}
