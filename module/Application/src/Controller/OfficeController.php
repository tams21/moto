<?php

namespace Application\Controller;

use Application\Model\Pagination;
use Application\Model\Office;
use Application\Model\OfficeTable;
use Laminas\View\Model\ViewModel;

class OfficeController extends \Laminas\Mvc\Controller\AbstractActionController
{

    const ITEMS_PER_PAGE = 10;
    private OfficeTable $officeTable;

    public function __construct(OfficeTable $officeTable)
    {
        $this->officeTable = $officeTable;
    }

    public function ListAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $viewData = [];
        $officeResult = $this->officeTable->fetchAllPaginated($page, self::ITEMS_PER_PAGE);
        $models = [];
        foreach ($officeResult as $u) {
            $models[] = $u;
        }
        $totalNumberOfRows = $this->officeTable->getLastFoundRows();

        $pagination = new Pagination($page, ceil($totalNumberOfRows / self::ITEMS_PER_PAGE));

        $viewData['models'] = $models;
        $viewData['pagination'] = $pagination;
        return new ViewModel($viewData);
    }

    public function EditAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'office', 'action'=>'list']);

        if ($id) {
            try {
                $office = $this->officeTable->fetchById($id);
            } catch (\Error $e) {}
        }
        if (empty($office)) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Редакция на офис';
            $viewData['model'] = $office;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/office/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        $newOffice = new Office($postedData);
        $newOffice->exchangeArray($postedData);


        try {
            $this->officeTable->update($newOffice);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'office', 'action'=>'edit'], ['query' => ['id'=>$id]]);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно редактиран офис '{$office->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function AddAction()
    {
        $backLink = $this->url()->fromRoute('application', ['controller'=>'office', 'action'=>'list']);

        if (!$this->getRequest()->isPost()) {
            $viewData = [];
            $viewData['title'] = 'Добавяне на офис';
            $viewData['model'] = null;
            $viewData['backlink'] = $backLink;
            $this->layout()->setVariable('backlink', $backLink);
            $view = new ViewModel($viewData);
            $view->setTemplate('application/office/add-edit.phtml');
            return $view;
        }

        $postedData = $this->params()->fromPost();
        unset($postedData['fuel']);
        $newOffice = new Office($postedData);
        $newOffice->exchangeArray($postedData);

        try {
            $this->officeTable->insert($newOffice);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със записа. Моля провере данните и опитайте отново');
            return $this->redirect()->toRoute('application', ['controller'=>'office', 'action'=>'add']);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно добавен офис '{$newOffice->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

    public function DeleteAction()
    {
        $id = $this->params()->fromQuery('id', 1);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'office', 'action'=>'list']);

        try {
            $model = $this->officeTable->fetchById($id);
        } catch (\Error $e) {
            $this->flashMessenger()->addErrorMessage('Страницата не е открита!');
            return $this->redirect()->toUrl($backLink);
        }

        try {
            $this->officeTable->delete($model);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Възникна проблем със изтриването на записа.');
            return $this->redirect()->toUrl($backLink);
        }

        $this->flashMessenger()->addSuccessMessage("Успешно изтрит офис '{$model->name}'!");
        return $this->redirect()->toUrl($backLink);
    }

}
