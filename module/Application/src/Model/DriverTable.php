<?php
namespace Application\Model;

class DriverTable extends AbstractTable
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
        return $this->insertRecord($data);

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

