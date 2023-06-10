<?php
namespace Application\Model;

class Repair extends \ArrayObject
{
    public $id;
    public $description;
    public $date_repair;
    public $invoice_issuer;
    public $invoice_num;
    public $car_service;
    public $notes;
    public $vehicle_id;
    
    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->description=$input["description"];
        $this->date_repair=$input["date_repair"];
        $this->invoice_issuer=$input["invoice_issuer"];
        $this->invoice_num=$input["invoice_num"];
        $this->car_service=$input["car_service"];
        $this->notes=$input["notes"];
        $this->vehicle_id=$input["vehicle_id"];
    }
}

