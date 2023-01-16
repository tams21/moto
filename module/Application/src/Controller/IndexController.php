<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\AssignmentTable;
use Application\Model\DriverTable;
use Application\Model\FuelTable;
use Application\Model\RefuelingTable;
use Application\Model\VehicleTable;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Model\UserTable;

class IndexController extends AbstractActionController
{
    private DriverTable $driverTable;
    private AssignmentTable $assignmentTable;
    private VehicleTable $vehicleTable;
    private RefuelingTable $refuelingTable;

    public function __construct(
        DriverTable $driverTable,
        AssignmentTable $assignmentTable,
        VehicleTable $vehicleTable,
        RefuelingTable $refuelingTable
    ) {
        $this->driverTable = $driverTable;
        $this->vehicleTable=$vehicleTable;
        $this->assignmentTable = $assignmentTable;
        $this->refuelingTable = $refuelingTable;
    }
    public function indexAction()
    {
        $diverId = (int) $this->identity()->driver_id;
        if (!$diverId) {
            return new ViewModel();
        }

        $driver=$this->driverTable->fetchById($diverId);
        if (!$driver) {
            return new ViewModel();
        }
        $assignmentSet = $this->assignmentTable->fetchAll(function(Select $select) {
            $today = new \DateTime();
            $todayStr = $today->format('Y-m-d');
            $select->where("(start_day <= '{$todayStr}' AND end_day > '{$todayStr}')"
                ." OR (start_day <= '{$todayStr}' AND end_day IS NULL)");
            $select->order("start_day DESC");
        });
        $assignment = $assignmentSet->current();
        if($assignment) {
            $vehicle = $this->vehicleTable->fetchById($assignment->vehicle_id);

            $refuelingSet = $this->refuelingTable->fetchAll(function (Select $select) use ($vehicle) {
                $select->where("vehicle_id = '{$vehicle->id}'");
                $select->order('date_refueling DESC');
                $select->limit(1);
            });
            $refueling = $refuelingSet->current();
        }

        $viewData = [
            'vehicle' => $vehicle??null,
            'refueling' => $refueling??null,
        ];
        return new ViewModel($viewData);
    }
    
    public function WorldAction()
    {
        return new ViewModel();
    }
    
}
