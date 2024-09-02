<?php

namespace Swissup\Ignition\Solutions\SolutionProviders;

use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Swissup\Ignition\Solutions\RunSetupUpdgradeSolution;
use Throwable;

class UpgradeYourDatabaseSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return str_contains($throwable->getMessage(), 'Please upgrade your database:');
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [
            new RunSetupUpdgradeSolution('Please upgrade your database'),
        ];
    }
}
