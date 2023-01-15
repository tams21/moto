<?php

namespace Application\Controller;

use Application\Model\Maintenance;
use Application\Model\MaintenanceTable;
use Application\Model\Pagination;
use Application\Model\Fuel;
use Application\Model\FuelTable;
use Laminas\View\Model\ViewModel;

class MaintenanceController extends \Laminas\Mvc\Controller\AbstractActionController
{

    const ITEMS_PER_PAGE = 10;
    private MaintenanceTable $maintenanceTable;

    public function __construct(MaintenanceTable $maintenanceTable)
    {
        $this->maintenanceTable = $maintenanceTable;
    }

    public function ListAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $viewData = [];
        $fuelResult = $this->maintenanceTable->fetchAllPaginated($page, self::ITEMS_PER_PAGE);
        $models = [];
        foreach ($fuelResult as $u) {
            $models[] = $u;
        }
        $totalNumberOfRows = $this->maintenanceTable->getLastFoundRows();

        $pagination = new Pagination($page, ceil($totalNumberOfRows / self::ITEMS_PER_PAGE));

        $viewData['models'] = $models;
        $viewData['pagination'] = $pagination;
        return new ViewModel($viewData);
    }

    public function EditAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'maintenance', 'action'=>'list']);

        if ($id) {
            try {
                $fuel = $this->maintenanceTable->fetchById($id);
            } catch (\Error $e) {}
        }
        if (empty($fuel)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Редакция на вид поддръжка';
            $viewData['model'] = $fuel;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/maintenance/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        $newMaintenance = new Maintenance($postedData);
        $newMaintenance->exchangeArray($postedData);


        try {
            $this->maintenanceTable->update($newMaintenance);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'maintenance', 'action'=>'edit'], ['query' => ['id'=>$id]]);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно редактиран вид поддръжка '{$fuel->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function AddAction()
    {
        $backLink = $this->url()->fromRoute('application', ['controller'=>'maintenance', 'action'=>'list']);

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Добавяне на видове поддръжка';
            $viewData['model'] = null;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/maintenance/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        unset($postedData['fuel']);
        $newMaintenance = new Maintenance($postedData);
        $newMaintenance->exchangeArray($postedData);

        try {
            $this->maintenanceTable->insert($newMaintenance);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'maintenance', 'action'=>'add']);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно добавен вид поддръжка '{$newMaintenance->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function DeleteAction()
    {
        $id = $this->params()->fromQuery('id', 1);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'maintenance', 'action'=>'list']);

        try {
            $model = $this->maintenanceTable->fetchById($id);
        } catch (\Error $e) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        try {
            $this->maintenanceTable->delete($model);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със изтриването на записа.');
            return $this->redirect()->toUrl($backLink);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно изтрит вид поддръжка '{$model->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

}
