<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$order = (isset($_POST['order']))? $_POST['order'] : '';

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit.php') == false) {

    echo __('Your request failed because you do not have access to this action.');

} else {

    if ($order != '') {

        $columnOrder = array_slice($order, 1 );
        $minSequence = (isset($_POST['sequence']))? $_POST['sequence'] : 0;

        for ($i = 0; $i < count($columnOrder); $i++) {

            // Re-order the sequenceNumber based off the new column order, using the minimum value to preserve pagination / filters
            try {
                $data = array('pupilsightMarkbookColumnID' => $columnOrder[$i], 'sequenceNumber' => $i + $minSequence );
                $sql = 'UPDATE pupilsightMarkbookColumn SET sequenceNumber=:sequenceNumber WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                print __('Your request failed due to a database error.');
            }

        }

    }
}
