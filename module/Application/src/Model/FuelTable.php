<?php
namespace Application\Model;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;

class FuelTable extends AbstractTable
{
    /**
     * @param int $id
     * @return ResultSetInterface
     */
    public function fetchAllForVehicle(int $id) : ResultSetInterface
    {
        /** @var Select $select */
        $select = $this->getTableGateway()->getSql()->select();
        $select->join(['vf'=>'vehicle_fuel'], 'vf.fuel_id = fuel.id vf.vehicle_id='.$id);
        return $this->getTableGateway()->selectWith($select);
    }

    /**
     * @param Fuel $model
     * @return int
     */
    public function update(Fuel $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        return $this->updateRecord($data, ['id' => $model->id]);
    }

    /**
     * @param Fuel $model
     * @return int
     */
    public function insert(Fuel $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        return $this->insertRecord($data);
    }

    /**
     * @param Fuel $model
     * @return int
     */
    public function delete(Fuel $model) :int
    {
        return $this->deleteRecord(['id' => $model->id]);
    }

}

