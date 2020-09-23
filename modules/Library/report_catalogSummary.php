<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$_SESSION[$guid]['report_student_emergencySummary.php_choices'] = '';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('Catalog Summary'));

if (isActionAccessible($guid, $connection2, '/modules/Library/report_catalogSummary.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<h3>';
    echo __('Search & Filter');
    echo '</h3>';

    //Get current filter values
    $ownershipType = null;
    if (isset($_POST['ownershipType'])) {
        $ownershipType = trim($_POST['ownershipType']);
    }
    if ($ownershipType == '') {
        if (isset($_GET['ownershipType'])) {
            $ownershipType = trim($_GET['ownershipType']);
        }
    }
    $pupilsightLibraryTypeID = null;
    if (isset($_POST['pupilsightLibraryTypeID'])) {
        $pupilsightLibraryTypeID = trim($_POST['pupilsightLibraryTypeID']);
    }
    if ($pupilsightLibraryTypeID == '') {
        if (isset($_GET['pupilsightLibraryTypeID'])) {
            $pupilsightLibraryTypeID = trim($_GET['pupilsightLibraryTypeID']);
        }
    }
    $pupilsightSpaceID = null;
    if (isset($_POST['pupilsightSpaceID'])) {
        $pupilsightSpaceID = trim($_POST['pupilsightSpaceID']);
    }
    if ($pupilsightSpaceID == '') {
        if (isset($_GET['pupilsightSpaceID'])) {
            $pupilsightSpaceID = trim($_GET['pupilsightSpaceID']);
        }
    }
    $status = null;
    if (isset($_POST['status'])) {
        $status = trim($_POST['status']);
    }
    if ($status == '') {
        if (isset($_GET['status'])) {
            $status = trim($_GET['status']);
        }
    }

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_catalogSummary.php");

    $row = $form->addRow();
        $row->addLabel('ownershipType', __('Ownership Type'));
        $row->addSelect('ownershipType')->fromArray(array('School' => __('School'), 'Individual' => __('Individual')))->selected($ownershipType)->placeholder();

    $sql = "SELECT pupilsightLibraryTypeID as value, name FROM pupilsightLibraryType WHERE active='Y' ORDER BY name";
    $row = $form->addRow();
        $row->addLabel('pupilsightLibraryTypeID', __('Item Type'));
        $row->addSelect('pupilsightLibraryTypeID')->fromQuery($pdo, $sql, array())->selected($pupilsightLibraryTypeID)->placeholder();

    $sql = "SELECT pupilsightSpaceID as value, name FROM pupilsightSpace ORDER BY name";
    $row = $form->addRow();
        $row->addLabel('pupilsightSpaceID', __('Location'));
        $row->addSelect('pupilsightSpaceID')->fromQuery($pdo, $sql, array())->selected($pupilsightSpaceID)->placeholder();

    $options = array("Available" => "Available", "Decommissioned" => "Decommissioned", "In Use" => "In Use", "Lost" => "Lost", "On Loan" => "On Loan", "Repair" => "Repair", "Reserved" => "Reserved");
    $row = $form->addRow();
        $row->addLabel('status', __('Status'));
        $row->addSelect('status')->fromArray($options)->selected($status)->placeholder();

    $row = $form->addRow();
        $row->addFooter(false);
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

	echo '<h3>';
	echo __('Report Data');
	echo '</h3>';

	echo "<div class='linkTop'>";
	echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/report_catalogSummaryExport.php?address='.$_GET['q']."&ownershipType=$ownershipType&pupilsightLibraryTypeID=$pupilsightLibraryTypeID&pupilsightSpaceID=$pupilsightSpaceID&status=$status'><img title='".__('Export to Excel')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/download.png'/></a>";
	echo '</div>';

	//Search with filters applied
	try {
		$data = array();
		$sqlWhere = 'WHERE ';
		if ($ownershipType != '') {
			$data['ownershipType'] = $ownershipType;
			$sqlWhere .= 'ownershipType=:ownershipType AND ';
		}
		if ($pupilsightLibraryTypeID != '') {
			$data['pupilsightLibraryTypeID'] = $pupilsightLibraryTypeID;
			$sqlWhere .= 'pupilsightLibraryTypeID=:pupilsightLibraryTypeID AND ';
		}
		if ($pupilsightSpaceID != '') {
			$data['pupilsightSpaceID'] = $pupilsightSpaceID;
			$sqlWhere .= 'pupilsightSpaceID=:pupilsightSpaceID AND ';
		}
		if ($status != '') {
			$data['status'] = $status;
			$sqlWhere .= 'status=:status AND ';
		}
		if ($sqlWhere == 'WHERE ') {
			$sqlWhere = '';
		} else {
			$sqlWhere = substr($sqlWhere, 0, -5);
		}
		$sql = "SELECT * FROM pupilsightLibraryItem $sqlWhere ORDER BY id";
		$result = $connection2->prepare($sql);
		$result->execute($data);
	} catch (PDOException $e) {
		echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
	}

	if ($result->rowCount() < 1) {
		echo "<div class='alert alert-danger'>";
		echo __('There are no records to display.');
		echo '</div>';
	} else {
		echo "<table cellspacing='0' style='width: 100%'>";
		echo "<tr class='head'>";
		echo '<th>';
		echo __('School ID').'<br/>';
		echo "<span style='font-style: italic; font-size: 85%'>".__('Type').'</span>';
		echo '</th>';
		echo '<th>';
		echo __('Name').'<br/>';
		echo "<span style='font-size: 85%; font-style: italic'>".__('Producer').'</span>';
		echo '</th>';
		echo '<th>';
		echo __('Location');
		echo '</th>';
		echo '<th>';
		echo __('Ownership').'<br/>';
		echo "<span style='font-size: 85%; font-style: italic'>".__('User/Owner').'</span>';
		echo '</th>';
		echo '<th>';
		echo __('Status').'<br/>';
		echo "<span style='font-size: 85%; font-style: italic'>".__('Borrowable').'</span>';
		echo '</th>';
		echo '<th>';
		echo __('Purchase Date').'<br/>';
		echo "<span style='font-size: 85%; font-style: italic'>".__('Vendor').'</span>';
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

			//COLOR ROW BY STATUS!
			echo "<tr class=$rowNum>";
			echo '<td>';
			echo '<b>'.$row['id'].'</b><br/>';
			echo "<span style='font-style: italic; font-size: 85%'>";
			try {
				$dataType = array('pupilsightLibraryTypeID' => $row['pupilsightLibraryTypeID']);
				$sqlType = 'SELECT name FROM pupilsightLibraryType WHERE pupilsightLibraryTypeID=:pupilsightLibraryTypeID';
				$resultType = $connection2->prepare($sqlType);
				$resultType->execute($dataType);
			} catch (PDOException $e) {
				echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
			}
			if ($resultType->rowCount() == 1) {
				$rowType = $resultType->fetch();
				echo __($rowType['name']).'<br/>';
			}
			echo '</span>';
			echo '</td>';
			echo '<td>';
			echo '<b>'.$row['name'].'</b><br/>';
			echo "<span style='font-size: 85%; font-style: italic'>".$row['producer'].'</span>';
			echo '</td>';
			echo '<td>';
			if ($row['pupilsightSpaceID'] != '') {
				try {
					$dataSpace = array('pupilsightSpaceID' => $row['pupilsightSpaceID']);
					$sqlSpace = 'SELECT * FROM pupilsightSpace WHERE pupilsightSpaceID=:pupilsightSpaceID';
					$resultSpace = $connection2->prepare($sqlSpace);
					$resultSpace->execute($dataSpace);
				} catch (PDOException $e) {
					echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
				}
				if ($resultSpace->rowCount() == 1) {
					$rowSpace = $resultSpace->fetch();
					echo $rowSpace['name'].'<br/>';
				}
			}
			if ($row['locationDetail'] != '') {
				echo "<span style='font-size: 85%; font-style: italic'>".$row['locationDetail'].'</span>';
			}
			echo '</td>';
			echo '<td>';
			if ($row['ownershipType'] == 'School') {
				echo $_SESSION[$guid]['organisationNameShort'].'<br/>';
			} elseif ($row['ownershipType'] == 'Individual') {
				echo 'Individual<br/>';
			}
			if ($row['pupilsightPersonIDOwnership'] != '') {
				try {
					$dataPerson = array('pupilsightPersonID' => $row['pupilsightPersonIDOwnership']);
					$sqlPerson = 'SELECT title, preferredName, surname FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
					$resultPerson = $connection2->prepare($sqlPerson);
					$resultPerson->execute($dataPerson);
				} catch (PDOException $e) {
					echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
				}
				if ($resultPerson->rowCount() == 1) {
					$rowPerson = $resultPerson->fetch();
					echo "<span style='font-size: 85%; font-style: italic'>".formatName($rowPerson['title'], $rowPerson['preferredName'], $rowPerson['surname'], 'Staff', false, true).'</span>';
				}
			}
			echo '</td>';
			echo '<td>';
			echo $row['status'].'<br/>';
			echo "<span style='font-size: 85%; font-style: italic'>".$row['borrowable'].'</span>';
			echo '</td>';
			echo '<td>';
			if ($row['purchaseDate'] == '') {
				echo '<i>'.__('Unknown').'</i><br/>';
			} else {
				echo dateConvertBack($guid, $row['purchaseDate']).'<br/>';
			}
			echo "<span style='font-size: 85%; font-style: italic'>".$row['vendor'].'</span>';
			echo '</td>';
            echo '</tr>';

            ++$count;
        }
        echo '</table>';
    }
}
?>
