<?php
namespace Application\Model;
use Laminas\Crypt\Password\Bcrypt;

class User extends \ArrayObject
{
    public $id;
    public $username;
    public $password;
    public $role;
    public $created;
    
    public function exchangeArray($input)
    {
        $this->id=$input["id"];
        $this->username=$input["username"];
        $this->password=$input["password"];
        $this->role=$input["role"];
        $this->created=$input["created"];
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

