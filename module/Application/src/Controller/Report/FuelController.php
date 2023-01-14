<?php

namespace Application\Controller\Report;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;

class FuelController extends \Laminas\Mvc\Controller\AbstractActionController
{
    private ServiceManager $serviceManager;

    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    public function getService($name)
    {
        return $this->serviceManager->get($name);
    }

    public function GenerateAction()
    {
        $viewData = [];
        return new ViewModel($viewData);
    }
}
