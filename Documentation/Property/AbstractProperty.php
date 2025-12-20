<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

use apivalk\apivalk\Documentation\Property\Validator\AbstractValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorFactory;

abstract class AbstractProperty
{
    /** @var string */
    private $propertyName;
    /** @var string */
    private $propertyDescription;
    /** @var bool */
    private $isRequired = true;
    /** @var string|null */
    private $example;
    /** @var AbstractValidator[] */
    private $validators = [];

    /** Is the swagger type definition of the value. Required for the documentation. */
    abstract public function getType(): string;

    /**
     * Is the PHP type definition of the value. It is required for the documentation, validation, and type safe magical getter in request classes.
     *
     * @see \apivalk\apivalk\Http\Request\AbstractApivalkRequest::populate
     */
    abstract public function getPhpType(): string;

    abstract public function getDocumentationArray(): array;

    public function __construct(
        string $propertyName,
        string $propertyDescription = ''
    ) {
        $this->propertyName = $propertyName;
        $this->propertyDescription = $propertyDescription;

        $this->addValidator(ValidatorFactory::create($this));
    }

    final public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    final public function getPropertyDescription(): string
    {
        return $this->propertyDescription;
    }

    final public function isRequired(): bool
    {
        return $this->isRequired;
    }

    final public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    final public function getExample(): ?string
    {
        return $this->example;
    }

    final public function setExample(string $example): self
    {
        $this->example = $example;

        return $this;
    }

    final public function addValidator(AbstractValidator $validator): self
    {
        $this->validators[] = $validator;

        return $this;
    }

    final public function getValidators(): array
    {
        return $this->validators;
    }
}
