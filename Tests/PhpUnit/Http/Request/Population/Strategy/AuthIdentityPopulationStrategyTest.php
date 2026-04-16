<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Population\Strategy;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Http\Request\Population\Strategy\AuthIdentityPopulationStrategy;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class AuthIdentityPopulationStrategyTest extends TestCase
{
    public function testSetsGuestAuthIdentityByDefault(): void
    {
        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);

        $request = $this->makeRequest();
        $strategy = new AuthIdentityPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertInstanceOf(GuestAuthIdentity::class, $request->getAuthIdentity());
    }

    private function makeRequest(): AbstractApivalkRequest
    {
        return new class extends AbstractApivalkRequest {
            /** @var AbstractAuthIdentity|null */
            private $identity;

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }

            public function setAuthIdentity(AbstractAuthIdentity $authIdentity): void
            {
                $this->identity = $authIdentity;
            }

            public function getAuthIdentity(): AbstractAuthIdentity
            {
                return $this->identity;
            }
        };
    }
}
