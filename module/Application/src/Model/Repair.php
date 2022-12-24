<?php
namespace Application\Model;

class Repair extends \ArrayObject
{
    public $id;
    public $description;
    public $passwordate_repaird;
    public $car_service;
    public $notes;
    public $id_vechicle;
    
    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->description=$input["description"];
        $this->date_repair=$input["date_repair"];
        $this->car_service=$input["car_service"];
        $this->notes=$input["notes"];
        $this->id_vechicle=$input["id_vechicle"];
    }
}

