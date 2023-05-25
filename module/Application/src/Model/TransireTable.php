<?php
namespace Application\Model;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGatewayInterface;

class TransireTable extends AbstractTable
{
    public function fetchAllPaginatedWithDetails(int $page = 1, int $onPage = 20, $filter = null): ResultSetInterface
    {
        return $this->getTableGateway()->select(function(Select $select) use ($page, $onPage, $filter) {
            $select->quantifier('SQL_CALC_FOUND_ROWS');
            $select->join(['d' => 'drivers'], "transire.driver_id=d.id", ['driver_name' => 'name']);
            $select->join(['v' => 'vehicles'], "transire.vehicle_id=v.id", ['vehicle_reg_nomer' => 'reg_nomer', 'vehicle_model' => 'model', 'vehicle_color' => 'color', 'vehicle_year_manufactured' => 'year_manufactured']);
            $select->limit($onPage);
            $select->order('id DESC');
            $select->offset(($page-1) * $onPage);
            if (is_callable($filter)) {
                $filter($select);
            }
        });
    }

    /**
     * @param Transire $model
     * @return int
     */
    public function insert(Transire $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        $this->insertRecord($data);
        return $this->getTableGateway()->getLastInsertValue();
    }

    /**
     * @param Transire $model
     * @return int
     */
    public function update(Transire $model) :int
    {
        $data = $model->getArrayCopy();
        $id =  $model->id;
        unset($data['id']);
        $status = $this->updateRecord($data, ['id' => $id]);
        return $status;
    }

    /**
     * @param Transire $model
     * @return int
     */
    public function delete(Transire $model) :int
    {
        return $this->deleteRecord(['id' => $model->id]);
    }
}

