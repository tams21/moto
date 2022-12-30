<?php
namespace Application\Model;

class OfficeTable extends AbstractTable
{
    /**
     * @param Office $model
     * @return int
     */
    public function update(Office $model) :int
    {
        $data = $model->getArrayCopy();
        $id =  $model->id;
        unset($data['id']);

        return $this->updateRecord($data, ['id' => $id]);
    }

    /**
     * @param Office $model
     * @return int
     */
    public function insert(Office $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        return $this->insertRecord($data);

    }

    /**
     * @param Office $model
     * @return int
     */
    public function delete(Office $model) :int
    {
        return $this->deleteRecord(['id' => $model->id]);
    }
}

