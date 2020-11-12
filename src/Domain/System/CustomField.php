<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\System;

use Exception;
use phpDocumentor\Reflection\Types\Null_;
use Pupilsight\Domain\Traits\TableAware;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Domain\QueryableGateway;

use Pupilsight\Domain\DBQuery;

/**
 * Setting Gateway
 *
 * @version v17
 * @since   v17
 */
class CustomField extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'CustomField';

    private static $searchableColumns = ['field_name', 'field_label', 'field_type', 'table_name', 'page_name', 'tab_name'];

    public function getAllTables()
    {
        $db = new DBQuery();
        $sq = "show tables";
        return $db->selectRaw($sq);
    }

    public function getAllTableList()
    {
        $tables = $this->getAllTables();
        $keys = array_keys($tables[0]);
        $colname = $keys[0];
        $len = count($tables);
        $i = 0;
        $dt = array();
        while ($i < $len) {
            $kv = $tables[$i][$colname];
            $dt[$kv] = $kv;
            $i++;
        }
        return $dt;
    }

    public function getModelTables()
    {
        $db = new DBQuery();
        $sq = "select id, table_name, table_tag from custom_field_modal order by table_tag asc";
        return $db->selectRaw($sq);
    }

    public function getAllColumn($table)
    {
        $db = new DBQuery();
        $sq = "show columns from $table";
        return $db->selectRaw($sq);
    }

    public function addCustomTab($dt)
    {
        try {
            $db = new DBQuery();
            $dt["table_column_after"] = "";
            $flag = $db->insertArray('custom_field', $dt);
            if ($flag) {
                $sq = "update custom_field_modal set tabs=concat(tabs, '," . $dt["field_name"] . "') where table_name='" . $dt["table_name"] . "'";
                //echo $sq;
                $db->query($sq);
                $flag = TRUE;
            }
        } catch (Exception $e) {
            echo 'CustomField->addCustomFieldToTable(): exception: ',  $e->getMessage(), "\n";
            $flag = FALSE;
        }
        return $flag;
    }

    public function addCustomField($dt)
    {
        $db = new DBQuery();
        $flag = FALSE;
        if ($dt["table_name"]) {
            $flag = $db->insertArray('custom_field', $dt);
            if ($flag) {
                $colType = "TEXT NULL ";
                $default_value = "NULL ";
                if ($dt["field_type"] == "varchar" || $dt["field_type"] == "email" || $dt["field_type"] == "image" || $dt["field_type"] == "file") {
                    $colType = "VARCHAR(255) NULL ";
                } else if ($dt["field_type"] == "mobile") {
                    $colType = "VARCHAR(12) NULL ";
                } else if ($dt["field_type"] == "date") {
                    $colType = "DATE NULL ";
                }
                if ($dt["default_value"]) {
                    $default_value = $dt["default_value"];
                }
                $this->addCustomFieldToTable($dt["table_name"], $dt["field_name"], $colType, $default_value, $dt["table_column_after"], $dt["isunique"]);
                $flag = TRUE;
            }
        }
        return $flag;
    }

    public function addCustomFieldToTable($table, $colName, $colType, $default = NULL, $afterCol = NULL, $isunique)
    {
        try {
            $db = new DBQuery();
            //ALTER TABLE `task` ADD `test` INT NOT NULL AFTER `name`;
            $sq = "ALTER TABLE " . $table . " ADD " . $colName . " " . $colType . " ";

            if ($default) {
                $sq .= " DEFAULT " . $default;
            }

            if ($afterCol) {
                $sq .= " AFTER " . $afterCol;
            }
            //echo "\n".$sq;
            $db->query($sq);

            if ($isunique == "Y") {
                $sq = "alter table " . $table . " ADD UNIQUE `unique_index`(`" . $colName . "`)";
                $db->query($sq);
                //echo "\n".$sq;
            }
        } catch (Exception $e) {
            echo 'CustomField->addCustomFieldToTable(): exception: ',  $e->getMessage(), "\n";
        }
    }

    //loading custom field in form
    public function loadCustomField($table)
    {
        $db = new DBQuery();
        $result = array();
        try {
            $sq = "select * from custom_field where table_name = '" . $table . "' ";
            $result = $db->selectRaw($sq);
        } catch (Exception $ex) {
            echo 'CustomField->loadCustomField(): exception: ',  $ex->getMessage(), "\n";
        }
        return $result;
    }

    public function loadCustomFieldModal($table)
    {
        $db = new DBQuery();
        $result = array();
        try {
            $sq = "select * from custom_field_modal where table_name = '" . $table . "' ";
            $result = $db->selectRaw($sq);
        } catch (Exception $ex) {
            echo 'CustomField->loadCustomFieldModal(): exception: ',  $ex->getMessage(), "\n";
        }
        return $result;
    }

    public function getAllInactiveColumn($table)
    {
        $db = new DBQuery();
        $result = array();
        try {
            $sq = "select field_name from custom_field where table_name = '" . $table . "' and active='N' ";
            $res = $db->selectRaw($sq);
            $len = count($res);
            $i = 0;
            while ($i < $len) {
                $result[$i] = $res[$i]["field_name"];
                $i++;
            }
        } catch (Exception $ex) {
            echo 'CustomField->loadCustomField(): exception: ',  $ex->getMessage(), "\n";
        }
        return $result;
    }

    //only update query
    public function postCustomField($dt, $colName, $colVal)
    {
        try {
            $tbl = "";
            $sq = "";
            $squ = "";
            foreach ($dt as $table => $field_type) {
                //echo $data." - ".$val;
                $tbl = $table;
                foreach ($field_type as $ft => $fields) {
                    //echo $tbl . "=>" . $ft;
                    //print_r($fields);
                    if ($ft == "image") {
                        //handle image 
                    } else {
                        foreach ($fields as $key => $val) {
                            if ($squ) {
                                $squ .= ", ";
                            }
                            $squ .= $key . "='" . $val . "'";
                        }
                    }
                }
            }

            if ($squ) {
                $sq = "update " . $tbl . " set " . $squ . " where " . $colName . "='" . $colVal . "'";
                $db = new DBQuery();
                $db->query($sq);
            }
        } catch (Exception $ex) {
            echo 'CustomField->updateCustomField(): exception: ',  $ex->getMessage(), "\n";
        }
    }

    public function postCustomFieldQuery($dt, $colName, $colVal)
    {
        $sq = "";
        try {
            $tbl = "";
            $squ = "";
            foreach ($dt as $table => $st) {
                //echo $data." - ".$val;
                $tbl = $table;
                foreach ($st as $key => $val) {
                    if ($squ) {
                        $squ .= ", ";
                    }
                    $squ .= $key . "='" . $val . "'";
                }
            }
            if ($squ) {
                $sq = "update " . $tbl . " set " . $squ . " where " . $colName . "='" . $colVal . "'";
            }
        } catch (Exception $ex) {
            echo 'CustomField->updateCustomField(): exception: ',  $ex->getMessage(), "\n";
        }
        return $sq;
    }

    public function getPostData($tableName, $primaryCol, $primaryColVal)
    {
        try {
            $sq = "select group_concat(field_name) as fields from custom_field where table_name='" . $tableName . "' and active='Y' and field_type in('varchar','text','email','mobile','image','date','file','url','dropdown','checkboxes','radioboxes','tab')";
            //echo $sq;
            $db = new DBQuery();
            $res = $db->selectRaw($sq);

            if ($res) {
                $sq = "select " . $res[0]["fields"] . " from " . $tableName . " where " . $primaryCol . "='" . $primaryColVal . "' ";
                //echo $sq;
                $st = $db->selectRaw($sq);
                if ($st) {
                    $result["t"] = $tableName;
                    $result["pc"] = $primaryCol;
                    $result["pcv"] = $primaryColVal;
                    $result["dt"] = $st[0];
                    echo "\n<script>pcdt=" . json_encode($result) . ";</script>";
                } else {
                    echo "\n<script>pcdt=\"\";</script>";
                }
            }
        } catch (Exception $ex) {
            echo 'CustomField->updateCustomField(): exception: ',  $ex->getMessage(), "\n";
        }
    }

    public function getCustomFieldList()
    {
        $db = new DBQuery();
        $result = array();
        try {
            $sq = "select c.id, c.field_name, c.field_title, c.field_type, c.modules, c.tab, c.active, cm.tabs, cm.table_tag, cm.page_view, cm.page_edit from custom_field as c left join custom_field_modal as cm on c.table_name = cm.table_name";
            $result = $db->selectRaw($sq);
        } catch (Exception $ex) {
            echo 'CustomField->getCustomFieldList(): exception: ',  $ex->getMessage(), "\n";
        }
        return $result;
    }

    public function getInputTag($tag)
    {
        if ($tag == "varchar") {
            return "textfield";
        } else if ($tag == "text") {
            return "textarea";
        } else if ($tag == "dropdown") {
            return "dropdown";
        } else if ($tag == "tab") {
            return "tab";
        }
        return $tag;
    }
}
