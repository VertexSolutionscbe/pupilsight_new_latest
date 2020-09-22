<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('View Overdue Items'));

if (isActionAccessible($guid, $connection2, '/modules/Library/report_viewOverdueItems.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<h2>';
    echo __('Filter');
    echo '</h2>';

    $ignoreStatus = '';
    if (isset($_GET['ignoreStatus'])) {
        $ignoreStatus = $_GET['ignoreStatus'];
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_viewOverdueItems.php");

    $row = $form->addRow();
        $row->addLabel('ignoreStatus', __('Ignore Status'))->description(__('Include all users, regardless of status and current enrolment.'));
        $row->addCheckbox('ignoreStatus')->checked($ignoreStatus);

    $row = $form->addRow();
        $row->addFooter(false);
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    echo '<h2>';
    echo __('Report Data');
    echo '</h2>';

    $today = date('Y-m-d');

    try {
        $data = array('today' => $today);
        if ($ignoreStatus == 'on') {
            $sql = "SELECT pupilsightLibraryItem.*, surname, preferredName, email FROM pupilsightLibraryItem JOIN pupilsightPerson ON (pupilsightLibraryItem.pupilsightPersonIDStatusResponsible=pupilsightPerson.pupilsightPersonID) WHERE pupilsightLibraryItem.status='On Loan' AND borrowable='Y' AND returnExpected<:today ORDER BY surname, preferredName";
        } else {
            $sql = "SELECT pupilsightLibraryItem.*, surname, preferredName, email FROM pupilsightLibraryItem JOIN pupilsightPerson ON (pupilsightLibraryItem.pupilsightPersonIDStatusResponsible=pupilsightPerson.pupilsightPersonID) WHERE pupilsightLibraryItem.status='On Loan' AND borrowable='Y' AND returnExpected<:today AND pupilsightPerson.status='Full' ORDER BY surname, preferredName";
        }
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    echo "<table cellspacing='0' style='width: 100%'>";
    echo "<tr class='head'>";
    echo '<th>';
    echo __('Borrowing User');
    echo '</th>';
    echo '<th>';
    echo __('Email');
    echo '</th>';
    echo '<th>';
    echo __('Item').'<br/>';
    echo "<span style='font-size: 85%; font-style: italic'>".__('Author/Producer').'</span>';
    echo '</th>';
    echo '<th>';
    echo __('Due Date');
    echo '</th>';
    echo '<th>';
    echo __('Days Overdue');
    echo '</th>';
    echo "<th style='width: 50px'>";
    echo __('Actions');
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
        echo formatName('', $row['preferredName'], $row['surname'], 'Student', true);
        echo '</td>';
        echo '<td>';
        echo $row['email'];
        echo '</td>';
        echo '<td>';
        echo '<b>'.$row['name'].'</b><br/>';
        echo "<span style='font-size: 85%; font-style: italic'>".$row['producer'].'</span>';
        echo '</td>';
        echo '<td>';
        echo dateConvertBack($guid, $row['returnExpected']);
        echo '</td>';
        echo '<td>';
        echo(strtotime($today) - strtotime($row['returnExpected'])) / (60 * 60 * 24);
        echo '</td>';
        echo '<td>';
        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/library_lending_item.php&pupilsightLibraryItemID='.$row['pupilsightLibraryItemID']."&name=&pupilsightLibraryTypeID=&pupilsightSpaceID=&status='><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
        echo '</td>';
        echo '</tr>';
    }
    if ($count == 0) {
        echo "<tr class=$rowNum>";
        echo '<td colspan=6>';
        echo __('There are no records to display.');
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
?>
