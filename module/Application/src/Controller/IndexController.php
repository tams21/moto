<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Model\UserTable;

class IndexController extends AbstractActionController
{
    private $userTable;
    
    public function __construct(UserTable $userTable)
    {
        $this->userTable=$userTable;
    }
    public function indexAction()
    {
        $users=$this->userTable->fetchAll();
       
        return new ViewModel();
    }
    
    public function WorldAction()
    {
        return new ViewModel();
    }
    
}
