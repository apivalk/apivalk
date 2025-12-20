<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Parameter;

class ParameterBag implements \IteratorAggregate, \Countable
{
    /** @var Parameter[] */
    private $parameters = [];

    public function set(Parameter $parameter): void
    {
        $this->parameters[$parameter->getName()] = $parameter;
    }

    public function has(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    public function get(string $key): ?Parameter
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * @return \Iterator<string, Parameter>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->parameters);
    }

    public function count(): int
    {
        return \count($this->parameters);
    }

    /**
     * Magic getter to direct access values of a parameter bag.
     *
     * $parameterBag->location_id is the same as $parameterBag->get('location_id')
     *
     * In requests, you can access all different bags like:
     * $request->body()
     * $request->query()
     * $request->file()
     * $request->path()
     * $request->header()
     *
     * @return bool|float|int|string|array|null
     */
    public function __get(string $key)
    {
        $parameter = $this->get($key);
        if ($parameter === null) {
            return null;
        }

        return $parameter->getValue();
    }
}
