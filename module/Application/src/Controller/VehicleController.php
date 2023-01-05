<?php

namespace Application\Controller;

use Application\Model\FuelTable;
use Application\Model\Pagination;
use Application\Model\Refueling;
use Application\Model\RefuelingTable;
use Application\Model\Vehicle;
use Application\Model\VehicleTable;
use Laminas\View\Model\ViewModel;

class VehicleController extends \Laminas\Mvc\Controller\AbstractActionController
{

    const ITEMS_PER_PAGE = 10;
    private VehicleTable $vehicleTable;
    private FuelTable $fuelTable;
    private RefuelingTable $refuelingTable;

    public function __construct(VehicleTable $vehicleTable, FuelTable $fuelTable, RefuelingTable $refuelingTable)
    {
        $this->vehicleTable = $vehicleTable;
        $this->fuelTable = $fuelTable;
        $this->refuelingTable = $refuelingTable;
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

        $this->flashMessenger()->addErrorMessage("Успешно редактиран автомобил '{$vehicle->reg_nomer}'!");
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
     * @param Vehicle|null $vehicle
     * @return array
     */
    private function getFuels(Vehicle $vehicle = null): array
    {
        if (empty($vehicle)) {
            $fuelResultSet = $this->fuelTable->fetchAll();
        } else {
            $fuelResultSet = $this->fuelTable->fetchAll(function ($select) use ($vehicle) {
                $fuels = $vehicle->getFuels();
                $fuelIds = implode(',', $fuels);
                $select->where("id IN ({$fuelIds})");
            });
        }
        $fuels = [];
        foreach ($fuelResultSet as $item) {
            $fuels[$item->id] = $item;
        }
        return $fuels;
    }

    public function FuelingAction()
    {
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'list']);
        $page = $this->params()->fromQuery('page', 1);
        $vehicleId = $this->params()->fromQuery('vehicleId', null);
        if (empty($vehicleId)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е намерена!');
            return $this->redirect()->toUrl($backLink);
        }
        $viewData = [];
        $vehicle = $this->vehicleTable->fetchById($vehicleId);
        if (empty($vehicleId)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е намерена!');
            return $this->redirect()->toUrl($backLink);
        }
        $vehicleResult = $this->refuelingTable->fetchAllPaginated($page, self::ITEMS_PER_PAGE,
        function ($select) use ($vehicleId) {
            $select->where("vehicle_id={$vehicleId}");
        });
        $models = [];
        foreach ($vehicleResult as $u) {
            $models[] = $u;
        }
        $totalNumberOfRows = $this->vehicleTable->getLastFoundRows();

        $pagination = new Pagination($page, ceil($totalNumberOfRows / self::ITEMS_PER_PAGE));

        $viewData['action'] = 'fueling';
        $viewData['models'] = $models;
        $viewData['title'] = 'Зареждане с гориво';
        $viewData['vehicle'] = $vehicle;
        $viewData['backlink'] = $backLink;
        $this->layout()->setVariable('backlink', $backLink);
        $viewData['pagination'] = $pagination;
        return new ViewModel($viewData);
    }

    public function AddFuelingAction()
    {
        $vehicleId = $this->params()->fromQuery('vehicleId', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'fueling'], ['query'=>['vehicleId'=>$vehicleId]]);
        if (empty($vehicleId)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е намерена!');
            return $this->redirect()->toUrl($backLink);
        }
        /** @var Vehicle $vehicle */
        $vehicle = $this->vehicleTable->fetchByIdWithFuel($vehicleId);
        if (empty($vehicleId)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е намерена!');
            return $this->redirect()->toUrl($backLink);
        }

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Добавяне на зареждане с гориво';
            $viewData['vehicle'] = $vehicle;
            $viewData['model'] = null;
            $viewData['fuels'] = $this->getFuels($vehicle);
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/vehicle/add-edit_fueling.phtml');
            return $view;
        }


        $postedData = $this->params()->fromPost();

        $newRefueling = new Refueling($postedData);
        $newRefueling->exchangeArray($postedData);

        $vehicle->odometer = $newRefueling->odometer;
        $vehicle['odometer'] = $newRefueling->odometer;

        $this->vehicleTable->update($vehicle);

         try {
             $this->refuelingTable->insert($newRefueling);
         } catch (\Exception $e) {
             $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
             return $this->redirect()->toRoute('application', ['controller'=>'vehicle', 'action'=>'addFueling'], ['query'=>['vehicleId'=>$vehicleId]]);
         }

        $this->flashMessenger()->addErrorMessage("Успешно добавено зареждане '{$newRefueling->quantity}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function EditFuelingAction()
    {
        $vehicleId = $this->params()->fromQuery('vehicleId', null);
        $id = $this->params()->fromQuery('id', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'fueling'], ['query'=>['vehicleId'=>$vehicleId]]);
        if (empty($vehicleId) || empty($id)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е намерена!');
            return $this->redirect()->toUrl($backLink);
        }
        /** @var Vehicle $vehicle */
        $vehicle = $this->vehicleTable->fetchByIdWithFuel($vehicleId);
        if (empty($vehicleId)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е намерена!');
            return $this->redirect()->toUrl($backLink);
        }
        $fueling = $this->refuelingTable->fetchById($id);
        if (empty($fueling)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е намерена!');
            return $this->redirect()->toUrl($backLink);
        }

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Добавяне на зареждане с гориво';
            $viewData['vehicle'] = $vehicle;
            $viewData['model'] = $fueling;
            $viewData['fuels'] = $this->getFuels($vehicle);
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/vehicle/add-edit_fueling.phtml');
            return $view;
        }


        $postedData = $this->params()->fromPost();

        $newRefueling = new Refueling($postedData);
        $newRefueling->exchangeArray($postedData);

        $vehicle->odometer = $newRefueling->odometer;
        $vehicle['odometer'] = $newRefueling->odometer;

        $this->vehicleTable->update($vehicle);

        try {
            $this->refuelingTable->update($newRefueling);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'vehicle', 'action'=>'addFueling'], ['query'=>['vehicleId'=>$vehicleId]]);
        }

        $this->flashMessenger()->addErrorMessage("Успешно редактирано зареждане '{$newRefueling->quantity}'!");
        return $this->redirect()->toUrl($backLink);
    }


    public function DeleteFuelingAction()
    {
        $vehicleId = $this->params()->fromQuery('vehicleId', null);
        $id = $this->params()->fromQuery('id', 1);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'fueling'], ['query'=>['vehicleId'=>$vehicleId]]);

        try {
            $model = $this->refuelingTable->fetchById($id);
        } catch (\Error $e) {}

        if (empty($model)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        try {
            $this->refuelingTable->delete($model);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със изтриването на записа.');
            return $this->redirect()->toUrl($backLink);
        }

        $this->flashMessenger()->addErrorMessage("Успешно изтритo зареждане '{$model->quantity}'!");
        return $this->redirect()->toUrl($backLink);
    }
}
