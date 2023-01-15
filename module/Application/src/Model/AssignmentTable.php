<?php
namespace Application\Model;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Join;

class AssignmentTable extends AbstractTable
{
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

