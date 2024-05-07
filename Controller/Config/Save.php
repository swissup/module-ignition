<?php

namespace Swissup\Ignition\Controller\Config;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Spatie\Ignition\Config\IgnitionConfig;

class Save implements HttpPostActionInterface
{
    public function __construct(
        private RequestInterface $request,
        private JsonResultFactory $resultFactory,
        private JsonSerializer $serializer
    ) {
    }

    public function execute()
    {
        $response = $this->resultFactory->create();

        if (!$this->request->isPost()) {
            return $response->setHttpResponseCode(405)->setData(['error' => 'Method is not allowed']);
        }

        try {
            $data = $this->serializer->unserialize($this->request->getContent());
            $data = array_intersect_key($data, array_flip([
                'editor',
                'theme',
                'hide_solutions',
            ]));

            $config = new IgnitionConfig();
            if (!$config->saveValues(array_merge($config->getConfigOptions(), $data))) {
                throw new Exception('Unable to save Ignition config');
            }

            $response->setData(true);
        } catch (Exception $e) {
            $response->setHttpResponseCode(400)->setData(['error' => $e->getMessage()]);
        }

        return $response;
    }
}
