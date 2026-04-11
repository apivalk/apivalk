<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\i18n\Locale;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Http\Request\File\FileBagFactory;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use apivalk\apivalk\Router\Route\Order\Order;
use apivalk\apivalk\Router\Route\Order\OrderBag;
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
    /** @var OrderBag */
    private $orderBag;

    abstract public static function getDocumentation(): ApivalkRequestDocumentation;

    public function populate(Route $route): void
    {
        $documentation = static::getDocumentation();

        // ToDo: Write Populator Logic/Strategy for this, it gets messy by now

        $this->method = $route->getMethod();
        $this->headerBag = ParameterBagFactory::createHeaderBag();
        $this->queryParameterBag = ParameterBagFactory::createQueryBag($documentation);
        $this->pathParameterBag = ParameterBagFactory::createPathBag($route, $documentation);
        $this->bodyParameterBag = ParameterBagFactory::createBodyBag($documentation);
        $this->fileBag = FileBagFactory::create();
        $this->authIdentity = new GuestAuthIdentity([]);
        $this->ip = IpResolver::getClientIp();
        $this->orderBag = new OrderBag();

        $this->populateOrderBag($route);
    }

    private function populateOrderBag(Route $route): void
    {
        foreach ($route->getOrderings() as $ordering) {
            if (!$this->orderBag->has($ordering->getField())) {
                $this->orderBag->set($ordering);
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

            $this->orderBag->set($direction === '-' ? Order::desc($field) : Order::asc($field));
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

    public function ordering(): OrderBag
    {
        return $this->orderBag;
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
