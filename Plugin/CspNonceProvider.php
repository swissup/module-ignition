<?php

namespace Swissup\Ignition\Plugin;

use Swissup\Ignition\Model\IgnitionNonceProvider;

class CspNonceProvider
{
    public function __construct(
        private IgnitionNonceProvider $nonceProvider
    ) {
    }

    public function afterGenerateNonce(
        \Magento\Csp\Helper\CspNonceProvider $subject,
        string $nonce
    ) {
        $this->nonceProvider->saveNonce($nonce);
        return $nonce;
    }
}
