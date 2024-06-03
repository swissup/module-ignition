<?php

namespace Swissup\Ignition\Solutions\SolutionProviders;

use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use ReflectionException;
use Throwable;

class ConstructorArgumentExceptionSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return $throwable instanceof ReflectionException
            && str_contains($throwable->getMessage(), 'Impossible to process constructor argument');
    }

    public function getSolutions(Throwable $throwable): array
    {
        // Impossible to process constructor argument Parameter #0 [ <required> ... $argName ] of ... class
        preg_match('/\[.*(?<argument>\$\w+)\s+\] of (?<class>.*) class/', $throwable->getMessage(), $matches);

        if ($matches) {
            $description = sprintf(
                '`%s` argument is invalid in `%s` class',
                $matches['argument'],
                $matches['class']
            );
        } else {
            $description = 'Ensure that all constructor arguments are valid';
        }

        return [
            BaseSolution::create('Invalid constructor argument')
                ->setSolutionDescription($description),
        ];
    }
}
