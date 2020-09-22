<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('Parent Weekly Email Summary'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/report_parentWeeklyEmailSummaryConfirmation.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<p>';
    echo __('This report shows responses to the weekly summary email, organised by calendar week and role group.');
    echo '</p>';

    echo '<h2>';
    echo __('Choose Roll Group & Week');
    echo '</h2>';

    $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])? $_GET['pupilsightRollGroupID'] : null;
    $weekOfYear = isset($_GET['weekOfYear'])? $_GET['weekOfYear'] : null;

    $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/report_parentWeeklyEmailSummaryConfirmation.php');

    $row = $form->addRow();
        $row->addLabel('pupilsightRollGroupID', __('Roll Group'));
        $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'])->required()->selected($pupilsightRollGroupID);

    $begin = new DateTime($_SESSION[$guid]['pupilsightSchoolYearFirstDay']);
    $end = new DateTime();
    $dateRange = new DatePeriod($begin, new DateInterval('P1W'), $end);

    $weeks = array();
    foreach ($dateRange as $date) {
        $weeks[$date->format('W')] = __('Week').' '.$date->format('W').': '.$date->format($_SESSION[$guid]['i18n']['dateFormatPHP']);
    }
    $weeks = array_reverse($weeks, true);

    $row = $form->addRow();
        $row->addLabel('weekOfYear', __('Calendar Week'));
        $row->addSelect('weekOfYear')->fromArray($weeks)->selected($weekOfYear);

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session, __('Clear Filters'));

    echo $form->getOutput();

    if ($pupilsightRollGroupID != '') {
        echo '<h2>';
        echo __('Report Data');
        echo '</h2>';

        try {
            $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID);
            $sql = "SELECT student.surname AS studentSurname, student.preferredName AS studentPreferredName, parent.surname AS parentSurname, parent.preferredName AS parentPreferredName, parent.title AS parentTitle, pupilsightRollGroup.name, student.pupilsightPersonID AS pupilsightPersonIDStudent, parent.pupilsightPersonID AS pupilsightPersonIDParent FROM pupilsightPerson AS student JOIN pupilsightStudentEnrolment ON (student.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) LEFT JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=student.pupilsightPersonID) LEFT JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) LEFT JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) LEFT JOIN pupilsightPerson AS parent ON (pupilsightFamilyAdult.pupilsightPersonID=parent.pupilsightPersonID) WHERE (pupilsightFamilyAdult.contactPriority=1 OR pupilsightFamilyAdult.contactPriority IS NULL) AND student.status='Full' AND parent.status='Full' AND (student.dateStart IS NULL OR student.dateStart<='".date('Y-m-d')."') AND (student.dateEnd IS NULL OR student.dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightRollGroupID=:pupilsightRollGroupID ORDER BY student.surname, student.preferredName, parent.surname, parent.preferredName";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        echo "<table class='table'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Student');
        echo '</th>';
        echo '<th>';
        echo __('Parent');
        echo '</th>';
        echo '<th>';
        echo __('Sent');
        echo '</th>';
        echo '<th>';
        echo __('Confirmed');
        echo '</th>';
        echo '</tr>';

        $count = 0;
        $rowNum = 'odd';
        while ($row = $result->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            ++$count;

            //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo "<a href='index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=".$row['pupilsightPersonIDStudent']."&subpage=Homework'>".formatName('', $row['studentPreferredName'], $row['studentSurname'], 'Student', true).'</a>';
            echo '</td>';
            echo '<td>';
            echo formatName($row['parentTitle'], $row['parentPreferredName'], $row['parentSurname'], 'Parent', true);
            echo '</td>';
            echo "<td style='width:15%'>";
            try {
                $dataData = array('pupilsightPersonIDStudent' => $row['pupilsightPersonIDStudent'], 'pupilsightPersonIDParent' => $row['pupilsightPersonIDParent'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'weekOfYear' => $weekOfYear);
                $sqlData = 'SELECT * FROM pupilsightPlannerParentWeeklyEmailSummary WHERE pupilsightPersonIDStudent=:pupilsightPersonIDStudent AND pupilsightPersonIDParent=:pupilsightPersonIDParent AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND weekOfYear=:weekOfYear';
                $resultData = $connection2->prepare($sqlData);
                $resultData->execute($dataData);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultData->rowCount() == 1) {
                $rowData = $resultData->fetch();
                echo "<img title='".__('Sent')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/> ";
            } else {
                $rowData = null;
                echo "<img title='".__('Not Sent')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/> ";
            }
            echo '</td>';
            echo "<td style='width:15%'>";
            if (is_null($rowData)) {
                echo __('NA');
            } else {
                if ($rowData['confirmed'] == 'Y') {
                    echo "<img title='".__('Confirmed')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/> ";
                } else {
                    echo "<img title='".__('Not Confirmed')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/> ";
                }
            }
            echo '</td>';
            echo '</tr>';
        }
        if ($count == 0) {
            echo "<tr class=$rowNum>";
            echo '<td colspan=4>';
            echo __('There are no records to display.');
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
?>
