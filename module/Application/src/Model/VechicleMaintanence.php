<?php
namespace Application\Model;

class VechicleMaintanence extends \ArrayObject
{
    public $id;
    public $id_vechicles;
    public $id_maintanence_shadule;
    public $start_date;
    public $еxpiration_date;
    public $start_kilometers;
    public $expiration_kimoleters;
    
    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->id_vechicles=$input["id_vechicles"];
        $this->id_maintanence_shadule=$input["id_maintanence_shadule"];
        $this->start_date=$input["start_date"];
        $this->еxpiration_date=$input["еxpiration_date"];
        $this->start_kilometers=$input["start_kilometers"];
        $this->expiration_kimoleters=$input["expiration_kimoleters"];
    }
}

