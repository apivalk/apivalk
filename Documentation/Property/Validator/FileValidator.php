<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\FileProperty;
use apivalk\apivalk\Http\Request\File\File;

/**
 * Validates an uploaded file against its FileProperty. It deliberately does not extend AbstractValidator: that
 * contract validates a Parameter, while an upload is a File living in the request's file bag. The result type is
 * shared, so file errors surface exactly like every other validation error.
 *
 * @see \apivalk\apivalk\Middleware\RequestValidationMiddleware
 */
final class FileValidator
{
    private FileProperty $property;

    public function __construct(FileProperty $property)
    {
        $this->property = $property;
    }

    public function getProperty(): FileProperty
    {
        return $this->property;
    }

    public function validate(File $file): ValidatorResult
    {
        if (!$file->isValid()) {
            // PHP reports its own size limits as upload errors, which are a size problem rather than a failure.
            if ($file->getError() === UPLOAD_ERR_INI_SIZE || $file->getError() === UPLOAD_ERR_FORM_SIZE) {
                return new ValidatorResult(false, ValidatorResult::FILE_IS_LARGER_THAN_MAX_SIZE);
            }

            return new ValidatorResult(false, ValidatorResult::FILE_UPLOAD_FAILED);
        }

        $maxSizeInBytes = $this->property->getMaxSizeInBytes();

        if ($maxSizeInBytes !== null && $file->getSize() > $maxSizeInBytes) {
            return new ValidatorResult(false, ValidatorResult::FILE_IS_LARGER_THAN_MAX_SIZE);
        }

        $allowedMediaTypes = $this->property->getAllowedMediaTypes();

        if (\count($allowedMediaTypes) > 0
            && !\in_array((string)$file->getDetectedMediaType(), $allowedMediaTypes, true)) {
            return new ValidatorResult(false, ValidatorResult::FILE_MEDIA_TYPE_IS_NOT_ALLOWED);
        }

        return new ValidatorResult(true);
    }
}
