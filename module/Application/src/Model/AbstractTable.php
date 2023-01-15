<?php

namespace Application\Model;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\TableGateway\TableGatewayInterface;

class AbstractTable
{
    private $tableGateway;
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getTableGateway() : TableGateway
    {
        return $this->tableGateway;
    }

    public function fetchAll($filter=null)
    {
        return $this->getTableGateway()->select($filter);
    }

    public function fetchAllPaginated(int $page=1, int $onPage=20, $filter = null): ResultSetInterface
    {
        return $this->getTableGateway()->select(function($select) use ($page, $onPage, $filter){
            $select->quantifier('SQL_CALC_FOUND_ROWS');
            $select->limit($onPage);
            $select->order('date_refueling DESC');
            $select->offset(($page-1) * $onPage);
            if (is_callable($filter)) {
                $filter($select);
            }
        });
    }
    public function getLastFoundRows(): int
    {
        $sql = $this->getTableGateway()->getSql();
        $select = new Select(' ');
        $select->setSpecification(Select::SELECT, array(
            'SELECT %1$s' => array(
                array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '),
                null
            )
        ));
        $select->columns(array(
            'total' => new Expression("FOUND_ROWS()")
        ));
        $statement = $sql->prepareStatementForSqlObject($select);
        $result2 = $statement->execute();
        $row = $result2->current();
        return (int)$row['total'];
    }

    public function fetchById(int $id)
    {
        $model = $this->getTableGateway()->select(['id'=>$id]);
        return $model->current();
    }

    public function fetchAllByUsername($username)
    {
        return $this->getTableGateway()->select(['username'=>$username]);
    }

    /**
     * @param \ArrayObject $model
     * @return int
     */
    public function updateRecord(array $data, $where) :int
    {
        return $this->getTableGateway()->update($data, $where);
    }

    /**
     * @param \ArrayObject $model
     * @return int
     */
    public function insertRecord(array $data) :int
    {
        return $this->getTableGateway()->insert($data);
    }

    /**
     * @param \ArrayObject $model
     * @return int
     */
    public function deleteRecord(array $where) :int
    {
        return $this->getTableGateway()->delete($where);
    }
}
