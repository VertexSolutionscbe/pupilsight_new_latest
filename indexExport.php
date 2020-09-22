<?php
/*
Pupilsight, Flexible & Open School System
*/

include './pupilsight.php';

$pupilsightRollGroupID = $_GET['pupilsightRollGroupID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php';

try {
    $data = array('pupilsightPersonIDTutor' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor3' => $_SESSION[$guid]['pupilsightPersonID']);
    $sql = 'SELECT * FROM pupilsightRollGroup WHERE (pupilsightPersonIDTutor=:pupilsightPersonIDTutor OR pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2 OR pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3)';
    $result = $connection2->prepare($sql);
    $result->execute($data);
} catch (PDOException $e) {
    $URL .= '?return=error0';
    header("Location: {$URL}");
}

if ($result) {
    if ($pupilsightRollGroupID == '') {
        $URL .= '?return=error1';
        header("Location: {$URL}");
    } else {
        if ($result->rowCount() < 1) {
            $URL .= '?return=error3';
            header("Location: {$URL}");
        } else {
            //Proceed!
            $data = ['pupilsightRollGroupID' => $pupilsightRollGroupID, 'today' => date('Y-m-d')];
            $sql = "SELECT surname, preferredName, email 
                    FROM pupilsightStudentEnrolment 
                    JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID 
                    WHERE pupilsightRollGroupID=:pupilsightRollGroupID AND status='Full' 
                    AND (dateStart IS NULL OR dateStart<=:today) 
                    AND (dateEnd IS NULL  OR dateEnd>=:today) 
                    ORDER BY surname, preferredName";

            $result = $pdo->select($sql, $data);

            $exp = new Pupilsight\Excel();
            $exp->exportWithQuery($result, 'classList.xls');
        }
    }
}
