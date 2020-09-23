<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$_SESSION[$guid]['report_student_emergencySummary.php_choices'] = '';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('Student Borrowing Record'));

if (isActionAccessible($guid, $connection2, '/modules/Library/report_studentBorrowingRecord.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<h2>';
    echo __('Choose Student');
    echo '</h2>';

    $pupilsightPersonID = null;
    if (isset($_GET['pupilsightPersonID'])) {
        $pupilsightPersonID = $_GET['pupilsightPersonID'];
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_studentBorrowingRecord.php");

    $row = $form->addRow();
        $row->addLabel('pupilsightPersonID', __('Student'));
        $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightPersonID)->placeholder()->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if ($pupilsightPersonID != '') {
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        $output = getBorrowingRecord($guid, $connection2, $pupilsightPersonID);
        if ($output == false) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            echo $output;
        }
    }
}
?>
