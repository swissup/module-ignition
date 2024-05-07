<?php

namespace Swissup\Ignition\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url;

class Router implements \Magento\Framework\App\RouterInterface
{
    protected ActionFactory $actionFactory;

    public function __construct(
        ActionFactory $actionFactory
    ) {
        $this->actionFactory = $actionFactory;
    }

    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');

        if ($identifier !== '_ignition/update-config') {
            return false;
        }

        $request->setRouteName('_ignition')
            ->setControllerName('config')
            ->setActionName('save')
            ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

        return $this->actionFactory->create(Forward::class);
    }
}
