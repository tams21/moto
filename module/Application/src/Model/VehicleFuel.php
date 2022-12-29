<?php

namespace Application\Model;

class VehicleFuel extends \ArrayObject
{
    public $vehicle_id;
    public $fuel_id;

    public function exchangeArray($input)
    {
        $this->vehicle_id=$input["vehicle_id"];
        $this->fuel_id=$input["fuel_id"];
        parent::exchangeArray($input);
    }
}
