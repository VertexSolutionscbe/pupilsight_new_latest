<?php
include './pupilsight.php';

$type = substr($_GET['fastFinderSearch'], 0, 3);
$id = substr($_GET['fastFinderSearch'], 4);

if ($_SESSION[$guid]['absoluteURL'] == '') {
    $URL = './index.php';
} else {
    if ($type == 'Stu') {
        $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$id;
    } elseif ($type == 'Act') {
        $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$id;
    } elseif ($type == 'Sta') {
        $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/staff_view_details.php&pupilsightPersonID='.$id;
    } elseif ($type == 'Cla') {
        $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Departments/department_course_class.php&pupilsightCourseClassID='.$id;
    }
}

header("Location: {$URL}");
