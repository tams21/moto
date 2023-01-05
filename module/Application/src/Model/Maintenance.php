<?php

namespace Application\Model;

class Maintenance extends \ArrayObject
{
    public $id;
    public $name;

    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->name=$input["name"];
        parent::exchangeArray($input);
    }
}
