<?php

namespace Swissup\Ignition\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;

class IgnitionFactory
{
    public function __construct(
        private State $state,
        private ScopeConfigInterface $config,
        private array $solutionProviders = []
    ) {
    }

    public function create(): Ignition
    {
        $flareApiKey = $this->config->getValue('swissup_ignition/general/api_key', ScopeInterface::SCOPE_WEBSITE);

        return (new Ignition())
            ->applicationPath(BP)
            ->sendToFlare($flareApiKey)
            ->addSolutionProviders($this->solutionProviders)
            ->runningInProductionEnvironment($this->state->getMode() === State::MODE_PRODUCTION);
    }
}
