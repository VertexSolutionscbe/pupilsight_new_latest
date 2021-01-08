<?php

class DBQuery
{
    private $conn;
    private function connect()
    {
        include 'config.php';

        $this->conn = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);
        if ($this->conn->connect_errno) {
            echo "Failed to connect to MySQL: " . $this->conn->connect_error;
            exit();
        }
    }

    public function select($sq)
    {
        $this->connect();
        $result = $this->conn->query($sq);
        print_r($result);
        $this->conn->close();
    }
}
