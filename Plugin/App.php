<?php

namespace Swissup\Ignition\Plugin;

use Closure;
use Magento\Framework\App\Http as AppHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Swissup\Ignition\Model\Ignition;
use Swissup\Ignition\Model\IgnitionFactory;
use Swissup\Ignition\Model\IgnitionNonceProvider;
use Throwable;

class App
{
    private Ignition $ignition;

    public function __construct(
        private ResponseHttp $response,
        private IgnitionFactory $ignitionFactory,
        private IgnitionNonceProvider $nonceProvider
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

        $html = str_replace(
            '<script>',
            "<script nonce='{$this->nonceProvider->generateNonce()}'>",
            ob_get_clean()
        );

        $this->response->setHttpResponseCode(500)
            ->setBody($html)
            ->sendResponse();

        exit(1); // phpcs:ignore
    }
}
