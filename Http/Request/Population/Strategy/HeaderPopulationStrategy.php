<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Population\Strategy;

use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;

class HeaderPopulationStrategy implements PopulationStrategyInterface
{
    public function populate(
        AbstractApivalkRequest $request,
        RequestPopulationContext $context
    ): void {
        $request->setHeaderBag(ParameterBagFactory::createHeaderBag());
    }
}
