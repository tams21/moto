<?php
namespace Application\Model;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;

class UserTable extends AbstractTable
{
    /**
     * @param User $model
     * @return int
     */
    public function update(User $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        return $this->updateRecord($data, ['id' => $model->id]);
    }

    /**
     * @param User $model
     * @return int
     */
    public function insert(User $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        $this->insertRecord($data);
        return $this->getTableGateway()->getLastInsertValue();
    }

    /**
     * @param User $model
     * @return int
     */
    public function delete(User $model) :int
    {
        return $this->deleteRecord(['id' => $model->id]);
    }
}

