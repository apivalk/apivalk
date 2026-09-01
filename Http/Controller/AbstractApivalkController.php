<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller;

use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\Route\Route;

/**
 * `__invoke()` is declared as `@method` rather than as an abstract method on purpose.
 *
 * An abstract signature would fix the parameter at `ApivalkRequestInterface` for every
 * subclass, and PHP forbids narrowing a parameter when overriding, so controllers could
 * never name their own request class. Without an inherited signature there is nothing to
 * be compatible with, and a controller can write `__invoke(MyRequest $request)` natively.
 *
 * The trade is that a missing `__invoke()` is no longer a compile-time error. RouteCacheFactory
 * checks for it while indexing routes instead, so it still fails on boot rather than mid-request.
 *
 * @template TRequest of ApivalkRequestInterface = ApivalkRequestInterface
 *
 * @method AbstractApivalkResponse __invoke(ApivalkRequestInterface $request)
 */
abstract class AbstractApivalkController
{
    abstract public static function getRoute(): Route;

    /**
     * @return class-string<TRequest>
     */
    abstract public static function getRequestClass(): string;

    /**
     * @return array<class-string<AbstractApivalkResponse>>
     */
    abstract public static function getResponseClasses(): array;
}
