<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\TimetableGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Timetables'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    if ($pupilsightSchoolYearID != $_SESSION[$guid]['pupilsightSchoolYearID']) {
        try {
            $data = array('pupilsightSchoolYearID' => $_GET['pupilsightSchoolYearID']);
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
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    }

    if ($pupilsightSchoolYearID != '') {
        echo '<h2>';
        echo $pupilsightSchoolYearName;
        echo '</h2>';

        echo "<div class='linkTop'>";
            //Print year picker
            if (getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/tt.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
			echo ' | ';
			if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
				echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/tt.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
			} else {
				echo __('Next Year').' ';
			}
        echo '</div>';


        $timetableGateway = $container->get(TimetableGateway::class);
        $timetables = $timetableGateway->selectTimetablesBySchoolYear($pupilsightSchoolYearID);

        // DATA TABLE
        $table = DataTable::create('timetables');

        // $table->addHeaderAction('add', __('Add'))
        //     ->setURL('/modules/Timetable Admin/tt_add.php')
        //     ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
        //     ->displayLabel();
        // $table->addHeaderAction('copy', __('Copy'))
        //     ->setURL('/modules/Academics/ac_manage_skill_edit.php')
        //     ->setClass('btn btn-primary')
        //     ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
        //     ->displayLabel();
        echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Timetable Admin/tt_add.php&pupilsightSchoolYearID=".$pupilsightSchoolYearID."' class='btn btn-primary'>Add</a>&nbsp;&nbsp;";  
   
        echo "<a style='display:none' id='showcopytt' href='fullscreen.php?q=/modules/Timetable Admin/tt_copy.php&width=800'  class='thickbox '></a>";   
        
        echo "<a  id='copytt' data-hrf='fullscreen.php?q=/modules/Timetable Admin/tt_copy.php&pupilsightSchoolYearID=".$pupilsightSchoolYearID."' class='btn btn-primary'>Copy</a>&nbsp;&nbsp;";    
        echo  "</div><div class='float-none'></div></div>";
          
            
            
        $table->modifyRows(function ($tt, $row) {
            if ($tt['active'] == 'N') $row->addClass('error');
            return $row;
        });
        $table->addCheckboxColumn('pupilsightTTID',__(''))
        ->setClass('chkbox')
            ->context('Select');
        $table->addColumn('name', __('Name'));
        $table->addColumn('nameShort', __('Short Name'));
        $table->addColumn('yearGroups', __('Class'));
        $table->addColumn('sections', __('Section'));
        $table->addColumn('active', __('Active'))->format(Format::using('yesNo', 'active'));

        // ACTIONS
        $table->addActionColumn()
            ->addParam('pupilsightTTID')
            ->addParam('pupilsightSchoolYearID')
            ->format(function ($person, $actions) {
                $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Timetable Admin/tt_edit.php');

                $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Timetable Admin/tt_delete.php');

                $actions->addAction('import', __('Import'))
                    ->setIcon('upload')
                    ->setURL('/modules/Timetable Admin/tt_import.php');
            });

        echo $table->render($timetables->toDataSet());
    }
}
?>
<script>
      $(document).on('click', '#copytt', function() {
        if ($("input[name='pupilsightTTID[]']").is(':checked')) {
            var checked = $("input[name='pupilsightTTID[]']:checked").length;
            if (checked > 1) {
                alert("Please Select One name!");
                return false;
            } else {
                var hrf = $(this).attr('data-hrf');
                var id = $("input[name='pupilsightTTID[]']:checked").val();
                if (id != '') {                  
                    var newhrf = hrf + '&tid=' + id;
                    $("#showcopytt").attr('href', newhrf);
                    $("#showcopytt").click();
                } else {
                    alert("Please Select name!");
                }
            }
        } else {
            alert("Please Select name!");
        }
    });

</script>