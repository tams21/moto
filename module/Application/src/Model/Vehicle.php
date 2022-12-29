<?php
namespace Application\Model;

class Vehicle extends \ArrayObject
{
    public $id;
    public $reg_nomer;
    public $model;
    public $odometer;
    public $color;
    public $year_manufactured;
    public $notes;

    private $fuels = [];

    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->reg_nomer=$input["reg_nomer"];
        $this->model=$input["model"];
        $this->odometer=$input["odometer"];
        $this->color=$input["color"];
        $this->year_manufactured=$input["year_manufactured"];
        $this->notes=$input["notes"];
        parent::exchangeArray($input);
    }

    public function getFuels() : array
    {
        return $this->fuels;
    }
    public function setFuels($fuels) : void
    {
        $this->fuels = $fuels;
        $this['fuels'] = $fuels;
    }
}

