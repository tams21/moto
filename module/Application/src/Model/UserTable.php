<?php
namespace Application\Model;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;

class UserTable
{
    private $tableGateway;
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway=$tableGateway;
    }
    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    /**
     * @param int $page
     * @param int $onPage
     * @return ResultSetInterface
     */
    public function fetchAllPaginated(int $page=1, int $onPage=20): ResultSetInterface
    {
        $select = $this->tableGateway->getSql()->select();
        $select->quantifier('SQL_CALC_FOUND_ROWS');
        $select->limit($onPage);
        $select->limit($onPage);
        $select->offset(($page-1) * $onPage);
        return $this->tableGateway->selectWith($select);
    }
    public function getLastFoundRows(): int
    {
        $sql = $this->tableGateway->getSql();
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

    public function fetchById(int $id): User
    {
        $user = $this->tableGateway->select(['id'=>$id]);
        return $user->current();
    }

    public function fetchAllByUsername($username)
    {
        return $this->tableGateway->select(['username'=>$username]);
    }

    /**
     * @param User $model
     * @return int
     */
    public function update(User $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        return $this->tableGateway->update($data, ['id' => $model->id]);
    }

    /**
     * @param User $model
     * @return int
     */
    public function insert(User $model) :int
    {
        $data = $model->getArrayCopy();
        unset($data['id']);
        return $this->tableGateway->insert($data);
    }

    /**
     * @param User $model
     * @return int
     */
    public function delete(User $model) :int
    {
        return $this->tableGateway->delete(['id' => $model->id]);
    }
}

