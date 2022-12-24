<?php

namespace Application\Listener;

use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;

class DispatchListener
{

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1001)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_DISPATCH,
            [$this, 'protectRestrictedPaths'],
            $priority
        );
    }

    public function protectRestrictedPaths(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        if (! $routeMatch) {
            return;
        }

        $routeName = $routeMatch->getMatchedRouteName();

        if ($routeName === 'application' && $routeMatch->getParam('controller') === 'auth') {
            return;
        }
        $auth = $event->getApplication()->getServiceManager()->get(AuthenticationServiceInterface::class);
        if ($auth->hasIdentity() === true) {
            return;
        }

        $params = [
            'controller' => 'auth',
            'action' => 'login'
        ];
        $options = [
            'name' => 'application',
            'query' => [
                'prevurl' => (string)$event->getRequest()->getUri(),
            ]
        ];

        $url = $event->getRouter()->assemble($params, $options);

        $response = new \Laminas\Http\PhpEnvironment\Response();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        return $response;
    }
}
