<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Population\Strategy;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Http\Request\Population\Strategy\IpPopulationStrategy;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class IpPopulationStrategyTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';
    }

    protected function tearDown(): void
    {
        unset($_SERVER['REMOTE_ADDR']);
    }

    public function testSetsIpFromServer(): void
    {
        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);

        $request = $this->makeRequest();
        $strategy = new IpPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertSame('1.2.3.4', $request->getIp());
    }

    private function makeRequest(): AbstractApivalkRequest
    {
        return new class extends AbstractApivalkRequest {
            /** @var string */
            private $ip = '';

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }

            public function setIp(?string $ip): void
            {
                $this->ip = $ip ?? '';
            }

            public function getIp(): string
            {
                return $this->ip;
            }
        };
    }
}
