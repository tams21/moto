<?php
namespace Application\Model;

use Laminas\Db\TableGateway\TableGateway;

class VehicleTable extends AbstractTable
{
    private TableGateway $vehicleFuelTableGateway;

    public function __construct(TableGateway $tableGateway, TableGateway $vehicleFuelTableGateway)
    {
        parent::__construct($tableGateway);
        $this->vehicleFuelTableGateway = $vehicleFuelTableGateway;
    }

    public function fetchById(int $id)
    {
        $model = $this->getTableGateway()->select(['id'=>$id]);
        return $model->current();
    }

    public function fetchByIdWithFuel(int $id)
    {
        $this->getTableGateway()->getSql()->select();
        $resultSet = $this->getTableGateway()->select(['id' => $id]);
        $model = $resultSet->current();
        $fuelsSet = $this->vehicleFuelTableGateway->select(['vehicle_id'=>$id]);
        $fuels = [];
        foreach ($fuelsSet as $f) {
            $fuels[] = $f->fuel_id;
        }
        $model->setFuels($fuels);
        return $model;
    }

    /**
     * @param Vehicle $model
     * @return int
     */
    public function update(Vehicle $model) :int
    {
        $data = $model->getArrayCopy();
        $id =  $model->id;
        unset($data['id']);
        $fuels = $data['fuels'];
        unset($data['fuels']);

        $status = $this->updateRecord($data, ['id' => $id]);

        $currentFuelsSet = $this->vehicleFuelTableGateway->select(['vehicle_id' => $id]);
        $currentFuels = [];
        $forDelete = [];
        $forAdd = [];
        foreach ($currentFuelsSet as $f) {
            $currentFuels[] = $f->fuel_id;
            if (!in_array($f->fuel_id, $fuels)) {
                $forDelete[] = $f->fuel_id;
            }
        }

        foreach ($fuels as $v) {
            if (!in_array($v, $currentFuels)) {
                $forAdd[] = $v;
            }
        }

        $this->vehicleFuelTableGateway->delete(['vehicle_id' => $id, 'fuel_id'=>$forDelete]);
        $status = $this->insertFuels($id, $forAdd);

        return $status;
    }

    /**
     * @param Vehicle $model
     * @return int
     */
    public function insert(Vehicle $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        $fuels = $data['fuels'];
        unset($data['fuels']);
        $status = $this->insertRecord($data);
        if ($status) {
            $id = $this->getTableGateway()->getLastInsertValue();
            $status = $this->insertFuels($id, $fuels);
        }
        return $status;
    }

    /**
     * @param Vehicle $model
     * @return int
     */
    public function delete(Vehicle $model) :int
    {
        $this->vehicleFuelTableGateway->delete(['vehicle_id' =>  $model->id]);
        return $this->deleteRecord(['id' => $model->id]);
    }

    /**
     * @param int $status
     * @param $fuels
     * @return int
     */
    private function insertFuels($id, $fuels): int
    {
        $insert = $this->vehicleFuelTableGateway->getSql()->insert();
        $insert->columns(['vehicle_id', 'fuel_id']);
        foreach ($fuels as $f) {
            $insert->values(['vehicle_id' => $id, 'fuel_id' => $f]);
            $status = $this->vehicleFuelTableGateway->insertWith($insert);
            if (!$status) {
                return $status;
            }
        }
        return $status??1;
    }
}

