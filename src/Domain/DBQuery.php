<?php

namespace Pupilsight\Domain;

class DBQuery
{
    private $conn;
    private function connect()
    {
        include($_SERVER['DOCUMENT_ROOT'] .'/config.php');
        $this->conn = new \MySQLi($databaseServer, $databaseUsername, $databasePassword, $databaseName);
        if ($this->conn->connect_errno) {
            echo "Failed to connect to MySQL: " . $this->conn->connect_error;
            exit();
        }
    }

    public function query($sq)
    {
        $this->connect();
        //$this->conn->query($sq);
        if ($this->conn->query($sq) === TRUE) {
            $this->conn->close();
            return TRUE;
        } else {
            echo $this->conn->error;
        }
        $this->conn->close();
        return FALSE;
    }


    public function insertArray($table, $dt)
    {
        $col = "";
        $val = "";

        foreach ($dt as $key => $value) {
            if ($col) {
                $col .= ",";
                $val .= ",";
            }
            $col .= $key;
            if ($value == "") {
                $val .= "NULL";
            } else {
                $val .= "'" . $value . "'";
            }
        }

        $sq = "insert into " . $table . " (" . $col . ") values(" . $val . ")";
        //echo "\n".$sq;
        return $this->query($sq);
    }


    public function select($sq)
    {
        $this->connect();
        //echo $sq.'<br>';
        //echo $sq;
        $result = $this->conn->query($sq);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $dt[] = $row;
            }
            $this->conn->close();
            return new DataSet($dt);
        }
        $this->conn->close();
        return new DataSet(array());
    }

    public function selectRaw($sq, $isSerialReq = FALSE)
    {
        $this->connect();
        //echo $sq.'<br>';
        $result = $this->conn->query($sq);
        if ($result->num_rows > 0) {
            $cnt = 1;
            while ($row = $result->fetch_assoc()) {
                if ($isSerialReq) {
                    $row["serial_number"] = $cnt;
                    $cnt++;
                }
                $dt[] = $row;
            }
            $this->conn->close();
            return $dt;
        }
        $this->conn->close();
        return array();
    }

    public function select_serial($sq)
    {
        $this->connect();
        $result = $this->conn->query($sq);
        if ($result->num_rows > 0) {
            $cnt = 1;
            while ($row = $result->fetch_assoc()) {
                $row["serial_number"] = $cnt;
                $dt[] = $row;
                $cnt++;
            }
            $this->conn->close();
            return new DataSet($dt);
        }
        $this->conn->close();
        return new DataSet(array());
    }

    public function convertDataset($res)
    {
        return new DataSet($res);
    }

    public function select_merge($sq)
    {
        $this->connect();
        $result = $this->conn->query($sq);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $dt[] = $row;
            }
            $this->conn->close();
            return $dt;
        }
        $this->conn->close();
        return array();
    }

    public function getDataObject($dt)
    {
        return new DataSet($dt);
    }

    //get single row
    public function getColVal($tableName, $colName, $colId, $colIdVal)
    {
        if (!empty($colIdVal)) {
            $db = new DBQuery();
            $sq = "select " . $colName . " from " . $tableName . " where " . $colId . "='" . $colIdVal . "'  ";
            $res = $db->selectRaw($sq);
            if (!empty($res)) {
                return $res[0][$colName];
            }
        }
        return "";
    }
}
