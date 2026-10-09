<?php

namespace Swissup\Ignition\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use Spatie\FlareClient\Flare;

class IgnitionFactory
{
    public function __construct(
        private State $state,
        private ScopeConfigInterface $config,
        private ErrorPageScript $errorPageScript,
        private array $solutionProviders = []
    ) {
    }

    public function create(): Ignition
    {
        $flareApiKey = $this->config->getValue('swissup_ignition/general/api_key', ScopeInterface::SCOPE_WEBSITE);
        $isProduction = $this->state->getMode() !== State::MODE_DEVELOPER;
        $ignition = (new Ignition())
            ->applicationPath(BP)
            ->sendToFlare($flareApiKey)
            ->addSolutionProviders($this->solutionProviders)
            ->addCustomHtmlToBody($this->errorPageScript->getHtml())
            ->runningInProductionEnvironment($isProduction);

        // Reports are sent to Flare in production only.
        // Stack frame arguments may contain passwords, e.g. authenticate($username, $password)
        if ($isProduction) {
            $ignition->configureFlare(fn (Flare $flare) => $flare
                ->withStackFrameArguments(false)
                ->registerMiddleware(new CensorSensitiveData()));
        }

        return $ignition;
    }
}
