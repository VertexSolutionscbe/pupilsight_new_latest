<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Database;

use PDO;
use PDOStatement;
use Pupilsight\Domain\DataSet;
use Pupilsight\Domain\DataSetPublic;
use Pupilsight\Contracts\Database\Result as ResultContract;

/**
 * Methods to improve the intent and readability of database code.
 */
class Result extends PDOStatement implements ResultContract
{
    /**
     * Does the result contain no database rows?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->rowCount() == 0;
    }

    /**
     * Does the result contain any database rows?
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return $this->rowCount() > 0;
    }

    /**
     * Fetches all as an array, grouped by key using the first column in the result set.
     *
     * @return array
     */
    public function fetchGrouped()
    {
        return $this->isNotEmpty()? $this->fetchAll(PDO::FETCH_GROUP) : array();
    }

    /**
     * Fetches all as an array, grouped by key where the contents 
     *
     * @return array
     */
    public function fetchGroupedUnique()
    {
        return $this->isNotEmpty()? $this->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE) : array();
    }

    /**
     * Fetches all as an associative array of key => value pairs. The query may only have two columns.
     *
     * @return array
     */
    public function fetchKeyPair()
    {
        return $this->isNotEmpty()? $this->fetchAll(PDO::FETCH_KEY_PAIR) : array();
    }

    /**
     * Fetches all results and returns it as a DataSet object.
     *
     * @return array
     */
    public function toDataSet()
    {
        return new DataSet($this->fetchAll());
    }

    public function toDataSetPublic()
    {
        return new DataSetPublic($this->fetchAll());
    }
}
