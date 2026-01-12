<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Http\Request\File\FileBagFactory;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory;
use apivalk\apivalk\Security\AbstractAuthIdentity;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Security\GuestAuthIdentity;

abstract class AbstractApivalkRequest implements ApivalkRequestInterface
{
    /** @var MethodInterface|null */
    private $method;
    /** @var ParameterBag|null */
    private $headerBag;
    /** @var ParameterBag|null */
    private $queryParameterBag;
    /** @var ParameterBag|null */
    private $bodyParameterBag;
    /** @var ParameterBag|null */
    private $pathParameterBag;
    /** @var FileBag|null */
    private $fileBag;
    /** @var AbstractAuthIdentity|GuestAuthIdentity */
    private $authIdentity;

    abstract public static function getDocumentation(): ApivalkRequestDocumentation;

    public function populate(Route $route): void
    {
        $documentation = static::getDocumentation();

        $this->method = $route->getMethod();
        $this->headerBag = ParameterBagFactory::createHeaderBag();
        $this->queryParameterBag = ParameterBagFactory::createQueryBag($documentation);
        $this->pathParameterBag = ParameterBagFactory::createPathBag($route, $documentation);
        $this->bodyParameterBag = ParameterBagFactory::createBodyBag($documentation);
        $this->fileBag = FileBagFactory::create();
        $this->authIdentity = new GuestAuthIdentity([]);
    }

    public function getMethod(): MethodInterface
    {
        return $this->method;
    }

    public function header(): ParameterBag
    {
        return $this->headerBag;
    }

    public function query(): ParameterBag
    {
        return $this->queryParameterBag;
    }

    public function body(): ParameterBag
    {
        return $this->bodyParameterBag;
    }

    public function path(): ParameterBag
    {
        return $this->pathParameterBag;
    }

    public function file(): FileBag
    {
        return $this->fileBag;
    }

    public function getAuthIdentity(): AbstractAuthIdentity
    {
        return $this->authIdentity;
    }

    public function setAuthIdentity(AbstractAuthIdentity $authIdentity): void
    {
        $this->authIdentity = $authIdentity;
    }
}
