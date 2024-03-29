<?php

namespace Application\Controller;

use Application\Model\FuelTable;
use Application\Model\MaintenanceSchedule;
use Application\Model\MaintenanceScheduleTable;
use Application\Model\MaintenanceTable;
use Application\Model\Pagination;
use Application\Model\Repair;
use Application\Model\RepairTable;
use Application\Model\Refueling;
use Application\Model\RefuelingTable;
use Application\Model\Vehicle;
use Application\Model\VehicleTable;
use Laminas\Db\Sql\Join;
use Laminas\View\Model\ViewModel;

class VehicleController extends \Laminas\Mvc\Controller\AbstractActionController
{

    const ITEMS_PER_PAGE = 10;
    private VehicleTable $vehicleTable;
    private FuelTable $fuelTable;
    private RefuelingTable $refuelingTable;
    private MaintenanceScheduleTable $maintenanceScheduleTable;
    private MaintenanceTable $maintenanceTable;
    private RepairTable $repairTable;

    public function __construct(
        VehicleTable             $vehicleTable,
        FuelTable                $fuelTable,
        RefuelingTable           $refuelingTable,
        MaintenanceTable         $maintenanceTable,
        MaintenanceScheduleTable $maintenanceScheduleTable,
        RepairTable              $repairTable)
    {
        $this->vehicleTable = $vehicleTable;
        $this->fuelTable = $fuelTable;
        $this->refuelingTable = $refuelingTable;
        $this->maintenanceTable = $maintenanceTable;
        $this->maintenanceScheduleTable = $maintenanceScheduleTable;
        $this->repairTable = $repairTable;
    }

    public function ListAction()
    {
        $searchTerm = $this->params()->fromQuery('term', null);
        $page = $this->params()->fromQuery('page', 1);
        $viewData = [];
        $vehicleResult = $this->vehicleTable->fetchAllPaginated(
            $page,
            self::ITEMS_PER_PAGE,
            empty($searchTerm) ? null : function ($select) use ($searchTerm) {
                $select->where("reg_nomer LIKE '%{$searchTerm}%' OR model LIKE '%{$searchTerm}%' OR color LIKE '%{$searchTerm}%' OR notes LIKE '%{$searchTerm}%' OR year_manufactured LIKE '%{$searchTerm}%'");
            }
        );
        $models = [];
        foreach ($vehicleResult as $u) {
            $models[] = $u;
        }
        $totalNumberOfRows = $this->vehicleTable->getLastFoundRows();

        $pagination = new Pagination($page, ceil($totalNumberOfRows / self::ITEMS_PER_PAGE));

        $viewData['models'] = $models;
        $viewData['term'] = $searchTerm;
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

        $this->flashMessenger()->addSuccessMessage("Успешно редактиран автомобил '{$vehicle->reg_nomer}'!");
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

        $this->flashMessenger()->addSuccessMessage("Успешно добавен автомобил '{$newVehicle->reg_nomer}'!");
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

        $this->flashMessenger()->addSuccessMessage("Успешно изтрит автомобил '{$model->reg_nomer}'!");
        return $this->redirect()->toUrl($backLink);
    }

    /**
     * @param Vehicle|null $vehicle
     * @return array
     */
    private function getFuels(Vehicle $vehicle = null): array
    {
        if (empty($vehicle) || empty($vehicle->getFuels())) {
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

    /**
     * @param Vehicle|null $vehicle
     * @return array
     */
    private function getMaintenance(): array
    {
        $maintenanceResultSet = $this->maintenanceTable->fetchAll();

        $maintenances = [];
        foreach ($maintenanceResultSet as $item) {
            $maintenances[$item->id] = $item;
        }
        return $maintenances;
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

        if (empty($vehicle->getFuels())) {
            $this->flashMessenger()->addErrorMessage('Автомобилат няма избран вид гориво!');
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

        $this->flashMessenger()->addSuccessMessage("Успешно добавено зареждане '{$newRefueling->quantity}'!");
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

        $this->flashMessenger()->addSuccessMessage("Успешно редактирано зареждане '{$newRefueling->quantity}'!");
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

        $this->flashMessenger()->addSuccessMessage("Успешно изтритo зареждане '{$model->quantity}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function MaintenanceScheduleAction()
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
        $schedulesResult = $this->maintenanceScheduleTable->fetchAllPaginated(
            $page,
            self::ITEMS_PER_PAGE,
            function ($select) use ($vehicleId) {
                $select->join(
                    ['d'=>'maintenance'],
                    "d.id={$this->maintenanceScheduleTable->getTableGateway()->getTable()}.maintenance_id",
                    ['maintenance_name'=>'name'], Join::JOIN_LEFT);
                $select->where("vehicle_id={$vehicleId}");
            });

        $models = [];
        foreach ($schedulesResult as $u) {
            $models[] = $u;
        }
        $totalNumberOfRows = $this->maintenanceScheduleTable->getLastFoundRows();

        $pagination = new Pagination($page, ceil($totalNumberOfRows / self::ITEMS_PER_PAGE));

        $viewData['action'] = 'maintenanceSchedule';
        $viewData['models'] = $models;
        $viewData['title'] = 'Графици за поддръжка';
        $viewData['vehicle'] = $vehicle;
        $viewData['backlink'] = $backLink;
        $this->layout()->setVariable('backlink', $backLink);
        $viewData['pagination'] = $pagination;
        return new ViewModel($viewData);
    }

    public function RepairAction()
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
        $repairResult = $this->repairTable->fetchAllPaginated($page, self::ITEMS_PER_PAGE,
            function ($select) use ($vehicleId) {
                $select->where("vehicle_id={$vehicleId}");
            });
        $models = [];
        foreach ($repairResult as $u) {
            $models[] = $u;
        }
        $totalNumberOfRows = $this->repairTable->getLastFoundRows();

        $pagination = new Pagination($page, ceil($totalNumberOfRows / self::ITEMS_PER_PAGE));

        $viewData['action'] = 'repair';
        $viewData['models'] = $models;
        $viewData['title'] = 'Ремонти';
        $viewData['vehicle'] = $vehicle;
        $viewData['backlink'] = $backLink;
        $this->layout()->setVariable('backlink', $backLink);
        $viewData['pagination'] = $pagination;
        return new ViewModel($viewData);
    }

    public function AddRepairAction()
    {
        $vehicleId = $this->params()->fromQuery('vehicleId', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'repair'], ['query'=>['vehicleId'=>$vehicleId]]);
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
            $viewData['title'] = 'Добавяне на ремонт';
            $viewData['vehicle'] = $vehicle;
            $viewData['model'] = null;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/vehicle/add-edit_repair.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();

        $newRepair = new Repair($postedData);
        $newRepair->exchangeArray($postedData);

        try {
            $this->repairTable->insert($newRepair);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'vehicle', 'action'=>'addRepair'], ['query'=>['vehicleId'=>$vehicleId]]);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно добавен ремонт!");
        return $this->redirect()->toUrl($backLink);
    }

    public function EditRepairAction()
    {
        $id = $this->params()->fromQuery('id', 1);
        $vehicleId = $this->params()->fromQuery('vehicleId', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'repair'], ['query'=>['vehicleId'=>$vehicleId]]);
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
        $repair = $this->repairTable->fetchById($id);

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Редакция на график за поддръжка';
            $viewData['vehicle'] = $vehicle;
            $viewData['model'] = $repair;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/vehicle/add-edit_repair.phtml');
            return $view;
        }


