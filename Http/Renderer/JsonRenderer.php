<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Renderer;

use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

final class JsonRenderer implements RendererInterface
{
    public function render(AbstractApivalkResponse $response): void
    {
        $headers = $response->getHeaders();
        $headers['Content-Type'] = 'application/json';

        $responseArray = $response->toArray();

        foreach ($headers as $name => $value) {
            header(\sprintf('%s: %s', $name, $value));
        }

        http_response_code($response->getStatusCode());

        $responsePagination = $response->getResponsePagination();
        if ($responsePagination !== null) {
            $responseArray['pagination'] = $responsePagination->toArray();
        }

        echo json_encode($responseArray);
    }
}
