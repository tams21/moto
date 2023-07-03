<?php

namespace Application\Controller;

use Application\Model\FuelReportView;
use Application\Model\TransireReportView;
use Application\Model\VehicleTable;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\Between;
use Laminas\Db\Sql\Predicate\Predicate;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Laminas\Db\Sql\Select;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;

class ReportController extends \Laminas\Mvc\Controller\AbstractActionController
{
    private ServiceManager $serviceManager;

    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    public function getService($name)
    {
        return $this->serviceManager->get($name);
    }

    public function ListAction()
    {
        $viewData = [];
        return new ViewModel($viewData);
    }

    public function FuelAction()
    {
        $from = $this->params()->fromQuery('from', date('Y-m-01', strtotime('-1 month')));
        $to = $this->params()->fromQuery('to', date('Y-m-t', strtotime('-1 month')));
        $download = $this->params()->fromQuery('download', false);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'report', 'action'=>'list']);

        $fuelView = $this->getService(FuelReportView::class);
        $recordSet = $fuelView->fetchAll(function(Select $select) use ($from, $to) {
            $select->columns([
                'model' => 'model',
                'year_manufactured' => 'year_manufactured',
                'reg_nomer' => 'reg_nomer',
                'name' => 'name',
                'quantity' => new Expression('SUM(quantity)'),
                'date_start' => new Expression('min(date_refueling)'),
                'date_end' =>  new Expression('max(date_refueling)'),
                'odo_start' =>  new Expression('min(refuling_odometer)'),
                'odo_end' =>  new Expression('max(refuling_odometer)'),
            ]);
            $select->where("date_refueling BETWEEN '{$from}' AND '{$to}'");
            $select->group(['id', 'fuel_id']);
        });

        $data = [];
        foreach ($recordSet as $v) {
            $distance = $v->odo_end - $v->odo_start;
            $data[] = [
                'model' => $v->model,
                'year_manufactured' => $v->year_manufactured,
                'reg_nomer' => $v->reg_nomer,
                'name' => $v->name,
                'odo_start' => $v->odo_start,
                'odo_end' => $v->odo_end,
                'distance' => $distance,
                'quantity' => $v->quantity,
                'consumption' => $distance ? number_format(100 * $v->quantity / ($distance), '2', '.', ' ') : '-',
            ];
        }

        if (!$download) {
            $this->layout()->setVariable('backlink', $backLink);
            $viewData = [
                'backlink' => $backLink,
                'title' => 'Справка за изразходено гориво',
                'from' => $from,
                'to' => $to,
                'data' => $data,
                'downloadUrl' => $this->url()->fromRoute(
                    'application',
                    ['controller'=>'report', 'action'=>'fuel'],
                    ['query' => ['from' => $from, 'to' => $to, 'download'=>'csv']]
                ),
            ];
            return new ViewModel($viewData);
        }

        // Generate data for downloading
        $labels = [
            'model' => "Vehicle model",
            'year_manufactured' => "Vehicle year man.",
            'reg_nomer' => "Vehicle №",
            'name' => 'Type of Fuel',
            'odo_start' => 'Odometer at the beginning',
            'odo_end' => 'Odometer at the end',
            'distance' => 'Mileage for the period',
            'quantity' => 'Loaded fuel',
            'consumption' => 'Consumption per 100 km.',
        ];

        $this->response->getHeaders()->addHeaders([
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fuel_report_['.$from.'_'.$to.'].csv"',
            'Content-Transfer-Encoding' => 'binary',
        ]);
        $csv = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
        fputcsv($csv, array_values($labels));
        foreach ($data as $v) {
            fputcsv($csv, array_values($v));
        }
        rewind($csv);
        $this->response->setContent(stream_get_contents($csv));

        return $this->response;
    }

    public function TransireAction()
    {
        $from = $this->params()->fromQuery('from', date('Y-m-01', strtotime('-1 month')));
        $to = $this->params()->fromQuery('to', date('Y-m-t', strtotime('-1 month')));
        $vehicle_id = $this->params()->fromQuery('vehicle_id', null);
        $download = $this->params()->fromQuery('download', false);
        $backLink = $this->url()->fromRoute('application', ['controller'=>'report', 'action'=>'list']);

        $transireView = $this->getService(TransireReportView::class);

        $recordSet = $transireView->fetchAll(function(Select $select) use ($from, $to, $vehicle_id) {
            $select->columns([
                'start_odometer' => new Expression('MIN(start_odometer)'),
                'end_odometer' => new Expression('MAX(end_odometer)'),
                'driver_id' => 'driver_id',
                'vehicle_id' => 'vehicle_id',
                'route' => new Expression('GROUP_CONCAT(route)'),
                'date' => 'date',
                'driver_name' => 'driver_name',
                'driver_office_name' => 'driver_office_name',
                'driver_office_city' => 'driver_office_city',
                'vehivle_model' => 'vehivle_model',
                'vehicle_year_manufactured' => 'vehicle_year_manufactured',
                'vehicle_reg_nomer' => 'vehicle_reg_nomer',
                'vehicle_color' => 'vehicle_color',
            ]);
            $pred = new Predicate();
            $pred->between('date', $from, $to);
            if ($vehicle_id) {
                $pred->equalTo('vehicle_id', $vehicle_id);
            }
            $select->where($pred);
            $select->group(['date', 'driver_id', 'vehicle_id']);
        });

        $data = [];
        foreach ($recordSet as $v) {
            $distance = $v->end_odometer - $v->start_odometer;
            $data[] = [
                'date' => (new \DateTime($v->date))->format('d.m.Y г.'),
                'start_odometer' => $v->start_odometer,
                'end_odometer' => $v->end_odometer,
                'route' => $v->route,
                'distance' => $distance,
                'driver_name' => $v->driver_name,
                /*'driver_id' => $v->driver_id,
                'vehicle_id' => $v->vehicle_id,
                'driver_office_name' => $v->driver_office_name,
                'driver_office_city' => $v->driver_office_city,
                'vehivle_model' => $v->vehivle_model,
                'vehicle_year_manufactured' => $v->vehicle_year_manufactured,
                'vehicle_reg_nomer' => $v->vehicle_reg_nomer,
                'vehicle_color' => $v->vehicle_color,*/
            ];
        }

        if (!$download) {
            $vehicleTable = $this->getService(VehicleTable::class);
            $vehicles = $vehicleTable->fetchAll();
            $this->layout()->setVariable('backlink', $backLink);
            $viewData = [
                'backlink' => $backLink,
                'title' => 'Справка по пътни листи',
                'from' => $from,
                'to' => $to,
                'vehicle_id' => $vehicle_id,
                'vehicles' => $vehicles,
                'data' => $data,
                'downloadUrl' => $this->url()->fromRoute(
                    'application',
                    ['controller'=>'report', 'action'=>'transire'],
                    ['query' => ['from' => $from, 'to' => $to, 'vehicle_id' => $vehicle_id, 'download'=>'csv']]
                ),
            ];
            return new ViewModel($viewData);
        }

        // Generate data for downloading
        $labels = [
            'date' => 'date',
            'start_odometer' => 'dometer at the beginning',
            'end_odometer' => 'Odometer at the end',
            'route' => 'route',
            'distance' => 'Mileage for the period',
            'driver_name' => 'Driver name',
            /*'driver_id' => 'driver_id',
            'vehicle_id' => 'vehicle_id',
            'driver_office_name' => 'Office name',
            'driver_office_city' => 'Office city',
            'vehivle_model' => 'Vehicle model',
            'vehicle_year_manufactured' => 'Vehicle year man',
            'vehicle_reg_nomer' => 'Vehicle №',*/
        ];

        $this->response->getHeaders()->addHeaders([
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transire_report_['.$from.'_'.$to.'].csv"',
            'Content-Transfer-Encoding' => 'binary',
        ]);
        $csv = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
        fputcsv($csv, array_values($labels));
        foreach ($data as $v) {
            fputcsv($csv, array_values($v));
        }
        rewind($csv);
        $this->response->setContent(stream_get_contents($csv));

        return $this->response;
    }
}
