<?php

namespace Tests\Mocks;


class PDOMock
{
    protected $dsn;
    protected $user;
    protected $pass;

    public $emulate_query_false = false;

    public $valid_user = 'testuser';

    public function __construct(string $dsn, string $username = null, string $password = null, array $options = [])
    {
        $this->dsn = $dsn;
        $this->user = $username;
        $this->pass = $password;

        if ($username !== $this->valid_user) {
            throw new \PDOException('Invalid credentials');
        }
    }

    public function getAttribute(int $attr)
    {
        $pdoReflect = new \ReflectionClass(\PDO::class);

        if (!in_array($attr, array_values($pdoReflect->getConstants()))) {
            return null;
        }

        if ($attr == \PDO::ATTR_SERVER_VERSION) {
            return '5.6.36-82.0-log';
        }

        return null;
    }

    public function query(string $sql, ...$args)
    {
        if ($this->emulate_query_false) {
            return false;
        }

        if (strtoupper($sql) == 'SELECT 1') {
            return 1;
        }

        return false;
    }
}