<?php
namespace Application\Model;

class Transire extends \ArrayObject
{
    public $id;
    public $start_odometer;
    public $end_odometer;
    public $driver_id;
    public $vehicle_id;
    public $route;
    public $date;

    public $driver;
    public $vehicle;

    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->start_odometer=$input["start_odometer"];
        $this->end_odometer=$input["end_odometer"];
        $this->driver_id=$input["driver_id"];
        $this->vehicle_id=$input["vehicle_id"];
        $this->route=$input["route"];
        $this->date=$input["date"];
        if (isset($input["driver_name"])) {
            $driverData = [
                'id' => $input["driver_id"],
                'name' => $input["driver_name"],
                ];
            $this->driver = new Driver($driverData);
            $this->driver->exchangeArray($driverData);
        }
        if (isset($input["vehicle_reg_nomer"])) {
            $vehicleData = [
                'id' => $input["driver_id"],
                'reg_nomer' => $input["vehicle_reg_nomer"],
                'color' => $input["vehicle_color"],
                'year_manufactured' => $input["vehicle_year_manufactured"],
            ];
            $this->vehicle = new Vehicle($vehicleData);
            $this->vehicle->exchangeArray($vehicleData);;
        }
    }
}

