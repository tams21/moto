<?php

namespace Application\Controller;

use Application\Model\DriverTable;
use Application\Model\Pagination;
use Application\Model\Office;
use Application\Model\OfficeTable;
use Application\Model\Transire;
use Application\Model\TransireTable;
use Application\Model\VehicleTable;
use Laminas\Db\Sql\Select;
use Laminas\View\Model\ViewModel;

class TransireController extends \Laminas\Mvc\Controller\AbstractActionController
{
    const ITEMS_PER_PAGE = 10;
    private TransireTable $transireTable;
    private DriverTable $driverTable;
    private VehicleTable $vehicleTable;

    public function __construct(TransireTable $transireTable, DriverTable $driverTable, VehicleTable $vehicleTable)
    {
        $this->transireTable = $transireTable;
        $this->driverTable = $driverTable;
        $this->vehicleTable = $vehicleTable;
    }

    public function ListAction()
    {
        $driverId = $this->identity()->role === 'driver' ? $this->identity()->driver_id : null;
        $page = $this->params()->fromQuery('page', 1);
        $viewData = [];
        $result = $this->transireTable->fetchAllPaginatedWithDetails($page, self::ITEMS_PER_PAGE, function(Select $select) use ($driverId) {
            $select->where("transire.driver_id='{$driverId}'");
        });
        $models = [];

        foreach ($result as $u) {
            $models[] = $u;
        }

        $totalNumberOfRows = $this->transireTable->getLastFoundRows();

        $pagination = new Pagination($page, ceil($totalNumberOfRows / self::ITEMS_PER_PAGE));

        $viewData['models'] = $models;
        $viewData['pagination'] = $pagination;
        return new ViewModel($viewData);
    }

    public function EditAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'transire', 'action'=>'list']);
        $driverId = $this->identity()->role === 'driver' ? $this->identity()->driver_id : null;
        if ($id) {
            try {
                $record = $this->transireTable->fetchById($id);
            } catch (\Error $e) {}
        }
        if (empty($record)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            if ($driverId) {
                $viewData['driverFixed'] = true;
            }
            $viewData['driverList'] = $this->driverTable->fetchAll();
            $viewData['vehicleList'] = $this->vehicleTable->fetchAll();
            $viewData['title'] = 'Редакция на пътен лист';
            $viewData['model'] = $record;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/transire/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        $newRecord = new Transire($postedData);
        $newRecord->exchangeArray($postedData);


        try {
            $this->transireTable->update($newRecord);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'transire', 'action'=>'edit'], ['query' => ['id'=>$id]]);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно редактиран пътен лист!");
        return $this->redirect()->toUrl($backLink);
    }

    public function AddAction()
    {
        $backLink = $this->url()->fromRoute('application', ['controller'=>'transire', 'action'=>'list']);
        $driverId = $this->identity()->role === 'driver' ? $this->identity()->driver_id : null;
        $vehicleId = $this->params()->fromQuery('vehicleId', null);

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Добавяне на пътен лист';
            $viewData['driverId'] = $driverId;
            $viewData['vehicleId'] = $vehicleId;
            $viewData['driverList'] = $this->driverTable->fetchAll();
            $viewData['vehicleList'] = $this->vehicleTable->fetchAll();
            if ($vehicleId) {
                $viewData['vehicleFixed'] = true;
            }

            if ($driverId) {
                $viewData['driverFixed'] = true;
            }

            $viewData['date'] = (new \DateTime())->format('Y-m-d H:i:s');
            //$viewData['start_odometer'] = str_pad($vehicle->odometer,6, '0', STR_PAD_LEFT);
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/transire/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        unset($postedData['fuel']);
        $newRecord = new Transire($postedData);
        $newRecord->exchangeArray($postedData);

        try {
            $this->transireTable->insert($newRecord);
        } catch (\Exception $e) {
            var_dump($e);
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'transire', 'action'=>'add']);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно добавен пътен лист!");
        return $this->redirect()->toUrl($backLink);
    }

    public function DeleteAction()
    {
        $id = $this->params()->fromQuery('id', 1);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'transire', 'action'=>'list']);

        try {
            $model = $this->transireTable->fetchById($id);
        } catch (\Error $e) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        try {
            $this->transireTable->delete($model);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със изтриването на записа.');
            return $this->redirect()->toUrl($backLink);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно изтрит офис '{$model->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

}
