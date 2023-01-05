<?php
namespace Application\Model;
use Laminas\Db\TableGateway\TableGatewayInterface;

class RefuelingTable extends AbstractTable
{
    /**
     * @param Refueling $model
     * @return int
     */
    public function insert(Refueling $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        $this->insertRecord($data);
        return $this->getTableGateway()->getLastInsertValue();
    }

    public function update(Refueling $model) :int
    {
        $data = $model->getArrayCopy();
        $id =  $model->id;
        unset($data['id']);
        $status = $this->updateRecord($data, ['id' => $id]);
        return $status;
    }

    /**
     * @param Refueling $model
     * @return int
     */
    public function delete(Refueling $model) :int
    {
        return $this->deleteRecord(['id' => $model->id]);
    }
}

