<?php
namespace Application\Model;

class Transire extends \ArrayObject
{
    public $id;
    public $start_odometar;
    public $end_odometer;
    public $id_drivers;
    public $route;
    public $date;
    
    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->start_odometar=$input["start_odometar"];
        $this->end_odometer=$input["end_odometer"];
        $this->id_drivers=$input["id_drivers"];
        $this->route=$input["route"];
        $this->date=$input["date"];
    }
}