        $postedData = $this->params()->fromPost();

        $newRepair = new Repair($postedData);
        $newRepair->exchangeArray($postedData);

        try {
            $this->repairTable->update($newRepair);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'vehicle', 'action'=>'editRepair'], ['query'=>['vehicleId'=>$vehicleId]]);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно редактиран ремонт!");
        return $this->redirect()->toUrl($backLink);
    }

    public function DeleteRepairAction()
    {
        $vehicleId = $this->params()->fromQuery('vehicleId', null);
        $id = $this->params()->fromQuery('id', 1);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'repair'], ['query'=>['vehicleId'=>$vehicleId]]);

        try {
            $model = $this->repairTable->fetchById($id);
        } catch (\Error $e) {}

        if (empty($model)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        try {
            $this->repairTable->delete($model);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със изтриването на записа.');
            return $this->redirect()->toUrl($backLink);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно изтрит ремонт!");
        return $this->redirect()->toUrl($backLink);
    }

    public function AddMaintenanceScheduleAction()
    {
        $vehicleId = $this->params()->fromQuery('vehicleId', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'maintenanceSchedule'], ['query'=>['vehicleId'=>$vehicleId]]);
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

        $maintenances = $this->getMaintenance();
        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Добавяне на график за поддръжка';
            $viewData['vehicle'] = $vehicle;
            $viewData['model'] = null;
            $viewData['maintenances'] = $maintenances;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/vehicle/add-edit_maintenance-schedule.phtml');
            return $view;
        }


        $postedData = $this->params()->fromPost();

        $newMaintenanceSchedule = new MaintenanceSchedule($postedData);
        $newMaintenanceSchedule->exchangeArray($postedData);

        try {
            $this->maintenanceScheduleTable->insert($newMaintenanceSchedule);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'vehicle', 'action'=>'addMaintenanceSchedule'], ['query'=>['vehicleId'=>$vehicleId]]);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно добавено график за поддръжка '{$maintenances[$newMaintenanceSchedule->maintenance_id]->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function EditMaintenanceScheduleAction()
    {
        $id = $this->params()->fromQuery('id', 1);
        $vehicleId = $this->params()->fromQuery('vehicleId', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'maintenanceSchedule'], ['query'=>['vehicleId'=>$vehicleId]]);
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
        $maintenanceSchedule = $this->maintenanceScheduleTable->fetchById($id);

        $maintenances = $this->getMaintenance();

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Редакция на график за поддръжка';
            $viewData['vehicle'] = $vehicle;
            $viewData['model'] = $maintenanceSchedule;
            $viewData['maintenances'] = $maintenances;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/vehicle/add-edit_maintenance-schedule.phtml');
            return $view;
        }


        $postedData = $this->params()->fromPost();

        $newMaintenanceSchedule = new MaintenanceSchedule($postedData);
        $newMaintenanceSchedule->exchangeArray($postedData);

        try {
            $this->maintenanceScheduleTable->update($newMaintenanceSchedule);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'vehicle', 'action'=>'addMaintenanceSchedule'], ['query'=>['vehicleId'=>$vehicleId]]);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно редактиран график за поддръжка '{$maintenances[$newMaintenanceSchedule->maintenance_id]->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function DeleteMaintenanceScheduleAction()
    {
        $vehicleId = $this->params()->fromQuery('vehicleId', null);
        $id = $this->params()->fromQuery('id', 1);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'vehicle', 'action'=>'maintenanceSchedule'], ['query'=>['vehicleId'=>$vehicleId]]);

        try {
            $model = $this->maintenanceScheduleTable->fetchById($id);
        } catch (\Error $e) {}

        if (empty($model)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        try {
            $this->maintenanceScheduleTable->delete($model);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със изтриването на записа.');
            return $this->redirect()->toUrl($backLink);
        }

        $maintenances = $this->getMaintenance();

        $this->flashMessenger()->addSuccessMessage("Успешно изтрит график за поддръжка '{$maintenances[$model->maintenance_id]->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

}
