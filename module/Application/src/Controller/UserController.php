<?php

namespace Application\Controller;

use Application\Model\Driver;
use Application\Model\DriverTable;
use Application\Model\Pagination;
use Application\Model\User;
use Application\Model\UserTable;
use Laminas\Db\Sql\Join;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class UserController extends AbstractActionController
{

    const ITEMS_PER_PAGE = 10;
    private UserTable $userTable;
    private DriverTable $driverTable;

    public function __construct(UserTable $userTable, DriverTable $driverTable)
    {
        $this->userTable = $userTable;
        $this->driverTable = $driverTable;
    }

    public function ListAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $viewData = [];
        $usersResult = $this->userTable->fetchAllPaginated(
            $page,
            self::ITEMS_PER_PAGE,
            function($select) {
                $select->join(
                    ['d'=>'drivers'],
                    "d.id={$this->userTable->getTableGateway()->getTable()}.driver_id",
                    ['driver_name'=>'name','driver_office_id'=>'office_id','drivert_vehicle_id'=>'vehicle_id'], Join::JOIN_LEFT);
            });
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
            $drivers = $this->getDrivers();
            if ($user->driver_id) {
                $currentDriver = $this->driverTable->fetchById($user->driver_id);
                if ($currentDriver) {
                    $drivers[] = $currentDriver;
                }
            }
            $viewData['drivers'] = $drivers;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/user/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        if (empty($postedData['driver_id'])) {
            $postedData['driver_id'] = null;
        }
        $newUser = new User($postedData);
        $newUser->exchangeArray($postedData);

        try {
            $this->userTable->update($newUser);
        } catch (\Exception $e) { var_dump($e);
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'user', 'action'=>'edit'], ['query' => ['id'=>$id]]);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно редактиран потребител @{$user->username}!");
        return $this->redirect()->toUrl($backLink);
    }

    public function AddAction()
    {
        $backLink = $this->url()->fromRoute('application', ['controller'=>'user', 'action'=>'list']);

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Добавяне на нов потребител';
            $viewData['model'] = null;
            $viewData['drivers'] = $this->getDrivers();
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/user/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();

        if ($postedData['role'] === 'driver') {
            if ($postedData['driver_id'] === '') {
                $driver = new Driver(['name' => $postedData['name'], 'office_id' => null, 'vehicle_id' => null]);
                $postedData['driver_id'] = $this->driverTable->insert($driver);
            } else {
                $postedData['driver_id'] = null;
            }
        }

        $newUser = new User($postedData);
        $newUser->exchangeArray($postedData);

        try {
            $this->userTable->insert($newUser);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'user', 'action'=>'edit']);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно добавен потребител @{$newUser->username}!");
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

        $this->flashMessenger()->addSuccessMessage("Успешно изтрит потребител @{$user->username}!");
        return $this->redirect()->toUrl($backLink);
    }

    public function SetCustomPassAction()
    {
        $id = $this->params()->fromQuery('id');
        $backLink = $this->url()->fromRoute('application', ['controller'=>'user', 'action'=>'list']);

        $viewData = [
            'title' => 'Задаване на парола',
            'backlink' => $backLink,
        ];

        try {
            /** @var User $user */
            $user = $this->userTable->fetchById($id);
            $viewData['model'] = $user;
        } catch(\Exception $e) {
            $this->flashMessenger()->addErrorMessage("Не е открит потребител с идентификатор @{$user->username}");
            return $this->redirect()->toRoute('application', ['controller'=>'user', 'action'=>'list']);
        }

        $postedData = $this->params()->fromPost();
        if (empty($postedData)) {
            // Not posted
            return new ViewModel($viewData);
        }

        if ($postedData['password'] !== $postedData['password2']) {
            $this->flashMessenger()->addErrorMessage("Паролата и потвърждението не съвпадат! Моля изпишете два пъти новата парола по идентичен начин!");
            return $this->redirect()->toRoute('application', ['controller'=>'user', 'action'=>'setCustomPass'],['query'=>['id'=>$id]]);
        }

        $user->changePassword($postedData['password']);

        try {;
            $this->userTable->update($user);
            $this->flashMessenger()->addSuccessMessage("Успешно зададена парола за потребител {$user->name}");
            return $this->redirect()->toUrl($backLink);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage("Възникна проблем при запис на паролата.");
            return $this->redirect()->toRoute('application', ['controller'=>'user', 'action'=>'setCustomPass'],['query'=>['id'=>$id]]);
        }
    }

    /**
     * @return array
     */
    private function getDrivers(): array
    {
        $driversSet = $this->driverTable->fetchAllNotAddedToUser();
        $drivers = [];
        foreach ($driversSet as $v) {
            $drivers[] = $v;
        }
        return $drivers;
    }
}
