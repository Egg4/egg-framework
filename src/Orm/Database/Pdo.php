<?php

namespace Egg\Orm\Database;

class Pdo extends AbstractDatabase
{
    protected $pdo;
    protected $transactionCount = 0;

    public function __construct(array $settings = [])
    {
        $settings = array_merge([
            'persistent'    => true,
            'autocommit'    => true,
            'timeout'       => 30,
            'mysqlUtf8'     => true,
        ], $settings);

        parent::__construct($settings);

        $this->pdo = new \PDO($settings['dsn'], $settings['login'], $settings['password'], [
            \PDO::ATTR_PERSISTENT           => $settings['persistent'],
            \PDO::ATTR_ERRMODE              => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_AUTOCOMMIT           => $settings['autocommit'],
            \PDO::ATTR_TIMEOUT              => $settings['timeout'],
        ]);
        if (strpos($settings['dsn'], 'mysql') === 0 AND $settings['mysqlUtf8']) {
            $this->pdo->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
        }
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