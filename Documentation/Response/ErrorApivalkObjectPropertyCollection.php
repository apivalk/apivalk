<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Response;

use apivalk\apivalk\Documentation\Property\AbstractPropertyCollection;
use apivalk\apivalk\Documentation\Property\StringProperty;

class ErrorApivalkObjectPropertyCollection extends AbstractPropertyCollection
{
    public function __construct(string $mode)
    {
        $this->addProperty(new StringProperty('name', 'The field name'));
        $this->addProperty(new StringProperty('error', 'The error message'));
    }
}
