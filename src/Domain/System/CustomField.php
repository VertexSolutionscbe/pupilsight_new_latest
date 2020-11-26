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
            $dt['field_title'] = addslashes($dt['field_title']); //addslashes
            $flag = $db->insertArray('custom_field', $dt);
            if ($flag) {
                $colType = "TEXT NULL ";
                $default_value = "NULL ";
                if ($dt["field_type"] == "varchar" || $dt["field_type"] == "email" || $dt["field_type"] == "number" || $dt["field_type"] == "image" || $dt["field_type"] == "file") {
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
                    /*if ($ft == "image") {
                        //handle image 
                    } else {*/
                    foreach ($fields as $key => $val) {
                        if ($squ) {
                            $squ .= ", ";
                        }
                        $squ .= $key . "='" . $val . "'";
                    }
                    //}
                }
            }
            //print_r($squ);
            if ($squ) {
                $sq = "update " . $tbl . " set " . $squ . " where " . $colName . "='" . $colVal . "'";
                //echo $sq;
                //die();
                $db = new DBQuery();
                $db->query($sq);
            }

            //manage file upload in custom field
            $this->fileCustomField($colName, $colVal);
        } catch (Exception $ex) {
            echo 'CustomField->updateCustomField(): exception: ',  $ex->getMessage(), "\n";
        }
    }

    //file custom field upload handle here
    public function fileCustomField($colName, $colVal)
    {
        try {
            $sq = "";
            try {
                if ($_FILES["custom"]) {
                    foreach ($_FILES["custom"]["name"] as $table => $files) {
                        //file
                        foreach ($files["file"]  as $fieldName => $fileName) {

                            if ($_FILES['custom']['tmp_name'][$table]["file"][$fieldName]) {
                                $id = time() . mt_rand(10, 99);
                                $file_name = $id . "_" . $_FILES['custom']['name'][$table]["file"][$fieldName];
                                $file_tmp = $_FILES['custom']['tmp_name'][$table]["file"][$fieldName];
                                $fileSavePath = $_SERVER["DOCUMENT_ROOT"] . "/public/custom_files/" . $file_name;
                                move_uploaded_file($file_tmp, $fileSavePath);
                                try {
                                    $sq = "update " . $table . " set " . $fieldName . "='" . "/public/custom_files/" . $file_name . "' where " . $colName . "='" . $colVal . "'";
                                    $db = new DBQuery();
                                    $db->query($sq);
                                } catch (Exception $ex) {
                                    print_r($ex);
                                }
                            }
                        }

                        //image
                        foreach ($files["image"]  as $fieldName => $fileName) {

                            if ($_FILES['custom']['tmp_name'][$table]["image"][$fieldName]) {
                                $id = time() . mt_rand(10, 99);
                                $file_name = $id . "_" . $_FILES['custom']['name'][$table]["image"][$fieldName];
                                $file_tmp = $_FILES['custom']['tmp_name'][$table]["image"][$fieldName];
                                $fileSavePath = $_SERVER["DOCUMENT_ROOT"] . "/public/custom_images/" . $file_name;
                                move_uploaded_file($file_tmp, $fileSavePath);
                                try {
                                    $sq = "update " . $table . " set " . $fieldName . "='" . "/public/custom_images/" . $file_name . "' where " . $colName . "='" . $colVal . "'";
                                    $db = new DBQuery();
                                    $db->query($sq);
                                } catch (Exception $ex) {
                                    print_r($ex);
                                }
                            }
                        }
                    }
                }
            } catch (Exception $ex) {
                print_r($ex);
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
            $sq = "select group_concat(field_name) as fields from custom_field where table_name='" . $tableName . "' and active='Y' and field_type in('varchar','text','email','number','mobile','image','date','file','url','dropdown','checkboxes','radioboxes','tab')";
            $db = new DBQuery();
            $res = $db->selectRaw($sq);
            if ($res) {
                $sq = "select " . $res[0]["fields"] . " from " . $tableName . " where " . $primaryCol . "='" . $primaryColVal . "' ";
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

    public function isColumnAvailable($tableName, $colName)
    {
        try {
            $sq = "SHOW COLUMNS FROM " . $tableName . " LIKE '" . $colName . "'";
            $db = new DBQuery();
            $res = $db->selectRaw($sq);
            if ($res) {
                return TRUE;
            }
        } catch (Exception $ex) {
            print_r($ex);
        }
        return FALSE;
    }

    public function removeUnusedColumn($id)
    {
        try {
            $db = new DBQuery();
            $sq = "delete from custom_field where id='" . $id . "'";
            $db->query($sq);
        } catch (Exception $ex) {
            print_r($ex);
        }
    }

    public function getCustomFieldList()
    {
        $db = new DBQuery();
        $result = array();
        try {
            $sq = "select c.id, c.field_name, c.field_title, c.field_type, c.modules, c.tab, c.active, c.table_name, cm.tabs, cm.table_tag, cm.page_view, cm.page_edit from custom_field as c left join custom_field_modal as cm on c.table_name = cm.table_name";
            $result = $db->selectRaw($sq);
        } catch (Exception $ex) {
            echo 'CustomField->getCustomFieldList(): exception: ',  $ex->getMessage(), "\n";
        }
        return $result;
    }

    public function isActiveField($customlist, $fieldName)
    {
        $flag = TRUE;
        $len = count($customlist);
        $i = 0;
        while ($i < $len) {
            if ($customlist[$i]["field_name"] == $fieldName) {
                if ($customlist[$i]["active"] == "N") {
                    $flag = FALSE;
                }
                break;
            }
            $i++;
        }
        return $flag;
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

    public function isNotTabField($customlist, $fieldName)
    {
        $flag = TRUE;
        $len = count($customlist);
        $i = 0;
        while ($i < $len) {
            if ($customlist[$i]["field_name"] == $fieldName) {
                if ($customlist[$i]["field_type"] == "tab") {
                    $flag = FALSE;
                }
                break;
            }
            $i++;
        }
        return $flag;
    }
}
