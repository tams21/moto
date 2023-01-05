<?php
namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;

class MaintenanceScheduleTable extends AbstractTable
{
    /**
     * @param MaintenanceSchedule $model
     * @return int
     */
    public function update(MaintenanceSchedule $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        return $this->updateRecord($data, ['id' => $model->id]);
    }

    /**
     * @param MaintenanceSchedule $model
     * @return int
     */
    public function insert(MaintenanceSchedule $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        return $this->insertRecord($data);
    }

    /**
     * @param MaintenanceSchedule $model
     * @return int
     */
    public function delete(MaintenanceSchedule $model) :int
    {
        return $this->deleteRecord(['id' => $model->id]);
    }
}

