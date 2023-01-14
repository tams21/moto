<?php

namespace Application\Model;

class FuelReport extends \ArrayObject
{

    public function exchangeArray($input)
    {
        parent::exchangeArray($input);
    }

    public function __get($name)
    {
        return $this[$name]??null;
    }

    public function __set($name, $value)
    {
        $this[$name] = $value;
    }
}
