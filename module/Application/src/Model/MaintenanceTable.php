<?php

namespace Application\Model;

class MaintenanceTable extends AbstractTable
{
    /**
     * @param Maintenance $model
     * @return int
     */
    public function update(Maintenance $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        return $this->updateRecord($data, ['id' => $model->id]);
    }

    /**
     * @param Maintenance $model
     * @return int
     */
    public function insert(Maintenance $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        return $this->insertRecord($data);
    }

    /**
     * @param Maintenance $model
     * @return int
     */
    public function delete(Maintenance $model) :int
    {
        return $this->deleteRecord(['id' => $model->id]);
    }
}
