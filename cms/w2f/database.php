<?php
@define('BASE_URL', 'http://pupilsight.test');
@define('BASE_URL_HOME', 'http://pupilsight.test');
class database
{
  static private $connection;

  public static function init()
  {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/pupilsight_new/config.php');
    //self::$connection = mysqli_connect('127.0.0.1', 'root', '');;
    self::$connection = mysqli_connect($databaseServer, $databaseUsername, $databasePassword);
    if (!self::$connection) {
      return false;
    }

    if (!mysqli_select_db(self::$connection, $databaseName)) {
      return false;
    }
    date_default_timezone_set("Asia/Kolkata");
    return true;
  }


  public static function doSelect($query, $options = array())
  {
    $result = array();
    $resource = mysqli_query(self::$connection, $query);
    if ($resource) {
      while ($row = mysqli_fetch_assoc($resource)) {
        $result[] = $row;
      }

      return $result;
    } else {
      echo "<pre>SQL ERROR :" . mysqli_error(self::$connection) . "</pre><pre>$query</pre>";
    }

    return false;
  }

  public static function doSelectOne($query,  $options = array())
  {
    $resource = mysqli_query(self::$connection, $query);
    if ($resource) {
      $row = mysqli_fetch_assoc($resource);
      return $row;
    } else {
      echo "<pre>SQL ERROR :" . mysqli_error(self::$connection) . "</pre><pre>$query</pre>";
    }

    return false;
  }

  public static function doInsert($query,  $options = array())
  {
    $response = mysqli_query(self::$connection, $query);

    if ($response) {
      return mysqli_insert_id(self::$connection);
    } else {
      echo "<pre>SQL ERROR :" . mysqli_error(self::$connection) . "</pre><pre>$query</pre>";
    }

    return $response;
  }

  public static function doUpdate($query,  $options = array())
  {
    $response = mysqli_query(self::$connection, $query);

    if ($response) {
      return $response;
    } else {
      echo "<pre>SQL ERROR :" . mysqli_error(self::$connection) . "</pre><pre>$query</pre>";
    }

    return $response;
  }

  public static function doUpdate1($query,  $options)
  {
    $response = mysqli_query(self::$connection, $query);

    if ($response) {
      return $response;
    } else {
      echo "<pre>SQL ERROR :" . mysqli_error(self::$connection) . "</pre><pre>$query</pre>";
    }

    return $response;
  }

  public static function doDelete($query,  $options = array())
  {
    $response = mysqli_query(self::$connection, $query);

    if ($response) {
      return $response;
    } else {
      echo "<pre>SQL ERROR :" . mysqli_error(self::$connection) . "</pre><pre>$query</pre>";
    }

    return $response;
  }

  public static function doEscape($str)
  {
    $response = mysqli_real_escape_string($str);
    return $response;
  }
}
