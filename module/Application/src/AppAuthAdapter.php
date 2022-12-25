<?php

namespace Application;

use Application\Model\User;
use Application\Model\UserTable;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;

class AppAuthAdapter implements AdapterInterface
{
    private $username;
    private $password;
    /**
     * @var UserTable
     */
    private $userTable;


    public function __construct(UserTable $userTable)
    {

        $this->userTable = $userTable;
    }

    public function setCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function authenticate()
    {
        /*if ($this->username === 'admin' && $this->password === '12345'){
            $userdata = [
                'username' => $this->username,
                'password' => $this->password,
                'name' => 'Administrator',
            ];
            return new Result(Result::SUCCESS, $userdata);
        }*/
        /**/
        try {
            $users = $this->userTable->fetchAllByUsername($this->username);
            foreach ($users as $v) {
                if ($v->checkPasswordAgainst($this->password)) {
                    return new Result(Result::SUCCESS, $v);
                }
            }
        } catch (\Exception $e) {
            return new Result(Result::FAILURE_UNCATEGORIZED, [], [$e->getMessage()]);
        }

        return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, []);
    }


}
