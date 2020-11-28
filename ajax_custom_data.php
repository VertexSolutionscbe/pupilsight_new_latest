<?php
/*
Pupilsight, Flexible & Open School System
*/
include 'pupilsight.php';
$session = $container->get('session');


//Proceed
$type = $_POST['type'];
$val = $_POST['val'];

/* open Description Indicator */
if ($type == 'subjectSortTab') {
    try {
        $pupilsightSchoolYearID = $_POST["pupilsightSchoolYearID"];
        $pupilsightProgramID = $_POST["pupilsightProgramID"];
        $pupilsightYearGroupID = $_POST["pupilsightYearGroupID"];

        $pupilsightSchoolYearID1 = intval($_POST["pupilsightSchoolYearID"]);
        $pupilsightProgramID1 = intval($_POST["pupilsightProgramID"]);
        $pupilsightYearGroupID1 = intval($_POST["pupilsightYearGroupID"]);

        $sub = $_POST["subjects"];
        $len = count($sub);
        $i = 0;
        $squ = "";
        $row = 1;
        while ($i < $len) {
            $squ .= "update subjectToClassCurriculum set pos='" . $row . "' where pupilsightDepartmentID='" . intval($sub[$i]) . "' and pupilsightSchoolYearID='" . $pupilsightSchoolYearID1 . "'; ";
            $squ .= "update assign_core_subjects_toclass set pos='" . $row . "' where pupilsightDepartmentID='" . $sub[$i] . "' and pupilsightProgramID='" . $pupilsightProgramID . "' and pupilsightYearGroupID='" . $pupilsightYearGroupID . "'; ";
            $row++;
            $i++;
        }
        //echo $squ;
        $connection2->query($squ);
        $res["status"] = 1;
    } catch (Exception $ex) {
        $res["status"] = 2;
        $res["message"] = $ex->getMessage();
    }
    if ($res) {
        echo json_encode($res);
    }
} else if ($type == 'getCustomControl') {
    if ($val) {
        $modules = "student";
        //user_manage_edit.php,student_edit.php,student_add.php,staff_manage_add.php,parent_edit.php
        if ($val == "page_edit.php") {
            $modules = "father";
        } else if ($val == "student_edit.php" || $val == "student_add.php") {
            $modules = "student";
        } else if ($val == "staff_manage_add.php") {
            $modules = "staff";
        }

        $sq = "select c.*,m.tabs  from custom_field_modal as m, custom_field as c ";
        $sq .= "where m.table_name = c.table_name and FIND_IN_SET('" . $val . "',page_view) ";
        $sq .= "and c.modules like '%" . $modules . "%'";
        //echo $sq;
        $result = $connection2->query($sq);
        $rs = $result->fetchAll();
        //print_r($rs);
        if (empty($rs)) {

            $sq = "select c.*,m.tabs  from custom_field_modal as m, custom_field as c where m.table_name = c.table_name and FIND_IN_SET('" . $val . "',page_edit) ";
            //echo "\n".$sq;
            $result = $connection2->query($sq);
            $dt["data"] = $result->fetchAll();
            $dt["view"] = false;
        } else {
            $dt["data"] = $rs;
            $dt["view"] = true;
        }

        if ($dt) {
            echo json_encode($dt);
        }
    }
} else if ($type == 'hideCustomControl') {
    if ($val) {
        $res = array();
        try {
            $fields = $_POST["fields"];
            $len = count($fields);
            $i = 0;
            while ($i < $len) {
                $sqs = "select id from custom_field where field_name='" . $fields[$i] . "' and table_name='" . $val . "'";
                $result = $connection2->query($sqs);
                $row = $result->fetchAll();
                if (isset($row[0]["id"])) {
                    $squ = "update custom_field set active='N' where id='" . $row[0]["id"] . "' ";
                    $connection2->query($squ);
                } else {
                    $sq = "insert into custom_field(field_name, table_name, active) values ";
                    $sq .= "('" . $fields[$i] . "','" . $val . "','N');";
                    $connection2->query($sq);
                }
                $i++;
            }
            $res["status"] = 1;
        } catch (Exception $ex) {
            $res["status"] = 2;
            $res["message"] = $ex->getMessage();
        }
        if ($result) {
            echo json_encode($res);
        }
    }
    /*
    if($val){
        $result = array();
        try{
            $sq = "select id from custom_field where field_name='".$fields[$i]."' and table_name='".$val."'";
            $sq = "insert into custom_field(field_name, table_name, active) values ";
            $fields = $_POST["fields"];
            $len = count($fields);
            $i = 0;
            $squ = "";
            while($i<$len){
                if($squ){
                    $squ .=",";
                }
                $squ .="('".$fields[$i]."','".$val."','N')";
                $i++;
            }
            $sq .=$squ.";";
            $connection2->query($sq);
            $result["status"] = 1;
        }catch(Exception $ex){
            $result["status"] = 2;
            $result["message"] = $ex->getMessage();
        }
        if($result){
            echo json_encode($result);
        }
        
    }*/
} else if ($type == 'showCustomControl') {
    if ($val) {
        $res = array();
        try {
            $fields = $_POST["fields"];
            $len = count($fields);
            $i = 0;
            while ($i < $len) {
                $sqs = "select id from custom_field where field_name='" . $fields[$i] . "' and table_name='" . $val . "'";
                $result = $connection2->query($sqs);
                $row = $result->fetchAll();
                if (isset($row[0]["id"])) {
                    $squ = "update custom_field set active='Y' where id='" . $row[0]["id"] . "' ";
                    $connection2->query($squ);
                }
                $i++;
            }
            $res["status"] = 1;
        } catch (Exception $ex) {
            $res["status"] = 2;
            $res["message"] = $ex->getMessage();
        }
        if ($result) {
            echo json_encode($res);
        }
    }
} else if ($type == 'switchCustomControlTab') {
    if ($val) {
        $result = array();
        try {
            $fieldid = $_POST['fieldid'];
            $sq = "update custom_field set tab='" . $val . "' where id='" . $fieldid . "' ";
            $connection2->query($sq);
            $result["status"] = 1;
        } catch (Exception $ex) {
            $result["status"] = 2;
            $result["message"] = $ex->getMessage();
        }
        if ($result) {
            echo json_encode($result);
        }
    }
} else if ($type == 'sortTab') {
    if ($val) {
        $result = array();
        try {
            $tabs = implode(",", $_POST['tabs']);
            $sq = "update custom_field_modal set tabs='" . $tabs . "' where table_name='" . $val . "' ";
            $connection2->query($sq);
            $result["status"] = 1;
        } catch (Exception $ex) {
            $result["status"] = 2;
            $result["message"] = $ex->getMessage();
        }
        if ($result) {
            echo json_encode($result);
        }
    }
} else if ($type == 'deleteField') {
    if ($val) {
        $result = array();
        try {
            $id = $_POST['id'];
            $table_name = $_POST['table_name'];
            if ($id && $table_name) {
                $sq = "delete from custom_field  where id='" . $id . "'; ";
                $connection2->query($sq);

                $sq = "ALTER TABLE " . $table_name . " DROP " . $val . "; ";
                $connection2->query($sq);
            }
            $result["status"] = 1;
        } catch (Exception $ex) {
            $result["status"] = 2;
            $result["message"] = $ex->getMessage();
        }
        if ($result) {
            echo json_encode($result);
        }
    }
}
