<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Renderer;

use apivalk\apivalk\Http\Renderer\DelegatingRenderer;
use apivalk\apivalk\Http\Renderer\RendererInterface;
use apivalk\apivalk\Http\Response\FileApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use PHPUnit\Framework\TestCase;

class DelegatingRendererTest extends TestCase
{
    public function testFileResponseGoesToFileRenderer(): void
    {
        $response = FileApivalkResponse::fromContent('x', 'x.txt', 'text/plain');

        $defaultRenderer = $this->createMock(RendererInterface::class);
        $defaultRenderer->expects($this->never())->method('render');
        $fileRenderer = $this->createMock(RendererInterface::class);
        $fileRenderer->expects($this->once())->method('render')->with($response);

        (new DelegatingRenderer($defaultRenderer, $fileRenderer))->render($response);
    }

    public function testOtherResponsesGoToDefaultRenderer(): void
    {
        $response = new NotFoundApivalkResponse();

        $defaultRenderer = $this->createMock(RendererInterface::class);
        $defaultRenderer->expects($this->once())->method('render')->with($response);
        $fileRenderer = $this->createMock(RendererInterface::class);
        $fileRenderer->expects($this->never())->method('render');

        (new DelegatingRenderer($defaultRenderer, $fileRenderer))->render($response);
    }
}
