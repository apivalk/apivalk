<?php

declare(strict_types=1);

namespace apivalk\apivalk;

use apivalk\apivalk\Documentation\OpenAPI\Object\SecuritySchemeObject;
use apivalk\apivalk\Http\i18n\Locale;
use apivalk\apivalk\Http\i18n\LocalizationConfiguration;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Http\Renderer\JsonRenderer;
use apivalk\apivalk\Http\Renderer\RendererInterface;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Security\SecuritySchemeCollection;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ApivalkConfiguration
{
    private AbstractRouter $router;
    private RendererInterface $renderer;
    private MiddlewareStack $middlewareStack;
    /** @var callable|null */
    private $exceptionHandler;
    private ?ContainerInterface $container;
    private LoggerInterface $logger;
    private LocalizationConfiguration $localizationConfiguration;
    private SecuritySchemeCollection $securitySchemes;

    public function __construct(
        AbstractRouter $router,
        ?RendererInterface $renderer = null,
        ?callable $exceptionHandler = null,
        ?ContainerInterface $container = null,
        ?LoggerInterface $logger = null,
        ?LocalizationConfiguration $localizationConfiguration = null
    ) {
        $this->router = $router;
        $this->middlewareStack = new MiddlewareStack();
        $this->renderer = $renderer ?? new JsonRenderer();
        $this->exceptionHandler = $exceptionHandler;
        $this->container = $container;
        $this->logger = $logger ?? new NullLogger();
        $this->securitySchemes = new SecuritySchemeCollection();

        if ($localizationConfiguration === null) {
            $this->localizationConfiguration = new LocalizationConfiguration(Locale::en());
        } else {
            $this->localizationConfiguration = $localizationConfiguration;
        }
    }

    public function getMiddlewareStack(): MiddlewareStack
    {
        return $this->middlewareStack;
    }

    public function getRouter(): AbstractRouter
    {
        return $this->router;
    }

    public function getRenderer(): RendererInterface
    {
        return $this->renderer;
    }

    public function getExceptionHandler(): ?callable
    {
        return $this->exceptionHandler;
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getLocalizationConfiguration(): LocalizationConfiguration
    {
        return $this->localizationConfiguration;
    }

    public function addSecurityScheme(SecuritySchemeObject $securityScheme): void
    {
        $this->securitySchemes->add($securityScheme);
    }

    public function getSecuritySchemes(): SecuritySchemeCollection
    {
        return $this->securitySchemes;
    }
}
