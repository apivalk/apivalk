<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Response;

use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;
use apivalk\apivalk\Documentation\Property\AbstractPropertyCollection;

class ErrorApivalkObject extends AbstractObjectProperty
{
    /** @var string */
    private $name = 'error';
    /** @var string */
    private $error = 'Error';

    final public function __construct()
    {
        parent::__construct('error', 'Error');
    }

    /**
     * @param array{name: string, error: string} $array
     */
    public function populateByArray(array $array): void
    {
        $this->name = $array['name'];
        $this->error = $array['error'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getPropertyCollection(): AbstractPropertyCollection
    {
        return new ErrorApivalkObjectPropertyCollection(AbstractPropertyCollection::MODE_VIEW);
    }
}
