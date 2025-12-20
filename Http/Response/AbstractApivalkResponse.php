<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;

abstract class AbstractApivalkResponse
{
    /** @var int */
    public const HTTP_100_CONTINUE = 100;
    /** @var int */
    public const HTTP_101_SWITCHING_PROTOCOLS = 101;
    /** @var int */
    public const HTTP_200_OK = 200;
    /** @var int */
    public const HTTP_201_CREATED = 201;
    /** @var int */
    public const HTTP_202_ACCEPTED = 202;
    /** @var int */
    public const HTTP_203_NONAUTHORITATIVE_INFORMATION = 203;
    /** @var int */
    public const HTTP_204_NO_CONTENT = 204;
    /** @var int */
    public const HTTP_205_RESET_CONTENT = 205;
    /** @var int */
    public const HTTP_206_PARTIAL_CONTENT = 206;
    /** @var int */
    public const HTTP_300_MULTIPLE_CHOICES = 300;
    /** @var int */
    public const HTTP_301_MOVED_PERMANENTLY = 301;
    /** @var int */
    public const HTTP_302_FOUND = 302;
    /** @var int */
    public const HTTP_303_SEE_OTHER = 303;
    /** @var int */
    public const HTTP_304_NOT_MODIFIED = 304;
    /** @var int */
    public const HTTP_305_USE_PROXY = 305;
    /** @var int */
    public const HTTP_306_UNUSED = 306;
    /** @var int */
    public const HTTP_307_TEMPORARY_REDIRECT = 307;
    /** @var int */
    public const HTTP_400_ERROR_CODES_BEGIN_AT = 400;
    /** @var int */
    public const HTTP_400_BAD_REQUEST = 400;
    /** @var int */
    public const HTTP_401_UNAUTHORIZED = 401;
    /** @var int */
    public const HTTP_402_PAYMENT_REQUIRED = 402;
    /** @var int */
    public const HTTP_403_FORBIDDEN = 403;
    /** @var int */
    public const HTTP_404_NOT_FOUND = 404;
    /** @var int */
    public const HTTP_405_METHOD_NOT_ALLOWED = 405;
    /** @var int */
    public const HTTP_406_NOT_ACCEPTABLE = 406;
    /** @var int */
    public const HTTP_407_PROXY_AUTHENTICATION_REQUIRED = 407;
    /** @var int */
    public const HTTP_408_REQUEST_TIMEOUT = 408;
    /** @var int */
    public const HTTP_409_CONFLICT = 409;
    /** @var int */
    public const HTTP_410_GONE = 410;
    /** @var int */
    public const HTTP_411_LENGTH_REQUIRED = 411;
    /** @var int */
    public const HTTP_412_PRECONDITION_FAILED = 412;
    /** @var int */
    public const HTTP_413_REQUEST_ENTITY_TOO_LARGE = 413;
    /** @var int */
    public const HTTP_414_REQUEST_URI_TOO_LONG = 414;
    /** @var int */
    public const HTTP_415_UNSUPPORTED_MEDIA_TYPE = 415;
    /** @var int */
    public const HTTP_416_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    /** @var int */
    public const HTTP_417_EXPECTATION_FAILED = 417;
    /** @var int */
    public const HTTP_422_UNPROCESSABLE_ENTITY = 422;
    /** @var int */
    public const HTTP_429_TOO_MANY_REQUESTS = 429;
    /** @var int */
    public const HTTP_500_INTERNAL_SERVER_ERROR = 500;
    /** @var int */
    public const HTTP_501_NOT_IMPLEMENTED = 501;
    /** @var int */
    public const HTTP_502_BAD_GATEWAY = 502;
    /** @var int */
    public const HTTP_503_SERVICE_UNAVAILABLE = 503;
    /** @var int */
    public const HTTP_504_GATEWAY_TIMEOUT = 504;
    /** @var int */
    public const HTTP_505_VERSION_NOT_SUPPORTED = 505;

    /** @var array */
    private $headers = [];
    /** @var null|ResponsePagination */
    private $responsePagination;

    abstract public static function getDocumentation(): ApivalkResponseDocumentation;

    abstract public static function getStatusCode(): int;

    abstract public function toArray(): array;

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function addPagination(ResponsePagination $pagination): void
    {
        $this->responsePagination = $pagination;
    }

    public function hasPagination(): bool
    {
        return $this->responsePagination !== null;
    }

    public function getResponsePagination(): ?ResponsePagination
    {
        return $this->responsePagination;
    }
}
