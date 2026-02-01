<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Response;

use apivalk\apivalk\Documentation\Property\AbstractPropertyCollection;
use apivalk\apivalk\Documentation\Property\StringProperty;

class ErrorObjectPropertyCollection extends AbstractPropertyCollection
{
    public function __construct(string $mode)
    {
        $this->addProperty(new StringProperty('errorKey', 'A key identifying the error'));
        $this->addProperty(new StringProperty('message', 'The error message'));
    }
}
