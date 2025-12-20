<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller;

use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\Route;

abstract class AbstractApivalkController
{
    abstract public static function getRoute(): Route;

    /**
     * @return class-string<ApivalkRequestInterface>
     */
    abstract public static function getRequestClass(): string;

    /**
     * @return array<class-string<AbstractApivalkResponse>>
     */
    abstract public static function getResponseClasses(): array;

    abstract public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse;
}
