<?php

namespace Swissup\Ignition\Solutions\SolutionProviders;

use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Swissup\Ignition\Solutions\RunSetupUpdgradeSolution;
use Throwable;
use Zend_Db_Statement_Exception;

class DbColumnNotFoundSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        if (!$throwable instanceof Zend_Db_Statement_Exception) {
            return false;
        }

        // return $throwable->getCode() === 42;
        return str_contains($throwable->getMessage(), 'Column not found:');
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [
            new RunSetupUpdgradeSolution('A column was not found'),
        ];
    }
}
