<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\i18n\Locale;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Http\Request\File\FileBagFactory;
use apivalk\apivalk\Http\Request\Pagination\CursorPaginator;
use apivalk\apivalk\Http\Request\Pagination\OffsetPaginator;
use apivalk\apivalk\Http\Request\Pagination\PagePaginator;
use apivalk\apivalk\Http\Request\Pagination\PaginatorFactory;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Sort\Sort;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use apivalk\apivalk\Util\IpResolver;

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

    abstract public static function getDocumentation(): ApivalkRequestDocumentation;

    public function populate(Route $route): void
    {
        $documentation = static::getDocumentation();

        // ToDo: Write Populator Logic/Strategy for this, it gets messy by now

        $this->method = $route->getMethod();
        $this->headerBag = ParameterBagFactory::createHeaderBag();
        $this->queryParameterBag = ParameterBagFactory::createQueryBag($route, $documentation);
        $this->pathParameterBag = ParameterBagFactory::createPathBag($route, $documentation);
        $this->bodyParameterBag = ParameterBagFactory::createBodyBag($documentation);
        $this->fileBag = FileBagFactory::create();
        $this->authIdentity = new GuestAuthIdentity([]);
        $this->ip = IpResolver::getClientIp();
        $this->sortBag = new SortBag();
        $this->filterBag = new FilterBag();

        $this->populateOrderBag($route);
        $this->populateFilterBag($route);
        $this->createPaginator($route);
    }

    private function populateOrderBag(Route $route): void
    {
        foreach ($route->getSortings() as $ordering) {
            if (!$this->sortBag->has($ordering->getField())) {
                $this->sortBag->set($ordering);
            }
        }

        $orderBy = $this->queryParameterBag->get('order_by');

        if ($orderBy === null) {
            return;
        }

        foreach (explode(',', $orderBy->getRawValue()) as $curOrderByField) {
            $curOrderByField = trim($curOrderByField);

            if ($curOrderByField === '') {
                continue;
            }

            if ($curOrderByField[0] !== '+' && $curOrderByField[0] !== '-') {
                $direction = '+';
                $field = $curOrderByField;
            } else {
                $direction = $curOrderByField[0];
                $field = substr($curOrderByField, 1);
            }

            if ($field === '') {
                continue;
            }

            $this->sortBag->set($direction === '-' ? Sort::desc($field) : Sort::asc($field));
        }
    }

    private function populateFilterBag(Route $route): void
    {
        foreach ($route->getFilters() as $filter) {
            $field = $filter->getField();
            $queryParameter = $this->queryParameterBag->get($field);

            $clonedFilter = clone $filter;
            if ($queryParameter !== null) {
                $clonedFilter->setValue(
                    ParameterBagFactory::typeCastValueByProperty($queryParameter->getRawValue(), $filter->getProperty())
                );
            }
            $this->filterBag->set($clonedFilter);
        }
    }

    private function createPaginator(Route $route): void
    {
        $pagination = $route->getPagination();

        if ($pagination !== null) {
            switch ($pagination->getType()) {
                case Pagination::TYPE_OFFSET:
                    $this->paginator = PaginatorFactory::offset($this, $pagination->getMaxLimit());
                    break;
                case Pagination::TYPE_CURSOR:
                    $this->paginator = PaginatorFactory::cursor($this, $pagination->getMaxLimit());
                    break;
                case Pagination::TYPE_PAGE:
                    $this->paginator = PaginatorFactory::page($this, $pagination->getMaxLimit());
                    break;
            }
        }
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

    /**
     * @return mixed|null
     */
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
}
