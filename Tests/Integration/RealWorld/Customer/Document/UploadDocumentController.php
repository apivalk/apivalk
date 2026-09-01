<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Document;

use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadRequestApivalkResponse;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;
use Tests\Integration\RealWorld\Customer\Document\Request\DocumentUploadRequest;

/**
 * @extends AbstractApivalkController<DocumentUploadRequest>
 */
class UploadDocumentController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return Route::post('/v1/api/customers/{customer_id}/documents')
            ->description('Stores a document for a customer')
            ->pathProperty(
                (new IntegerProperty('customer_id', 'Customer integer ID'))->setMinimumValue(1)
            )
            ->routeAuthorization(
                new RouteAuthorization('bearer', ['api:customers'], ['api:customers:create'])
            );
    }

    public static function getRequestClass(): string
    {
        return DocumentUploadRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [DocumentUploadedResponse::class, BadRequestApivalkResponse::class];
    }

    /** @var DocumentUploadRequest $request */
    public function __invoke(DocumentUploadRequest $request): AbstractApivalkResponse
    {
        // Typed access through the generated file shape.
        $file = $request->file()->file;

        // The middleware rejects a missing file, so reaching this without one would be a framework bug.
        if ($file === null) {
            return new BadRequestApivalkResponse();
        }

        return new DocumentUploadedResponse(
            [
                'customer_id' => (int)$request->path()->get('customer_id')->getValue(),
                'document_type' => $request->body()->get('document_type')->getValue(),
                'field_name' => $file->getFieldName(),
                'filename' => $file->getName(),
                'size' => $file->getSize(),
                'detected_media_type' => $file->getDetectedMediaType(),
                'contents_md5' => md5($file->getContents()),
            ]
        );
    }
}
