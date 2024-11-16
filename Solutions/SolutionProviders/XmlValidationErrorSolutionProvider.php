<?php

namespace Swissup\Ignition\Solutions\SolutionProviders;

use Magento\Framework\Config\Dom\ValidationException;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Throwable;

class XmlValidationErrorSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return str_starts_with($throwable->getMessage(), 'The XML in file "');
    }

    public function getSolutions(Throwable $throwable): array
    {
        preg_match('/The XML in file "(?<filepath>.+?)" is invalid:\s+Element \'(?<element>.+?)\': (?<message>.+)\.\s+Line: (?<line>\d+)/', $throwable->getMessage(), $matches);

        if (!$matches) {
            return [];
        }

        $message = strtr($matches['message'], [
            "in unique identity-constraint 'uniqueNames'" => '',
        ]);
        $filepath = strtr($matches['filepath'], [
            BP => '',
            DIRECTORY_SEPARATOR => DIRECTORY_SEPARATOR . '&#8203;',
        ]);
        $solution = sprintf('%s in %s on line %s', $message, $filepath, $matches['line']);

        return [
            BaseSolution::create('XML validation error')->setSolutionDescription($solution),
        ];
    }
}
