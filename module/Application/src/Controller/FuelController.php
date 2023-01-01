<?php

namespace Application\Controller;

use Application\Model\Pagination;
use Application\Model\Fuel;
use Application\Model\FuelTable;
use Laminas\View\Model\ViewModel;

class FuelController extends \Laminas\Mvc\Controller\AbstractActionController
{

    const ITEMS_PER_PAGE = 10;
    private FuelTable $fuelTable;

    public function __construct(FuelTable $fuelTable)
    {
        $this->fuelTable = $fuelTable;
    }

    public function ListAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $viewData = [];
        $fuelResult = $this->fuelTable->fetchAllPaginated($page, self::ITEMS_PER_PAGE);
        $models = [];
        foreach ($fuelResult as $u) {
            $models[] = $u;
        }
        $totalNumberOfRows = $this->fuelTable->getLastFoundRows();

        $pagination = new Pagination($page, ceil($totalNumberOfRows / self::ITEMS_PER_PAGE));

        $viewData['models'] = $models;
        $viewData['pagination'] = $pagination;
        return new ViewModel($viewData);
    }

    public function EditAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'fuel', 'action'=>'list']);

        if ($id) {
            try {
                $fuel = $this->fuelTable->fetchById($id);
            } catch (\Error $e) {}
        }
        if (empty($fuel)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Редакция на вид гориво';
            $viewData['model'] = $fuel;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/fuel/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        $newFuel = new Fuel($postedData);
        $newFuel->exchangeArray($postedData);


        try {
            $this->fuelTable->update($newFuel);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'fuel', 'action'=>'edit'], ['query' => ['id'=>$id]]);
        }

        $this->flashMessenger()->addErrorMessage("Успешно редактиран вид гориво '{$fuel->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function AddAction()
    {
        $backLink = $this->url()->fromRoute('application', ['controller'=>'fuel', 'action'=>'list']);

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Добавяне на видове гориво';
            $viewData['model'] = null;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/fuel/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        unset($postedData['fuel']);
        $newFuel = new Fuel($postedData);
        $newFuel->exchangeArray($postedData);

        try {
            $this->fuelTable->insert($newFuel);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'fuel', 'action'=>'add']);
        }

        $this->flashMessenger()->addErrorMessage("Успешно добавен вид гориво '{$newFuel->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function DeleteAction()
    {
        $id = $this->params()->fromQuery('id', 1);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'fuel', 'action'=>'list']);

        try {
            $model = $this->fuelTable->fetchById($id);
        } catch (\Error $e) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        try {
            $this->fuelTable->delete($model);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със изтриването на записа.');
            return $this->redirect()->toUrl($backLink);
        }

        $this->flashMessenger()->addErrorMessage("Успешно изтрит вид гориво '{$model->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

}
