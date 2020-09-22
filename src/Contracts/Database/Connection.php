<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Contracts\Database;

/**
 * Database Connection Interface 
 * Borrowed in part from Illuminate\Database\ConnectionInterface
 *
 * @version	v16
 * @since	v16
 */
interface Connection
{
    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getConnection();

    /**
     * Run a select statement and return a single result.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return mixed
     */
    public function selectOne($query, $bindings = []);

    /**
     * Run a select statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return array
     */
    public function select($query, $bindings = []);

    /**
     * Run an insert statement and return the last insert ID.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function insert($query, $bindings = []);

    /**
     * Run an update statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function update($query, $bindings = []);

    /**
     * Run a delete statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function delete($query, $bindings = []);

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public function statement($query, $bindings = []);

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = []);

    // /**
    //  * Start a new database transaction.
    //  *
    //  * @return void
    //  */
    // public function beginTransaction();

    // /**
    //  * Commit the active database transaction.
    //  *
    //  * @return void
    //  */
    // public function commit();

    // /**
    //  * Rollback the active database transaction.
    //  *
    //  * @return void
    //  */
    // public function rollBack();

    // /**
    //  * Get the number of active transactions.
    //  *
    //  * @return int
    //  */
    // public function transactionLevel();

    /**
     * @deprecated
     * Backwards compatability for the old Pupilsight\sqlConnection class. 
     * Replaced with more expressive method names. Also because the 
     * parameters are backwards. Hoping to phase this one out in v17.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public function executeQuery($bindings = [], $query);
}