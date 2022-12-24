<?php
namespace Application\Model;

class MaintanenceShadule extends \ArrayObject
{
    public $id;
    public $id_vechicles;
    public $id_maintenence;
    public $period_days;
    public $kilometers;
    public $notify_days_before;
    public $notify_kilometers_before;
    
    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->id_vechicles=$input["id_vechicles"];
        $this->id_maintenencer=$input["id_maintenence"];
        $this->period_days=$input["period_days"];
        $this->kilometers=$input["kilometers"];
        $this->notify_days_before=$input["notify days before"];
        $this->notify_kilometers_before=$input["notify_kilometers_before"];
    }
}

