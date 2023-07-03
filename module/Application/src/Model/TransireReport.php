<?php

namespace Application\Model;

class TransireReport extends \ArrayObject
{
    public function __get($name)
    {
        return $this[$name]??null;
    }

    public function __set($name, $value)
    {
        $this[$name] = $value;
    }
}
