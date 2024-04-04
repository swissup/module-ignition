<?php

namespace Swissup\Ignition\Solutions\SolutionProviders;

use Magento\Framework\DB\Adapter\TableNotFoundException;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Swissup\Ignition\Solutions\RunSetupUpdgradeSolution;
use Throwable;

class DbTableNotFoundSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return $throwable instanceof TableNotFoundException;
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [
            new RunSetupUpdgradeSolution('A table was not found'),
        ];
    }
}
