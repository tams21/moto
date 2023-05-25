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
        $this->reg_nomer=$input["reg_nomer"]??null;
        $this->model=$input["model"]??null;
        $this->odometer=$input["odometer"]??null;
        $this->color=$input["color"]??null;
        $this->year_manufactured=$input["year_manufactured"]??null;
        $this->notes=$input["notes"]??null;
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

