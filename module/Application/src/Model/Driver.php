<?php
namespace Application\Model;

class Driver extends \ArrayObject
{
    public $id;
    public $name;
    public $office_id;
    public $vehicle_id;

    private $office;
    private $vehicle;

    public function exchangeArray($input)
    {
        $this->id = $input["id"];
        $this->name = $input["name"];
        $input["office_id"] = empty($input["office_id"]) ? null : (int) $input["office_id"];
        $input["vehicle_id"] = empty($input["vehicle_id"]) ? null : (int) $input["vehicle_id"];
        $this->office_id = $input["office_id"]??null;
        $this->vehicle_id = $input["vehicle_id"]??null;;

        if (!empty($input["office_name"])) {
            $officeData = [
                'id' => $input["office_id"],
                'name' => $input["office_name"],
                'city' => $input["office_city"]??''
            ];
            $office = new Office();
            $office->exchangeArray($officeData);
            $this->setOffice($office);
            unset($input["office_name"], $input["office_city"]);
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
     * @return Office
     */
    public function getOffice(): ?Office
    {
        return $this->office;
    }

    /**
     * @param Office $office
     */
    public function setOffice(Office $office): void
    {
        $this->office = $office;
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
