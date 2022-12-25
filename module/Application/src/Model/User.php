<?php
namespace Application\Model;
use Laminas\Crypt\Password\Bcrypt;

class User extends \ArrayObject
{
    static private $crypt;
    public $id;
    public $name;
    public $username;
    public $password;
    public $role;
    public $created;

    public function __construct($array = [], int $flags = 0, $iteratorClass = "ArrayIterator")
    {
        parent::__construct($array, $flags, $iteratorClass);
        if (!empty($array)) {
            $this->exchangeArray($array);
        }
    }


    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->name=$input["name"];
        $this->username=$input["username"]??null;
        $this->password=$input["password"]??null;
        $this->role=$input["role"]??null;
        $this->created=$input["created"]??null;
    }
    
    public function checkPasswordAgainst(string $password): bool
    {
        return self::getCrypt()->verify($password, $this->password);
    }
    
    public function changePassword($newPassword) {
        $this->password = self::getCrypt()->create($newPassword);
    }
    
    private static function getCrypt()
    {
        if (!is_null(self::$crypt)) {
            return self::$crypt;
        }
        return self::$crypt = new Bcrypt([
            'cost'=>11
        ]);
    }
}

