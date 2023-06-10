<?php
namespace Application\Model;

class RepairTable extends AbstractTable
{
    /**
     * @param Repair $model
     * @return int
     */
    public function insert(Repair $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        $this->insertRecord($data);
        return $this->getTableGateway()->getLastInsertValue();
    }

    /**
     * @param Repair $model
     * @return int
     */
    public function update(Repair $model) :int
    {
        $data = $model->getArrayCopy();
        $id =  $model->id;
        unset($data['id']);
        $status = $this->updateRecord($data, ['id' => $id]);
        return $status;
    }

    /**
     * @param Repair $model
     * @return int
     */
    public function delete(Repair $model) :int
    {
        return $this->deleteRecord(['id' => $model->id]);
    }
}

