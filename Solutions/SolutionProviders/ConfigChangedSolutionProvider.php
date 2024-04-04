<?php

namespace Swissup\Ignition\Solutions\SolutionProviders;

use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Throwable;

class ConfigChangedSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return str_contains($throwable->getMessage(), 'app:config:import')
            && str_contains($throwable->getMessage(), 'setup:upgrade');
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [
            BaseSolution::create('Synchronize configuration')
                ->setSolutionDescription('Run `bin/magento app:config:import` or `bin/magento setup:upgrade` command'),
        ];
    }
}
