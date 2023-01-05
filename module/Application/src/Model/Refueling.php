<?php
namespace Application\Model;


class Refueling extends \ArrayObject
{
    public $id;
    public $date_refueling;
    public $odometer;
    public $cost;
    public $quantity;
    
    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->date_refueling=$input["date_refueling"];
        $this->odometer=$input["odometer"];
        $this->cost=$input["cost"];
        $this->quantity=$input["quantity"];
    }
}
