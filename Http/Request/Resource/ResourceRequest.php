<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Resource;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;

class ResourceRequest extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        return new ApivalkRequestDocumentation();
    }
}
