<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Contracts\Database;

interface Result
{
    /**
     * Returns the number of rows affected by the last SQL statement. 
     * PDOStatement method. 
     *
     * @return integer
     */
    public function rowCount();

    /**
     * Does the result contain no database rows?
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Does the result contain any database rows?
     *
     * @return bool
     */
    public function isNotEmpty();

    /**
     * Fetches the next row from a result set. 
     * PDOStatement method. 
     *
     * @param integer|null $fetch_style
     * @param integer|null $cursor_orientation
     * @param integer|null $cursor_offset
     * @return mixed
     */
    public function fetch($fetch_style = null, $cursor_orientation = null, $cursor_offset = null);

    /**
     * Returns an array containing all of the result set rows. 
     * PDOStatement method. 
     *
     * @param integer|null        $fetch_style
     * @param integer|string|null $fetch_argument
     * @param array|null          $ctor_args
     * @return array
     */
    public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = null);

    /**
     * Returns a single column from the next row of a result set. 
     * PDOStatement method. 
     *
     * @param integer $column_number
     * @return string
     */
    public function fetchColumn($column_number = 0);
    
    /**
     * Fetches all as an array, grouped by key using the first column in the result set.
     *
     * @return array
     */
    public function fetchGrouped();

    /**
     * Fetches all as an array, grouped by key where the contents 
     *
     * @return array
     */
    public function fetchGroupedUnique();

    /**
     * Fetches all as an associative array of key => value pairs. The query may only have two columns.
     *
     * @return array
     */
    public function fetchKeyPair();

    /**
     * Returns the number of rows affected by the last SQL statement. 
     * PDOStatement method. 
     *
     * @param  integer    $mode
     * @param  mixed|null $params
     * @return boolean
     */
    public function setFetchMode($mode, $params = null);

    /**
     * Fetches all results and returns it as a DataSet object.
     *
     * @return array
     */
    public function toDataSet();
}
