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
        return array_map(function ($frame) {
            $frame['application_frame'] = $this->isApplicationFrame($frame);
            return $frame;
        }, parent::stracktraceAsArray());
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
            '/generated/code/',
            '/lib/internal/',
            '/vendor/magento/',
            '/index.php',
        ];

        foreach ($vendorPaths as $path) {
            if (str_contains($frame['file'], $path)) {
                return false;
            }
        }

        return true;
    }
}
