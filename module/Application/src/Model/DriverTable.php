<?php
namespace Application\Model;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Join;

class DriverTable extends AbstractTable
{

    public function fetchAllNotAddedToUser(): ResultSetInterface
    {
        return $this->getTableGateway()->select(function($select) {
            $tbl = $this->getTableGateway()->getTable();
            $select->join(
                ['u'=>'users'],
                "u.driver_id = {$tbl}.id",
                ['driver_name' => 'name'],
                Join::JOIN_LEFT
            );
            $select->where('u.name IS NULL');
        });
    }

    /**
     * @param Driver $model
     * @return int
     */
    public function update(Driver $model) :int
    {
        $data = $model->getArrayCopy();
        $id =  $model->id;
        unset($data['id']);

        return $this->updateRecord($data, ['id' => $id]);
    }

    /**
     * @param Driver $model
     * @return int
     */
    public function insert(Driver $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        $this->insertRecord($data);
        return $this->getTableGateway()->getLastInsertValue();
    }

    /**
     * @param Driver $model
     * @return int
     */
    public function delete(Driver $model) :int
    {
        return $this->deleteRecord(['id' => $model->id]);
    }
}

