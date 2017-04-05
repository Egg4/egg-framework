<?php

namespace Egg\Orm\Database;

class Pdo extends AbstractDatabase
{
    protected $pdo;
    protected $transactionCount = 0;

    public function __construct(array $settings = [])
    {
        $settings = array_merge([
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'dbname'        => 'test',
            'login'         => 'root',
            'password'      => '',
            'persistent'    => true,
            'autocommit'    => true,
            'timeout'       => 30,
            'encoding'     => 'utf8',
        ], $settings);

        parent::__construct($settings);

        $dsn = sprintf('%s:host=%s;dbname=%s;charset=%s', $settings['driver'], $settings['host'], $settings['dbname'], $settings['encoding']);
        $this->pdo = new \PDO($dsn, $settings['login'], $settings['password'], [
            \PDO::ATTR_PERSISTENT           => $settings['persistent'],
            \PDO::ATTR_ERRMODE              => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_AUTOCOMMIT           => $settings['autocommit'],
            \PDO::ATTR_TIMEOUT              => $settings['timeout'],
        ]);
    }

    public function getName()
    {
        return $this->settings['dbname'];
    }

    public function execute($sql, array $params = [])
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return new \Egg\Orm\Statement\Pdo($statement);
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction()
    {
        if (!$this->transactionCount++) return $this->pdo->beginTransaction();
        $this->pdo->exec('SAVEPOINT transaction_' . $this->transactionCount);

        return $this->transactionCount >= 0;
    }

    public function commit()
    {
        if (!--$this->transactionCount) return $this->pdo->commit();

        return $this->transactionCount >= 0;
    }

    public function rollback()
    {
        if (--$this->transactionCount) {
            $this->exec('ROLLBACK TO transaction_' . $this->transactionCount + 1);
            return true;
        }

        return $this->pdo->rollback();
    }
}