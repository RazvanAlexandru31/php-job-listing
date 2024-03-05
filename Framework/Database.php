<?php

namespace Framework;
use PDO;
use PDOException;
use Exception;

class Database
{
    public $pdo;

    /**
     * Constructor for Databse class
     * @param array $config
     */

    public function __construct($config)
    {
        $dsn = "{$config['type']}:host={$config['server']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
        $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Query the databse
     * @param string @query
     * @param $params
     * @return PDOStatement
     * @throws PDOException
     */

    public function query($query, $params = [])
    {
        try {
            $statement = $this->pdo->prepare($query);
            foreach ($params as $param => $value) {
                $statement->bindValue(':' . $param, $value, PDO::PARAM_INT || PDO::PARAM_STR);
            }
            $statement->execute();
            return $statement;
        } catch (PDOException $e) {
            throw new Exception('Query failed to execute ' . $e->getMessage());
        }
    }
}
