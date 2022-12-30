<?php
namespace Application\Model;

class Office extends \ArrayObject
{
    public $id;
    public $name;
    public $city;
    
    
    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->name=$input["name"];
        $this->city=$input["city"];
        parent::exchangeArray($input);
    }
}
