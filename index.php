<?php

define('ENVIRONMENT', 'DEVELOPMENT');

/**
 * Connects with the database and has a built-in query builder
 * (Make sure to call getFirst() or getAll() at the end of the query).
 * @example Database $db->select('users')->getFirst();
 */
class Database
{

    protected PDO $connection;
    protected string $query = '';
    protected $selectParams = [];
    protected PDOStatement $data;

    /**
     * Requires an array containing [$host, $dbname, $user, $password]
     * to connect with the configured database on the file (dbconfig.php).
     */
    public function __construct()
    {
        [$host, $dbname, $user, $password] = require('dbconfig.php');

        $dsn = "mysql:host=$host;dbname=$dbname";

        $this->connection = new PDO($dsn, $user, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]); 
    }


    /**
     * Add SELECT clause to the query
     */
    public function select(string $table, array $columns = ['*']): self
    {
        $columns = implode(', ', $columns);

        $this->query = "SELECT $columns FROM $table ";

        return $this;
    }


    /**
     * Add WHERE clause to the query
     * @param Array $params Associative array 
     * @example $params ['id' => '2'] equals: id = 2 in the sql
     * @example $params ['id' => '>= 1'] equals: id >= 1 in the sql
     * 
     * Make sure to match the array keys with the wildcards in the sql and to have
     * 
     * a space between the operators and the value while passing both in the array
     */
    public function where(array $params): self
    {
        
        foreach ($params as $key => $value) {

            if (strpos($this->query, 'WHERE') === false) {
                $this->query .= 'WHERE ';
            }
            

            if(!preg_match('/[<>=]/', $value)){
                 
                $this->selectParams[$key] = $value;
                $this->query .= "$key = :$key ";
                
            }else{
                
                $pieces = explode(' ', $value);
                $operators = $pieces[0];
                $this->selectParams[$key] = $pieces[1];
                
                $this->query .= "$key $operators :$key ";
                
            }

            if($key !== array_key_last($params)){
                $this->query .= 'AND ';
            }

        }

        return $this;
    }


    /**
    * Add the INSERT clause to the query
    * @return bool|array true for succes and false or errorInfo for failures if $returnErrorInfo = true
    */
    public function insert(string $table, array $params = [], $returnErrorInfo = false)
    {

        $columns = implode(', ', array_keys($params));
        $values = ':' . implode(', :', array_keys($params));

        $sql = "INSERT INTO $table ($columns) VALUES($values)";

        $query = $this->query($sql, $params);
        
        if($query->rowCount() > 0){
            return true;
        }else{

            if($returnErrorInfo){
                return $query->errorInfo();
            }
            
            return false;
        }

    }


    /**
    * Add ORDER BY clause to the query
    */
    public function orderBy(string $column, string $order = 'ASC'):self
    {
        $this->query .= "ORDER BY $column $order ";
        
        return $this;
    }


    /**
    * Receives a whole sql statement to query into the database
    * @example $sql SELECT * FROM users WHERE id >= :id
    * @example $params ['id' => 1]
    *
    * Make sure to match the array keys with the wildcards in the sql
    */
    public function query(string $sql, array $params = []):PDOStatement
    {
        $query = $this->connection->prepare($sql);

        $query->execute($params);
        
        return $query;
    }


    /**
    * Fetch the first result from the query
    */
    public function getFirst($fetchMode = PDO::FETCH_ASSOC)
    {
        $query = $this->query($this->query, $this->selectParams);

        return $query->fetch($fetchMode);
    }


    /**
    * Fetch all the results from the query
    */
    public function getAll($fetchMode = PDO::FETCH_ASSOC)
    {
        $query = $this->query($this->query, $this->selectParams);

        return $query->fetchAll($fetchMode);
    }
}