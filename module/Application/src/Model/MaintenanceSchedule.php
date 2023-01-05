<?php
namespace Application\Model;

class MaintenanceSchedule extends \ArrayObject
{
    public $id;
    public $vehicle_id;
    public $maintenance_id;
    public $period_days;
    public $kilometers;
    public $notify_days_before;
    public $notify_kilometers_before;

    private $maintenance;
    
    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->vehicle_id=$input["vehicle_id"];
        $this->maintenance_id=$input["maintenance_id"];
        $this->period_days=$input["period_days"]??'';
        $this->kilometers=$input["kilometers"]??'';
        $this->notify_days_before=$input["notify_days_before"]??'';
        $this->notify_kilometers_before=$input["notify_kilometers_before"]??'';
        parent::exchangeArray($input);

        if (!empty($input['maintenance_name'])) {
            $maintenance = new Maintenance();
            $maintenance->exchangeArray([
                'id'=>$this->maintenance_id,
                'name'=>$input['maintenance_name'],
            ]);
            $this->setMaintenance($maintenance);
        }
    }

    /**
     * @return Maintenance
     */
    public function getMaintenance() : ?Maintenance
    {
        return $this->maintenance;
    }

    /**
     * @param Maintenance $maintenance
     */
    public function setMaintenance(Maintenance $maintenance): void
    {
        $this->maintenance = $maintenance;
    }

}

