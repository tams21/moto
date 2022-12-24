<?php
namespace module\Application\src\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;

class OfficeTable
{
    private $tableGateway;
    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway=$tableGateway;
    }
    public function fetchAll()
    {
        return $this->tableGateway->select();
    }
}

