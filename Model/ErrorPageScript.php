<?php

namespace Swissup\Ignition\Model;

use Magento\Framework\Component\ComponentRegistrar;

class ErrorPageScript
{
    private const MODULE_NAME = 'Swissup_Ignition';

    private const SCRIPT_PATH = '/view/base/web/js/copy-buttons.js';

    public function __construct(
        private ComponentRegistrar $componentRegistrar
    ) {
    }

    /**
     * HTML to append to the error page body: the "copy stack trace / message"
     * widget. The <script> tag is rewritten with a CSP nonce by Plugin\App.
     */
    public function getHtml(): string
    {
        $script = $this->getScriptContents();

        if ($script === '') {
            return '';
        }

        return "<script>{$script}</script>";
    }

    private function getScriptContents(): string
    {
        $modulePath = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            self::MODULE_NAME
        );

        if (!$modulePath) {
            return '';
        }

        $file = $modulePath . self::SCRIPT_PATH;

        if (!is_file($file)) {
            return '';
        }

        return (string) file_get_contents($file);
    }
}
