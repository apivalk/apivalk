<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Bootstrap;

use apivalk\apivalk\Apivalk;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

trait RequestTrait
{
    /** @var Apivalk */
    private $apivalk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apivalk = ApiFactory::create(new InMemoryCache());
        $this->resetSuperglobals();
    }

    protected function tearDown(): void
    {
        $this->resetSuperglobals();
        parent::tearDown();
    }

    private function resetSuperglobals(): void
    {
        $requestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'] ?? null;

        $_SERVER = [
            'SERVER_PROTOCOL'    => 'HTTP/1.1',
            'REMOTE_ADDR'        => '127.0.0.1',
        ];

        if ($requestTimeFloat !== null) {
            $_SERVER['REQUEST_TIME_FLOAT'] = $requestTimeFloat;
        }
        $_GET   = [];
        $_POST  = [];
        $_FILES = [];
    }

    protected function makeRequest(
        string $method,
        string $path,
        array $query = [],
        array $body = [],
        ?string $token = null,
        string $ip = '127.0.0.1'
    ): AbstractApivalkResponse {
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $_SERVER['REQUEST_URI']    = $path . ($query ? '?' . http_build_query($query) : '');
        $_SERVER['REMOTE_ADDR']    = $ip;

        if ($token !== null) {
            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }

        $_GET  = $query;
        $_POST = $body;

        return $this->apivalk->run();
    }
}
