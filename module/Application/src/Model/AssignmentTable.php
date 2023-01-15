<?php
namespace Application\Model;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Join;

class AssignmentTable extends AbstractTable
{
    /**
     * @param Assignment $model
     * @return int
     */
    public function update(Assignment $model) :int
    {
        $data = $model->getArrayCopy();
        $id =  $model->id;
        unset($data['id']);

        return $this->updateRecord($data, ['id' => $id]);
    }

    /**
     * @param Assignment $model
     * @return int
     */
    public function insert(Assignment $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        $this->insertRecord($data);
        return $this->getTableGateway()->getLastInsertValue();
    }

    /**
     * @param Assignment $model
     * @return int
     */
    public function delete(Assignment $model) :int
    {
        return $this->deleteRecord(['id' => $model->id]);
    }
}

