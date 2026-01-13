<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;

interface ApivalkRequestInterface
{
    public static function getDocumentation(): ApivalkRequestDocumentation;

    public function populate(Route $route): void;

    public function getMethod(): MethodInterface;

    public function getIp(): ?string;

    public function header(): ParameterBag;

    public function query(): ParameterBag;

    public function body(): ParameterBag;

    public function path(): ParameterBag;

    public function file(): FileBag;

    public function getAuthIdentity(): AbstractAuthIdentity;

    public function setAuthIdentity(AbstractAuthIdentity $authIdentity): void;

    public function setRateLimitResult(RateLimitResult $rateLimitResult): void;

    public function getRateLimitResult(): ?RateLimitResult;
}
