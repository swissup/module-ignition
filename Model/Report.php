<?php

namespace Swissup\Ignition\Model;

use Spatie\FlareClient\Report as FlareReport;

class Report extends FlareReport
{
    public static function createFromFlareReport(FlareReport $report)
    {
        return (new self())
            ->setApplicationPath($report->getApplicationPath())
            ->throwable($report->getThrowable())
            ->useContext((fn() => $this->context)->call($report))
            ->exceptionClass($report->getExceptionClass())
            ->message($report->getMessage())
            ->stacktrace($report->getStacktrace())
            ->exceptionContext($report->getThrowable())
            ->setApplicationVersion($report->getApplicationVersion());
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function stracktraceAsArray(): array
    {
        $result = [];
        $applicationFrames = [];

        foreach (parent::stracktraceAsArray() as $frame) {
            $frame['application_frame'] = $this->isApplicationFrame($frame);

            if ($frame['application_frame']) {
                $applicationFrames[] = $frame;
            }

            $result[] = $frame;
        }

        if (!array_filter($applicationFrames, fn ($frame) => !$this->isSkippableFrame($frame))) {
            $result = array_map(function ($frame) {
                $frame['application_frame'] = !$this->isGeneratedCodeFrame($frame);
                return $frame;
            }, $result);
        }

        return $result;
    }

    /**
     * We don't want to collapse everything from vendor folder
     * because we develop the packages in the vendor folder.
     *
     * Additionally we want to collapse some other folders.
     */
    private function isApplicationFrame(array $frame): bool
    {
        $vendorPaths = [
            '/app/code/Magento/',
            '/lib/internal/',
            '/vendor/magento/',
            '/index.php',
        ];

        foreach ($vendorPaths as $path) {
            if (str_contains($frame['file'], $path)) {
                return false;
            }
        }

        if ($this->isGeneratedCodeFrame($frame)) {
            return false;
        }

        return true;
    }

    private function isGeneratedCodeFrame(array $frame): bool
    {
        return str_contains($frame['file'], '/generated/code/');
    }

    private function isSkippableFrame(array $frame): bool
    {
        return str_starts_with($frame['method'], 'around')
            && str_contains($frame['code_snippet'][$frame['line_number']], '$proceed(');
    }
}
