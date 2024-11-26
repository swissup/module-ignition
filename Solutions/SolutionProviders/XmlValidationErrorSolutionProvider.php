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
        return str_starts_with($throwable->getMessage(), 'The XML in file "')
            || $throwable instanceof ValidationException;
    }

    public function getSolutions(Throwable $throwable): array
    {
        $message = $throwable->getMessage();

        // The XML in file "/view/adminhtml/image.xml" is invalid: Element 'attribute': Duplicate key-sequence ['width'] in unique identity-constraint 'uniqueNames'. Line: 10 The xml was:
        // The XML in file "/view/adminhtml/form.xml" is invalid: xmlParseEntityRef: no name Line: 120 xmlParseEntityRef: no name Line: 120 Verify the XML and try again.
        preg_match('/The XML in file "(?<filepath>.+?)" is invalid:\s+(?<message>.+?)\s+Line: (?<line>\d+)/', $message, $matches);
        if ($matches) {
            return $this->createSolutionFromTheXmlInFileError($matches);
        }

        preg_match('/(?<message>.+)\.\s+Line: (?<line>\d+)/', $message, $matches);
        if ($matches) {
            if (str_contains($message, 'The type definition is abstract')) {
                $matches['message'] = 'Ensure that correct `xsi:type` is used';
            } else if (str_contains($message, 'Expected is ( updater )')) {
                // Element 'item': This element is not expected. Expected is ( updater ). Line: 1495 The xml was: 1490: <item ..
                $matches['message'] = 'Check if proper `xsi:type` value is used for **parent element**';
            }

            $solution = sprintf('%s on line %s', $matches['message'], $matches['line']);

            $startStr = $matches['line'] . ': ';
            $endStr = $matches['line'] + 1 . ': ';
            $startPos = strpos($message, $startStr);
            $endPos = strpos($message, $endStr);
            if ($startPos && $endPos) {
                $line = trim(substr(
                    $message,
                    $startPos + strlen($startStr),
                    $endPos - $startPos - strlen($startStr)
                ));
                $solution .= ': ' . $line;
            }

            return [
                BaseSolution::create('XML validation error')->setSolutionDescription($solution),
            ];
        }
    }

    private function createSolutionFromTheXmlInFileError(array $matches)
    {
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
