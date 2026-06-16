<?php

namespace Swissup\Ignition\Block;

use Magento\Framework\App\State;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Js extends Template
{
    protected $_template = 'Swissup_Ignition::js.phtml';

    public function getTemplate()
    {
        if ($this->_appState->getMode() === State::MODE_DEVELOPER ||
            !$this->isJsReportingEnabled() ||
            !$this->getProjectKey()
        ) {
            return '';
        }
        return parent::getTemplate();
    }

    public function getProjectKey()
    {
        return $this->_scopeConfig->getValue('swissup_ignition/general/api_key', ScopeInterface::SCOPE_WEBSITE);
    }

    public function isJsReportingEnabled()
    {
        return $this->_scopeConfig->isSetFlag('swissup_ignition/general/enable_js_reporting', ScopeInterface::SCOPE_STORE);
    }
}
