<?php
/*
Pupilsight, Flexible & Open School System
 */

namespace Pupilsight\Database;

use PDO;
use Pupilsight\Database\Result;
use Pupilsight\Database\Connection;

/**
 * Establish a Database Connection.
 *
 * @version	v16
 * @since	v12
 */
class MySqlConnector
{
    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return PDO
     */
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);

        try {
            $connection = new PDO($dsn, $config['databaseUsername'], $config['databasePassword']);

            $this->configureEncoding($connection);
            $this->setModes($connection);

            return new Connection($connection, $config);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function useDatabase(Connection $connection, $databaseName)
    {
        $databaseName = "`" . str_replace("`", "``", $databaseName) . "`";
        
        $querySuccess = $connection->statement("CREATE DATABASE IF NOT EXISTS {$databaseName} DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci");

        if ($querySuccess) {
            $connection->statement("USE {$databaseName}");
        }
    }

    /**
     * Get the DSN string for a host / port configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        extract($config, EXTR_SKIP);

        $dsn = "mysql:host={$databaseServer};";
        $dsn .= !empty($databasePort)? "port={$databasePort};" : '';
        $dsn .= !empty($databaseName)? "dbname={$databaseName};" : '';
        $dsn .= "charset=utf8";

        return $dsn;
    }

    protected function configureEncoding(PDO $connection)
    {
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $connection->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array(Result::class));
    }

    /**
     * Set the modes for the connection.
     * @param PDO $connection
     */
    protected function setModes(PDO $connection)
    {
        $version = $connection->getAttribute(PDO::ATTR_SERVER_VERSION);

        if (version_compare($version, '5.7') >= 0) {
            $mode ="SET SESSION `sql_mode` = ''"; // Default for 5.7.x is STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_AUTO_CREATE_USER
        } elseif (version_compare($version, '5.6') >= 0)  {
            $mode = "SET SESSION `sql_mode` = ''"; // Default for 5.6.x is NO_ENGINE_SUBSTITUTION
        } else {
            $mode = "SET SESSION `sql_mode` = ''"; // Default for < 5.6 is ''
        }

        $connection->prepare($mode)->execute();
    }
}
