<?php

namespace Application\Controller;

use Application\Model\FuelTable;
use Application\Model\Pagination;
use Application\Model\User;
use Application\Model\UserTable;
use Application\Model\Vehicle;
use Application\Model\VehicleTable;
use Laminas\View\Model\ViewModel;

class VehicleController extends \Laminas\Mvc\Controller\AbstractActionController
{

    const ITEMS_PER_PAGE = 10;
    private VehicleTable $vehicleTable;
    private FuelTable $fuelTable;

    public function __construct(VehicleTable $vehicleTable, FuelTable $fuelTable)
    {
        $this->vehicleTable = $vehicleTable;
        $this->fuelTable = $fuelTable;
    }

    public function ListAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $viewData = [];
        $vehicleResult = $this->vehicleTable->fetchAllPaginated($page, self::ITEMS_PER_PAGE);
        $models = [];
        foreach ($vehicleResult as $u) {
            $models[] = $u;
        }
        $totalNumberOfRows = $this->vehicleTable->getLastFoundRows();

        $pagination = new Pagination($page, ceil($totalNumberOfRows / self::ITEMS_PER_PAGE));

        $viewData['models'] = $models;
        $viewData['pagination'] = $pagination;
        return new ViewModel($viewData);
    }

    public function EditAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'list']);

        if ($id) {
            try {
                $vehicle = $this->vehicleTable->fetchByIdWithFuel($id);
            } catch (\Error $e) {}
        }
        if (empty($vehicle)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Редакция на автомобил';
            $viewData['model'] = $vehicle;
            $viewData['fuels'] = $this->getFuels();
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/vehicle/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        $fuel = $postedData['fuel']??[];
        unset($postedData['fuel']);
        $newVehicle = new Vehicle($postedData);
        $newVehicle->exchangeArray($postedData);
        $newVehicle->setFuels($fuel);

        try {
            $this->vehicleTable->update($newVehicle);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'vehicle', 'action'=>'edit'], ['query' => ['id'=>$id]]);
        }

        $this->flashMessenger()->addErrorMessage("Успешно редактиран ажтомобил '{$vehicle->reg_nomer}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function AddAction()
    {
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'list']);

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Добавяне на автомобил';
            $viewData['model'] = null;
            $viewData['fuels'] = $this->getFuels();
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/vehicle/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        $fuel = $postedData['fuel']??[];
        unset($postedData['fuel']);
        $newVehicle = new Vehicle($postedData);
        $newVehicle->exchangeArray($postedData);
        $newVehicle->setFuels($fuel);

        try {
            $this->vehicleTable->insert($newVehicle);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'vehicle', 'action'=>'add']);
        }

        $this->flashMessenger()->addErrorMessage("Успешно добавен автомобил '{$newVehicle->reg_nomer}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function DeleteAction()
    {
        $id = $this->params()->fromQuery('id', 1);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'list']);

        try {
            $model = $this->vehicleTable->fetchById($id);
        } catch (\Error $e) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        try {
            $this->vehicleTable->delete($model);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със изтриването на записа.');
            return $this->redirect()->toUrl($backLink);
        }

        $this->flashMessenger()->addErrorMessage("Успешно изтрит автомобил '{$model->reg_nomer}'!");
        return $this->redirect()->toUrl($backLink);
    }

    /**
     * @return array
     */
    private function getFuels(): array
    {
        $fuelResultSet = $this->fuelTable->fetchAll();
        $fuels = [];
        foreach ($fuelResultSet as $item) {
            $fuels[$item->id] = $item;
        }
        return $fuels;
    }
}
