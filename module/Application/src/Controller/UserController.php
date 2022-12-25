<?php

namespace Application\Controller;

use Application\Model\Pagination;
use Application\Model\User;
use Application\Model\UserTable;
use Laminas\View\Model\ViewModel;

class UserController extends \Laminas\Mvc\Controller\AbstractActionController
{

    const ITEMS_PER_PAGE = 10;
    private UserTable $userTable;

    public function __construct(UserTable $userTable)
    {
        $this->userTable = $userTable;
    }

    public function ListAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $viewData = [];
        $usersResult = $this->userTable->fetchAllPaginated($page, self::ITEMS_PER_PAGE);
        $users = [];
        foreach ($usersResult as $u) {
            $users[] = $u;
        }
        $totalNumberOfRows = $this->userTable->getLastFoundRows();

        $pagination = new Pagination($page, ceil($totalNumberOfRows / self::ITEMS_PER_PAGE));

        $viewData['users'] = $users;
        $viewData['pagination'] = $pagination;
        return new ViewModel($viewData);
    }

    public function EditAction()
    {
        $id = $this->params()->fromQuery('id', 1);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'user', 'action'=>'list']);

        try {
            $user = $this->userTable->fetchById($id);
        } catch (\Error $e) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Редакция на потребител';
            $viewData['model'] = $user;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/user/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        $newUser = new User($postedData);
        $newUser->exchangeArray($postedData);

        try {
            $this->userTable->update($newUser);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'user', 'action'=>'edit'], ['query' => ['id'=>$id]]);
        }

        $this->flashMessenger()->addErrorMessage("Успешно редактиран потребител @{$user->username}!");
        return $this->redirect()->toUrl($backLink);
    }

    public function AddAction()
    {
        $backLink = $this->url()->fromRoute('application', ['controller'=>'user', 'action'=>'list']);

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Добавяне на нов потребител';
            $viewData['model'] = null;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/user/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        $newUser = new User($postedData);
        $newUser->exchangeArray($postedData);

        try {
            $this->userTable->insert($newUser);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'user', 'action'=>'edit'], ['query' => ['id'=>$id]]);
        }

        $this->flashMessenger()->addErrorMessage("Успешно добавен потребител @{$newUser->username}!");
        return $this->redirect()->toUrl($backLink);
    }

    public function DeleteAction()
    {
        $id = $this->params()->fromQuery('id', 1);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'user', 'action'=>'list']);

        try {
            $user = $this->userTable->fetchById($id);
        } catch (\Error $e) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        try {
            $this->userTable->delete($user);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със изтриването на записа.');
            return $this->redirect()->toUrl($backLink);
        }

        $this->flashMessenger()->addErrorMessage("Успешно изтрит потребител @{$user->username}!");
        return $this->redirect()->toUrl($backLink);
    }
}
