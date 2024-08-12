<?php

namespace Swissup\Ignition\Solutions\SolutionProviders;

use Magento\Framework\Config\Dom\ValidationException;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Throwable;

class TypeDefinitionIsAbstractSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException
            && str_contains($throwable->getMessage(), 'The type definition is abstract');
    }

    public function getSolutions(Throwable $throwable): array
    {
        $solution = 'Ensure that correct `xsi:type` is used';

        // Element 'item': The type definition is abstract. Line: ...
        preg_match('/The type definition is abstract\.\s+Line: (?<line>\d+)/', $throwable->getMessage(), $matches);

        if ($matches) {
            $solution .= sprintf(' on line %s', $matches['line']);
        }

        return [
            BaseSolution::create('Missing xsi:type definition')->setSolutionDescription($solution),
        ];
    }
}
