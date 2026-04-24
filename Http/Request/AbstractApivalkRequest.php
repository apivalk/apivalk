<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\i18n\Locale;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Http\Request\Pagination\CursorPaginator;
use apivalk\apivalk\Http\Request\Pagination\OffsetPaginator;
use apivalk\apivalk\Http\Request\Pagination\PagePaginator;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Http\Request\Population\RequestPopulationStrategyCollection;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;

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
    /** @var string|null */
    private $ip;
    /** @var RateLimitResult|null */
    private $rateLimitResult;
    /** @var Locale */
    private $locale;
    /** @var SortBag */
    private $sortBag;
    /** @var FilterBag */
    private $filterBag;
    /** @var CursorPaginator|PagePaginator|OffsetPaginator|null */
    private $paginator;
    /** @var ApivalkRequestDocumentation */
    private $documentation;

    public function populate(Route $route, ApivalkRequestDocumentation $documentation): void
    {
        $this->documentation = $documentation;

        $requestPopulationContext = new RequestPopulationContext($route, $documentation);
        $requestPopulationStrategyCollection = new RequestPopulationStrategyCollection();

        foreach ($requestPopulationStrategyCollection->getAll() as $populationStrategy) {
            $populationStrategy->populate($this, $requestPopulationContext);
        }
    }

    public function getRuntimeDocumentation(): ApivalkRequestDocumentation
    {
        return $this->documentation;
    }

    public function getMethod(): MethodInterface
    {
        return $this->method;
    }

    public function getIp(): ?string
    {
        return $this->ip;
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

    public function sorting(): SortBag
    {
        return $this->sortBag;
    }

    public function filtering(): FilterBag
    {
        return $this->filterBag;
    }

    public function paginator()
    {
        return $this->paginator;
    }

    public function getAuthIdentity(): AbstractAuthIdentity
    {
        return $this->authIdentity;
    }

    public function setAuthIdentity(AbstractAuthIdentity $authIdentity): void
    {
        $this->authIdentity = $authIdentity;
    }

    public function setRateLimitResult(RateLimitResult $rateLimitResult): void
    {
        $this->rateLimitResult = $rateLimitResult;
    }

    public function getRateLimitResult(): ?RateLimitResult
    {
        return $this->rateLimitResult;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function setLocale(Locale $locale): void
    {
        $this->locale = $locale;
    }

    public function setMethod(MethodInterface $method): void
    {
        $this->method = $method;
    }

    public function setHeaderBag(ParameterBag $headerBag): void
    {
        $this->headerBag = $headerBag;
    }

    public function setQueryParameterBag(ParameterBag $queryParameterBag): void
    {
        $this->queryParameterBag = $queryParameterBag;
    }

    public function setPathParameterBag(ParameterBag $pathParameterBag): void
    {
        $this->pathParameterBag = $pathParameterBag;
    }

    public function setBodyParameterBag(ParameterBag $bodyParameterBag): void
    {
        $this->bodyParameterBag = $bodyParameterBag;
    }

    public function setFileBag(FileBag $fileBag): void
    {
        $this->fileBag = $fileBag;
    }

    public function setIp(?string $ip): void
    {
        $this->ip = $ip;
    }

    public function setSortBag(SortBag $sortBag): void
    {
        $this->sortBag = $sortBag;
    }

    public function setFilterBag(FilterBag $filterBag): void
    {
        $this->filterBag = $filterBag;
    }

    /**
     * @param CursorPaginator|OffsetPaginator|PagePaginator|null $paginator
     */
    public function setPaginator($paginator): void
    {
        $this->paginator = $paginator;
    }
}
