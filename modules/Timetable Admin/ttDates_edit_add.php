<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttDates_edit_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $dateStamp = $_GET['dateStamp'] ?? '';

    if ($pupilsightSchoolYearID == '' or $dateStamp == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        if (isSchoolOpen($guid, date('Y-m-d', $dateStamp), $connection2, true) != true) {
            echo "<div class='alert alert-danger'>";
            echo __('School is not open on the specified day.');
            echo '</div>';
        } else {
            try {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified record does not exist.');
                echo '</div>';
            } else {
                $values = $result->fetch();

                //Proceed!
                $page->breadcrumbs
                    ->add(__('Tie Days to Dates'), 'ttDates.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
                    ->add(__('Edit Days in Date'), 'ttDates_edit.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'dateStamp' => $dateStamp])
                    ->add(__('Add Day to Date'));

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

				$form = Form::create('addTTDate', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/ttDates_edit_addProcess.php');

				$form->addHiddenValue('address', $_SESSION[$guid]['address']);
				$form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
				$form->addHiddenValue('dateStamp', $dateStamp);

				$row = $form->addRow();
					$row->addLabel('schoolYearName', __('School Year'));
					$row->addTextField('schoolYearName')->readonly()->setValue($values['name']);

				$row = $form->addRow();
                    $row->addLabel('dateName', __('Date'));
					$row->addTextField('dateName')->readonly()->setValue(date('d/m/Y l', $dateStamp));

				$data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'date' => date('Y-m-d', $dateStamp));
				$sql = "SELECT pupilsightTTDay.pupilsightTTDayID as value, CONCAT(pupilsightTT.name, ': ', pupilsightTTDay.nameShort) as name
						FROM pupilsightTT
						JOIN pupilsightTTDay ON (pupilsightTTDay.pupilsightTTID=pupilsightTT.pupilsightTTID)
						LEFT JOIN (SELECT pupilsightTTDay.pupilsightTTID, pupilsightTTDayDate.date
                        	FROM pupilsightTTDay
                        	JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID)
                        ) AS dateCheck ON (dateCheck.pupilsightTTID=pupilsightTT.pupilsightTTID AND dateCheck.date=:date)
						WHERE pupilsightTT.pupilsightSchoolYearID=:pupilsightSchoolYearID
						AND dateCheck.pupilsightTTID IS NULL
						ORDER BY name";

				$row = $form->addRow();
                    $row->addLabel('pupilsightTTDayID', __('Day'));
                    $row->addSelect('pupilsightTTDayID')->fromQuery($pdo, $sql, $data)->required()->placeholder();

				$row = $form->addRow();
					$row->addFooter();
					$row->addSubmit();

				echo $form->getOutput();
            }
        }
    }
}
