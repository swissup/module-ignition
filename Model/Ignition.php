<?php

namespace Swissup\Ignition\Model;

use Spatie\FlareClient\Report as FlareReport;
use Spatie\Ignition\ErrorPage\ErrorPageViewModel;
use Spatie\Ignition\ErrorPage\Renderer;
use Throwable;

class Ignition extends \Spatie\Ignition\Ignition
{
    private IgnitionNonceProvider $nonceProvider;

    protected function createReport(Throwable $throwable): Report
    {
        return Report::createFromFlareReport(parent::createReport($throwable));
    }

    public function renderException(Throwable $throwable, ?FlareReport $report = null): void
    {
        $this->setUpFlare();

        $report ??= $this->createReport($throwable);

        $viewModel = new ErrorPageViewModel(
            $throwable,
            $this->ignitionConfig,
            $report,
            $this->solutionProviderRepository->getSolutionsForThrowable($throwable),
            $this->solutionTransformerClass,
            $this->customHtmlHead,
            $this->customHtmlBody,
        );

        (new Renderer())->render(
            [
                'viewModel' => $viewModel,
                'nonceProvider' => $this->nonceProvider,
            ],
            __DIR__ . '/../view/base/templates/ignition.phtml'
        );
    }

    public function nonceProvider(IgnitionNonceProvider $provider)
    {
        $this->nonceProvider = $provider;
        return $this;
    }
}
