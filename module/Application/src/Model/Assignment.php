<?php
namespace Application\Model;

class Assignment extends \ArrayObject
{
    public $id;
    public $vehicle_id;
    public $driver_id;

    public $start_day;
    public $end_day;

    private $driver;
    private $vehicle;

    public function exchangeArray($input)
    {
        $this->id = $input["id"];
        $input["vehicle_id"] = empty($input["vehicle_id"]) ? null : (int) $input["vehicle_id"];
        $input["driver_id"] = empty($input["driver_id"]) ? null : (int) $input["driver_id"];
        $this->vehicle_id = $input["vehicle_id"];;
        $this->driver_id = $input["driver_id"];
        $this->start_day = $input["start_day"];
        $this->end_day = $input["end_day"];

        if (!empty($input["driver_name"])) {
            $driverData = [
                'id' => $input["driver_id"],
                'name' => $input["driver_name"],
                'office_id' => $input["driver_office_id"]??null,
                'vehicle_id' => $input["driver_vehicle_id"]??null
            ];
            $driver = new Driver();
            $driver->exchangeArray($driverData);
            $this->setDriver($driver);
            unset($input["driver_name"], $input["driver_id"], $input["driver_office_id"], $input["driver_vehicle_id"]);
        }
        if (!empty($input["vehicle_reg_nomer"])) {
            $vehicleData = [
                'id' => $input["vehicle_id"],
                'reg_nomer' => $input["vehicle_reg_nomer"],
                'model' => $input["vehicle_model"]??'',
                'odometer' => $input["vehicle_odometer"]??'',
                'color' => $input["vehicle_color"]??'',
                'year_manufactured' => $input["vehicle_year_manufactured"]??'',
                'notes' => $input["vehicle_notes"]??'',
            ];

            $vehicle = new Vehicle();
            $vehicle->exchangeArray($vehicleData);
            $this->setVehicle($vehicle);
            unset($input["vehicle_reg_nomer"],
                $input["vehicle_model"],
                $input["vehicle_odometer"],
                $input["vehicle_color"],
                $input["vehicle_year_manufactured"],
                $input["vehicle_notes"]);
        }
        parent::exchangeArray($input);
    }

    /**
     * @return Driver
     */
    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    /**
     * @param Driver $driver
     */
    public function setDriver(Driver $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * @return Vehicle
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    /**
     * @param Vehicle $vehicle
     */
    public function setVehicle(Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle;
    }


}
