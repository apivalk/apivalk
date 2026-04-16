<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Population;

use apivalk\apivalk\Http\Request\Population\Strategy\AuthIdentityPopulationStrategy;
use apivalk\apivalk\Http\Request\Population\Strategy\BodyParameterPopulationStrategy;
use apivalk\apivalk\Http\Request\Population\Strategy\FilePopulationStrategy;
use apivalk\apivalk\Http\Request\Population\Strategy\FilteringPopulationStrategy;
use apivalk\apivalk\Http\Request\Population\Strategy\HeaderPopulationStrategy;
use apivalk\apivalk\Http\Request\Population\Strategy\IpPopulationStrategy;
use apivalk\apivalk\Http\Request\Population\Strategy\MethodPopulationStrategy;
use apivalk\apivalk\Http\Request\Population\Strategy\PaginationPopulationStrategy;
use apivalk\apivalk\Http\Request\Population\Strategy\PathParameterPopulationStrategy;
use apivalk\apivalk\Http\Request\Population\Strategy\PopulationStrategyInterface;
use apivalk\apivalk\Http\Request\Population\Strategy\QueryParameterPopulationStrategy;
use apivalk\apivalk\Http\Request\Population\Strategy\SortingPopulationStrategy;

class RequestPopulationStrategyCollection
{
    /** @var PopulationStrategyInterface[] */
    private $strategies = [];

    public function __construct()
    {
        $this->strategies[] = new MethodPopulationStrategy();
        $this->strategies[] = new HeaderPopulationStrategy();
        $this->strategies[] = new QueryParameterPopulationStrategy();
        $this->strategies[] = new PathParameterPopulationStrategy();
        $this->strategies[] = new BodyParameterPopulationStrategy();
        $this->strategies[] = new FilePopulationStrategy();
        $this->strategies[] = new AuthIdentityPopulationStrategy();
        $this->strategies[] = new IpPopulationStrategy();
        $this->strategies[] = new SortingPopulationStrategy();
        $this->strategies[] = new FilteringPopulationStrategy();
        $this->strategies[] = new PaginationPopulationStrategy();
    }

    /**
     * @return PopulationStrategyInterface[]
     */
    public function getAll(): array
    {
        return $this->strategies;
    }
}
