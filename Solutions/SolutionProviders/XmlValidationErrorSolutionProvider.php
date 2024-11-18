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
        // The XML in file "/view/adminhtml/image.xml" is invalid: Element 'attribute': Duplicate key-sequence ['width'] in unique identity-constraint 'uniqueNames'. Line: 10 The xml was:
        // The XML in file "/view/adminhtml/form.xml" is invalid: xmlParseEntityRef: no name Line: 120 xmlParseEntityRef: no name Line: 120 Verify the XML and try again.
        preg_match('/The XML in file "(?<filepath>.+?)" is invalid:\s+(?<message>.+?)\s+Line: (?<line>\d+)/', $throwable->getMessage(), $matches);

        if (!$matches) {
            return [];
        }

        $message = strtr($matches['message'], [
            "in unique identity-constraint 'uniqueNames'." => '',
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
