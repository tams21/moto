<?php

declare(strict_types=1);

namespace Application;

use Application\Listener\DispatchListener;
use Laminas\Mvc\MvcEvent;
use Laminas\Authentication;

class Module
{
    public function getConfig(): array
    {
        /** @var array $config */
        $config = include __DIR__ . '/../config/module.config.php';
        return $config;
    }
    
    public function onBootstrap(MvcEvent $event) {
        $application = $event->getApplication();
        
        // Create and register layout listener
        $listener = new DispatchListener();
        $listener->attach($application->getEventManager());
    }
    
    public function getServiceConfig()
    {
        return [
            'factories' => [
                Authentication\Storage\Session::class => function ($container) {
                    return new Authentication\Storage\Session();
                },
                AppAuthAdapter::class => function ($container) {
                    return new AppAuthAdapter();
                },
                Authentication\AuthenticationServiceInterface::class => function ($container) {
                    $storage = $container->get(Authentication\Storage\Session::class);
                    return new Authentication\AuthenticationService($storage);
                },
                ]
                ];
    }
}
