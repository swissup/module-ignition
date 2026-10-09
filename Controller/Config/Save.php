<?php

namespace Swissup\Ignition\Controller\Config;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Spatie\Ignition\Config\IgnitionConfig;

class Save implements HttpPostActionInterface
{
    public function __construct(
        private RequestInterface $request,
        private JsonResultFactory $resultFactory,
        private JsonSerializer $serializer,
        private State $appState
    ) {
    }

    public function execute()
    {
        $response = $this->resultFactory->create();

        // The error page is rendered in developer mode only
        if ($this->appState->getMode() !== State::MODE_DEVELOPER) {
            return $response->setHttpResponseCode(404)->setData(['error' => 'Not found']);
        }

        if (!$this->request->isPost()) {
            return $response->setHttpResponseCode(405)->setData(['error' => 'Method is not allowed']);
        }

        try {
            $config = new IgnitionConfig();
            $data = $this->validate($this->serializer->unserialize($this->request->getContent()));

            if (!$config->saveValues(array_merge($config->getConfigOptions(), $data))) {
                throw new Exception('Unable to save Ignition config');
            }

            $response->setData(true);
        } catch (Exception $e) {
            $response->setHttpResponseCode(400)->setData(['error' => $e->getMessage()]);
        }

        return $response;
    }

    /**
     * Theme is rendered unescaped by the error page, so allow known values only.
     * Editor can be a custom one from editor_options in ~/.ignition.json.
     */
    private function validate(mixed $data): array
    {
        if (!is_array($data)) {
            throw new Exception('Invalid data');
        }

        $data = array_intersect_key($data, array_flip(['editor', 'theme', 'hide_solutions']));
        $isValid = [
            'editor' => fn ($value) => is_string($value) && preg_match('/^[\w-]+\z/', $value),
            'theme' => fn ($value) => in_array($value, ['light', 'dark', 'auto'], true),
            'hide_solutions' => fn ($value) => is_bool($value),
        ];

        foreach ($data as $key => $value) {
            if (!$isValid[$key]($value)) {
                throw new Exception("Invalid value for '{$key}'");
            }
        }

        return $data;
    }
}
