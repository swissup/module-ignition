<?php

namespace Swissup\Ignition\Plugin;

use Closure;
use Magento\Framework\App\Http as AppHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Swissup\Ignition\Model\Ignition;
use Swissup\Ignition\Model\IgnitionFactory;
use Throwable;

class App
{
    private Ignition $ignition;

    public function __construct(
        private ResponseHttp $response,
        private IgnitionFactory $ignitionFactory
    ) {
    }

    public function aroundLaunch(AppHttp $subject, Closure $proceed)
    {
        $this->registerErrorHandler();

        try {
            return $proceed();
        } catch (Throwable $e) {
            // Can't rely on Magento's app->catchException in Magento/Framework/App/Bootstrap::run
            // because it doesn't handle Throwable types.
            $this->handleThrowable($e);
        }
    }

    private function registerErrorHandler()
    {
        $this->ignition = $this->ignitionFactory->create()->register();
    }

    private function handleThrowable(Throwable $exception)
    {
        ob_start();
        $this->ignition->handleException($exception);
        $this->response->setHttpResponseCode(500)
            ->setBody(ob_get_clean())
            ->sendResponse();

        exit(1);
    }
}
