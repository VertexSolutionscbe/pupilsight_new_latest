<?php
/*
Pupilsight, Flexible & Open School System
*/
include $_SERVER["DOCUMENT_ROOT"] . '/db.php';

$test1 = getTestData($conn, 184);
$test2 = getTestData($conn, 178);
/*
echo "\n<br>";
print_r($test1);
echo "\n<br>";
print_r($test2);
*/
$conn->close();
$dt = array();
$dt[0] = $test1;
$dt[1] = $test2;
$fdt = run($dt);
print_r($fdt);

function getTestData($conn, $testid, $pupilsightPersonID = NULL)
{
    $dt = array();
    try {
        $sql = "select id, pupilsightPersonID,
        group_concat(pupilsightDepartmentID) as subject, 
        group_concat(marks_obtained) as marks, 
        group_concat(marks_abex) as marks_abex,
        group_concat(skill_id) as skill,
        group_concat(gradeId) as grade_id
        from  `examinationMarksEntrybySubject` WHERE test_id = " . $testid . " ";
        if ($pupilsightPersonID) {
            $sql .= " and pupilsightPersonID='" . $pupilsightPersonID . "' ";
        }
        $sql .= " group by pupilsightPersonID order by pupilsightPersonID;";

        if ($result = $conn->query($sql)) {

            while ($col = $result->fetch_row()) {
                //printf("%s (%s)\n", $row[0], $row[1]);
                //print_r($row);

                $id = $col[0];
                $pupilsightPersonID = $col[1];
                $subject = $col[2];
                $marks = $col[3];
                $marks_abex = $col[4];
                $skill = $col[5];
                $grade_id = $col[6];

                $sub = explode(",", $subject);
                $mks = explode(",", $marks);
                $mksab = explode(",", $marks_abex);
                $sl = explode(",", $skill);
                $grd = explode(",", $grade_id);
                $len = count($sub);
                $i = 0;
                while ($i < $len) {
                    //$tmp[$sub[$i]]=;
                    $tmp = array();
                    $tmp["subject"] = $sub[$i];
                    $tmp["marks"] = $mks[$i];
                    $tmp["marks_abex"] = $mksab[$i];
                    $tmp["skill"] = $sl[$i];
                    $tmp["grade_id"] = $grd[$i];
                    $dt[$testid][$pupilsightPersonID][$sub[$i]] = $tmp;
                    $i++;
                }
                /*if ($dt) {
                    echo json_encode($dt);
                }*/
                //print_r($dt);
            }
            $result->free_result();
        }
        // $conn->close();
    } catch (Exception $ex) {
        print_r($ex);
    }
    return $dt;
}

function run($dt, $action)
{
    if ($dt) {
        $len = count($dt[0]["184"]);
        $i = 0;
        while ($i < $len) {
            $ex1 = $dt[0]["184"];
            $ex2 = $dt[1]["178"];
            //print_r($ex1);
            foreach ($ex1 as $stid => $cols) {
                //print_r($stid);
                foreach ($cols as $sub => $marks) {
                    //print_r($sub);
                    //print_r($marks);
                    $mks = array();
                    $mks[0] = $ex1[$stid][$sub]["marks"];
                    if ($ex2[$stid][$sub]["marks"]) {
                        $mks[1] = $ex2[$stid][$sub]["marks"];
                    }
                    //print_r($ex1[$stid][$sub]["marks"]);
                    $fm = sum($mks);
                    //$ex1[$stid][$sub]["sum"] = $fm;
                    $dt[0]["184"][$stid][$sub]["sum"] = $fm;
                    $dt[1]["178"][$stid][$sub]["sum"] = $fm;
                }
            }
            $i++;
        }
    }
    return $dt;
}

function sum($marks)
{
    $len = count($marks);
    $i = 0;
    $fm = 0;
    while ($i < $len) {
        $fm += $marks[$i];
        $i++;
    }
    return $fm;
}
