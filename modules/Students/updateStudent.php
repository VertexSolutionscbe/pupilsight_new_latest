<?php


/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
//echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>';

use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Admission\AdmissionGateway;
use Pupilsight\Domain\Helper\HelperGateway;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Students/updateStudent.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {

    $page->breadcrumbs->add(__('Student Id Generation'));


    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program = $program1 + $program2;

    $sqls = 'SELECT id, series_name FROM fn_fee_series WHERE type = "Admission" ';
    $results = $connection2->query($sqls);
    $seriesData = $results->fetchAll();

    $series = array();
    $series2 = array();
    $series1 = array('' => 'Select Series');
    foreach ($seriesData as $dt) {
        $series2[$dt['id']] = $dt['series_name'];
    }
    $series = $series1 + $series2;

    $classes = '';
    $endDate = '';
    $stDate = '';
    $enDate = '';

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    $HelperGateway = $container->get(HelperGateway::class);
    if ($_POST) {
        $series_id = $_POST['series_id'];
        $pupilsightProgramID = $_POST['pupilsightProgramID'];
        //$pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
        $type = $_POST['type'];

        //$classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);

        if (!empty($pupilsightProgramID) && !empty($type)) {
            $sqle = "SELECT a.officialName, a.pupilsightPersonID, a.admission_no, d.name AS class,c.name as program ,f.name as academic, d.pupilsightYearGroupID,c.pupilsightProgramID ,f.pupilsightSchoolYearID FROM pupilsightPerson AS a 
        LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID=b.pupilsightPersonID 
        LEFT JOIN pupilsightProgram AS c ON b.pupilsightProgramID=c.pupilsightProgramID 
        LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID=d.pupilsightYearGroupID 
        LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID=e.pupilsightRollGroupID 
        LEFT JOIN pupilsightSchoolYear AS f ON b.pupilsightSchoolYearID=f.pupilsightSchoolYearID 
        
        WHERE a.pupilsightRoleIDPrimary = '003' AND a.admission_no IS NULL AND b.pupilsightProgramID = " . $pupilsightProgramID . " AND b.pupilsightSchoolYearID = " . $pupilsightSchoolYearID . " ORDER BY d.pupilsightYearGroupID " . $type . " ";
            //echo $sqle;
            // die();
            $resulte = $connection2->query($sqle);
            $studentData = $resulte->fetchAll();
        }
    }

    // echo '<pre>';
    // print_r($studentData);
    // echo '</pre>';
    //die();

    echo '<h2>';
    echo __('Student  Id Generation');
    echo '</h2>';

    $types = array('' => 'Select Type', 'ASC' => 'Ascending', 'DESC' => 'Descending');
    $form = Form::create('filter', '');

    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view.php');
    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->placeholder('Select Program')->required();

    // $col = $row->addColumn()->setClass('newdes');
    // $col->addLabel('pupilsightYearGroupID', __('Class'));
    // $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->selected($pupilsightYearGroupID)->placeholder('Select Class')->required();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('series_id', __('Series'));
    $col->addSelect('series_id')->fromArray($series)->selected($series_id)->placeholder('Select Series')->required();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('type', __('Type'))->addClass('dte');
    $col->addSelect('type')->fromArray($types)->selected($type)->required();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));
    $col->addSearchSubmit($pupilsight->session, __('Clear Search'));



    echo $form->getOutput();
    if ($_POST) {
?>
        <form method="post" action="index.php?q=/modules/Students/updateStudentNo.php">
            <button class="btn btn-primary" style="float:right;">Update</button>
            <input type='hidden' name="series_id" value="<?php echo $series_id; ?>">
            <table class="table" id="historyTable">
                <thead>
                    <tr>
                        <th>Sl No</th>
                        <th>Program</th>
                        <th>Class</th>
                        <th>Student Name</th>
                        <th>Admission No</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($studentData)) {
                        $i = 1;
                        foreach ($studentData as $estd) {
                    ?>

                            <input style="display:none;" type='text' name="student_id[]" value="<?php echo $estd['pupilsightPersonID']; ?>">
                            <tr>
                                <th><?php echo $i; ?></th>
                                <th><?php echo $estd['program']; ?></th>
                                <th><?php echo $estd['class']; ?></th>
                                <th><?php echo $estd['officialName']; ?></th>
                                <th><?php echo $estd['admission_no']; ?></th>

                            </tr>
                        <?php $i++;
                        }
                    } else { ?>
                        <tr>
                            <th colspan="7">No Message History</th>
                        </tr>
                    <?php } ?>
                </tbody>

            </table>
        </form>
<?php
    }
}
?>

<script>
    $(function() {
        $("#historyTable").dataTable();
    })
    $("#start_date").datepicker({
        //minDate: 0,
        onClose: function(selectedDate) {
            $("#end_date").datepicker("option", "minDate", selectedDate);
        }
    });
</script>