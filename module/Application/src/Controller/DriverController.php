<?php

namespace Application\Controller;

use Application\Model\Assignment;
use Application\Model\AssignmentTable;
use Application\Model\Driver;
use Application\Model\OfficeTable;
use Application\Model\Pagination;
use Application\Model\Office;
use Application\Model\DriverTable;
use Application\Model\Vehicle;
use Application\Model\VehicleTable;
use Laminas\Db\Sql\Join;
use Laminas\Db\Sql\Select;
use Laminas\Validator\Date;
use Laminas\View\Model\ViewModel;

class DriverController extends \Laminas\Mvc\Controller\AbstractActionController
{

    const ITEMS_PER_PAGE = 10;
    private DriverTable $driverTable;
    private OfficeTable $officeTable;
    private VehicleTable $vehicleTable;
    private AssignmentTable $assignmentTable;

    public function __construct(DriverTable $driverTable,
                                OfficeTable $officeTable,
                                VehicleTable $vehicleTable,
                                AssignmentTable $assignmentTable)
    {
        $this->driverTable = $driverTable;
        $this->officeTable = $officeTable;
        $this->vehicleTable = $vehicleTable;
        $this->assignmentTable = $assignmentTable;
    }

    public function ListAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $viewData = [];
        $officeResult = $this->driverTable->fetchAllPaginated(
            $page,
            self::ITEMS_PER_PAGE,
            function(Select $select){
                $officeTableSrc = $this->officeTable->getTableGateway()->getTable();
                $driverTableSrc = $this->driverTable->getTableGateway()->getTable();
                $vehicleTableSrc = $this->vehicleTable->getTableGateway()->getTable();
                $select->join(['o' => $officeTableSrc],
                    "o.id = {$driverTableSrc}.office_id",
                    ['office_name'=>'name'],
                    Join::JOIN_LEFT);
                $select->join(['v'=>$vehicleTableSrc],
                    "v.id={$driverTableSrc}.vehicle_id",
                    [
                        'vehicle_reg_nomer'=>'reg_nomer',
                        'vehicle_model'=>'model',
                        'vehicle_odometer'=>'odometer',
                        'vehicle_color'=>'color',
                        'vehicle_year_manufactured'=>'year_manufactured',
                        'vehicle_notes'=>'notes',
                    ],
                    Join::JOIN_LEFT);
            });
        $models = [];
        foreach ($officeResult as $u) {
            $models[] = $u;
        }

        $totalNumberOfRows = $this->driverTable->getLastFoundRows();

        $pagination = new Pagination($page, ceil($totalNumberOfRows / self::ITEMS_PER_PAGE));

        $viewData['models'] = $models;
        $viewData['pagination'] = $pagination;
        return new ViewModel($viewData);
    }

    public function EditAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'driver', 'action'=>'list']);

        if ($id) {
            try {
                $driver = $this->driverTable->fetchById($id);
            } catch (\Error $e) {}
        }
        if (empty($driver)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Редакция на шофьор';
            $viewData['model'] = $driver;
            $viewData['offices'] = $this->getOffices();
            $viewData['vehicles'] =  $this->getVehicles();
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/driver/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        $newDriver = new Driver($postedData);
        $newDriver->exchangeArray($postedData);

        if ($driver->vehicle_id !== $newDriver->vehicle_id) {
            if ($driver->vehicle_id) {
                $assignmentSet = $this->assignmentTable->fetchAll(function (Select $select) use ($driver) {
                    $select->where("vehicle_id = '{$driver->vehicle_id}' AND driver_id = '{$driver->id}'");
                    $select->order('start_day DESC');
                    $select->limit(1);
                });
                $assignment = $assignmentSet->current();
                if ($assignment) {
                    $assignment->end_day = (new \DateTime())->format('Y-m-d');
                    $this->assignmentTable->update($assignment);
                }
            }

            if ($newDriver->vehicle_id) {
                $newAssignment = new Assignment();
                $newAssignment->exchangeArray([
                    'vehicle_id' => $newDriver->vehicle_id,
                    'driver_id' => $newDriver->id,
                    'start_day' => (new \DateTime())->format('Y-m-d'),
                    'end_day' => null,
                ]);
                $this->assignmentTable->insert($newAssignment);
            }
        }

        try {
            $this->driverTable->update($newDriver);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'driver', 'action'=>'edit'], ['query' => ['id'=>$id]]);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно редактиран шофьор '{$driver->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function AddAction()
    {
        $backLink = $this->url()->fromRoute('application', ['controller'=>'driver', 'action'=>'list']);

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Добавяне на шофьор';
            $viewData['model'] = null;
            $viewData['offices'] = $this->getOffices();
            $viewData['vehicles'] =  $this->getVehicles();
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/driver/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        $newDriver = new Driver($postedData);
        $newDriver->exchangeArray($postedData);

        if ($newDriver->vehicle_id) {
            $newAssignment = new Assignment();
            $newAssignment->exchangeArray([
                'vehicle_id' => $newDriver->vehicle_id,
                'driver_id' => $newDriver->id,
                'start_day' => (new \DateTime())->format('Y-m-d'),
                'end_day' => null,
            ]);
            $this->assignmentTable->insert($newAssignment);
        }

        try {
            $this->driverTable->insert($newDriver);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'driver', 'action'=>'add']);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно добавен шофьор '{$newDriver->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function DeleteAction()
    {
        $id = $this->params()->fromQuery('id', 1);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'driver', 'action'=>'list']);

        try {
            $model = $this->driverTable->fetchById($id);
        } catch (\Error $e) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        try {
            $this->driverTable->delete($model);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със изтриването на записа.');
            return $this->redirect()->toUrl($backLink);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно изтрит шофьор '{$model->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

    /**
     * @return Office[]
     */
    private function getOffices(): array
    {
        $officesSet = $this->officeTable->fetchAll();
        $offices = [];
        foreach ($officesSet as $v) {
            $offices[] = $v;
        }
        return $offices;
    }

    /**
     * @return Vehicle[]
     */
    private function getVehicles(): array
    {
        $vehiclesSet = $this->vehicleTable->fetchAll();
        $vehicles = [];
        foreach ($vehiclesSet as $v) {
            $vehicles[] = $v;
        }
        return $vehicles;
    }
}
