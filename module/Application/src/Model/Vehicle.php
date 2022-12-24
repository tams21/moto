<?php
namespace Application\Model;

class Vehicle extends \ArrayObject
{
    public $id;
    public $reg_nomer;
    public $model;
    public $fuel;
    public $odometer;
    
    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->reg_nomer=$input["reg_nomer"];
        $this->model=$input["model"];
        $this->fuel=$input["fuel"];
        $this->odometer=$input["odometer"];
    }
}

