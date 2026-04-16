<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Population\Strategy;

use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Util\IpResolver;

class IpPopulationStrategy implements PopulationStrategyInterface
{
    public function populate(AbstractApivalkRequest $request, RequestPopulationContext $context): void
    {
        $request->setIp(IpResolver::getClientIp());
    }
}
