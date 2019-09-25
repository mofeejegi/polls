<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 29/06/2018
 * Time: 4:07 PM
 */

class MySQLDatabase {

    /**
     * @var $connection mysqli
     */
    private $connection;

    public $last_query;

    function __construct() {
        $this->open_connection();
    }

    public function open_connection() {
        $this->connection = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        if (mysqli_connect_errno()){
            die("Database Connection failed: " . mysqli_connect_error() . "(" . mysqli_connect_errno() . ")");
        }
    }

    public function close_connection() {
        if (isset($this->connection)) {
            $this->connection->close();
        }
    }

    public function query($sql) {
        $this->last_query = $sql;
        $result = $this->connection->query($sql);
        $this->confirm_query($result);
        return $result;
    }

    public function multi_query($sql){
        $this->last_query = $sql;
        $first = $this->connection->multi_query($sql);
        $this->confirm_query($first);

        if ($first) {

            do {
                /* store result set */
                if ($result = $this->connection->store_result()) {
                    $this->confirm_query($result);
                    $result->free();
                }

            } while ($this->connection->next_result());

        }
    }


    private function confirm_query($result){
        if (!$result) {
            $output = "Database query failed: " . $this->connection->error . "<br/><br/>";
            $output .= "Last SQL Query: " . $this->last_query;
            die($output);
        }
    }

    /**
     * @param $result mysqli_result
     * @return mixed
     */
    public function fetch_array($result) {
        return $result->fetch_assoc();
    }

    public function escape_value($string){
        $escaped_string = $this->connection->real_escape_string($string);
        return $escaped_string;
    }

    /**
     * @param $result mysqli_result
     * @return int
     */
    public function num_rows($result){
        return $result->num_rows;
    }

    public function inserted_id(){
        // get last inserted id over the current db connection
        return $this->connection->insert_id;
    }

    public function affected_rows() {
        return $this->connection->affected_rows;
    }

    public function begin_transaction(){
        $this->connection->begin_transaction();
    }

    public function rollback(){
        $this->connection->rollback();
    }

    public function commit(){
        $this->connection->commit();
    }


}

$database = new MySQLDatabase();