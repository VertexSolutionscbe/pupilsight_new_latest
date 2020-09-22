<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$page->breadcrumbs->add(__('Lending & Activity Log'));

if (isActionAccessible($guid, $connection2, '/modules/Library/library_lending.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
    }

    echo '<h3>';
    echo __('Search & Filter');
    echo '</h3>';

    //Get current filter values
    $name = null;
    if (isset($_POST['name'])) {
        $name = trim($_POST['name']);
    }
    if ($name == '') {
        if (isset($_GET['name'])) {
            $name = trim($_GET['name']);
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

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/library_lending.php');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/library_lending.php");

    $row = $form->addRow();
        $row->addLabel('name', __('ID/Name/Producer'));
        $row->addTextField('name')->setValue($name)->maxLength(50);

    $data = array();
    $sql = "SELECT pupilsightLibraryTypeID AS value, name FROM pupilsightLibraryType WHERE active='Y' ORDER BY name";
    $row = $form->addRow();
        $row->addLabel('pupilsightLibraryTypeID', __('Type'));
        $row->addSelect('pupilsightLibraryTypeID')->fromQuery($pdo, $sql, $data)->placeholder()->selected($pupilsightLibraryTypeID);

    $row = $form->addRow();
        $row->addLabel('pupilsightSpaceID', __('Space'));
        $row->addSelectSpace('pupilsightSpaceID')->placeholder()->selected($pupilsightSpaceID);

    $statuses = array(
        'Available' => __('Available'),
        'On Loan' => __('On Loan'),
        'Repair' => __('Repair'),
        'Reserved' => __('Reserved')
    );
    $row = $form->addRow();
        $row->addLabel('type', __('Type'));
        $row->addSelect('type')->fromArray($statuses)->selected($status)->placeholder();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();


    //Set pagination variable
    $page = 1;
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }
    if ((!is_numeric($page)) or $page < 1) {
        $page = 1;
    }

    echo '<h3>';
    echo __('View');
    echo '</h3>';

    //Search with filters applied
    try {
        $data = array();
        $sqlWhere = 'AND ';
        $sqlWhere2 = 'AND ';
        if ($name != '') {
            $data['name'] = '%'.$name.'%';
            $data['producer'] = '%'.$name.'%';
            $data['id'] = '%'.$name.'%';
            $data['name2'] = '%'.$name.'%';
            $data['producer2'] = '%'.$name.'%';
            $data['id2'] = '%'.$name.'%';
            $sqlWhere .= '(name LIKE :name  OR producer LIKE :producer OR id LIKE :id) AND ';
            $sqlWhere2 .= '(name LIKE :name2  OR producer LIKE :producer2 OR id LIKE :id2) AND ';
        }
        if ($pupilsightLibraryTypeID != '') {
            $data['pupilsightLibraryTypeID'] = $pupilsightLibraryTypeID;
            $data['pupilsightLibraryTypeID2'] = $pupilsightLibraryTypeID;
            $sqlWhere .= 'pupilsightLibraryTypeID=:pupilsightLibraryTypeID AND ';
            $sqlWhere2 .= 'pupilsightLibraryTypeID=:pupilsightLibraryTypeID2 AND ';
        }
        if ($pupilsightSpaceID != '') {
            $data['pupilsightSpaceID'] = $pupilsightSpaceID;
            $data['pupilsightSpaceID2'] = $pupilsightSpaceID;
            $sqlWhere .= 'pupilsightSpaceID=:pupilsightSpaceID AND ';
            $sqlWhere2 .= 'pupilsightSpaceID=:pupilsightSpaceID2 AND ';
        }
        if ($status != '') {
            $data['status'] = $status;
            $data['status2'] = $status;
            $sqlWhere .= 'status=:status AND ';
            $sqlWhere .= 'status=:status2 AND ';
        }
        if ($sqlWhere == 'AND ') {
            $sqlWhere = '';
        } else {
            $sqlWhere = substr($sqlWhere, 0, -5);
        }
        if ($sqlWhere2 == 'AND ') {
            $sqlWhere2 = '';
        } else {
            $sqlWhere2 = substr($sqlWhere2, 0, -5);
        }

        $sql = "(SELECT pupilsightLibraryItem.*, NULL AS borrower FROM pupilsightLibraryItem WHERE (status='Available' OR status='Repair' OR status='Reserved') AND NOT ownershipType='Individual' AND borrowable='Y' $sqlWhere)
			UNION
			(SELECT pupilsightLibraryItem.*, concat(preferredName, ' ', surname) AS borrower FROM pupilsightLibraryItem JOIN pupilsightPerson ON (pupilsightLibraryItem.pupilsightPersonIDStatusResponsible=pupilsightPerson.pupilsightPersonID) WHERE (pupilsightLibraryItem.status='On Loan') AND NOT ownershipType='Individual' AND borrowable='Y' $sqlWhere2) ORDER BY name, producer";
        $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($page - 1) * $_SESSION[$guid]['pagination']);
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
        if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
            printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top', "name=$name&pupilsightLibraryTypeID=$pupilsightLibraryTypeID&pupilsightSpaceID=$pupilsightSpaceID&status=$status");
        }

        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('Number');
        echo '</th>';
        echo '<th>';
        echo __('ID');
        echo '</th>';
        echo "<th style='width: 250px'>";
        echo __('Name').'<br/>';
        echo "<span style='font-size: 85%; font-style: italic'>".__('Producer').'</span>';
        echo '</th>';
        echo '<th>';
        echo __('Type');
        echo '</th>';
        echo '<th>';
        echo __('Location');
        echo '</th>';
        echo '<th>';
        echo __('Status').'<br/>';
        echo "<span style='font-size: 85%; font-style: italic'>".__('Return Date').'<br/>'.__('Borrower').'</span>';
        echo '</th>';
        echo '<th>';
        echo __('Actions');
        echo '</th>';
        echo '</tr>';

        $count = 0;
        $rowNum = 'odd';
        try {
            $resultPage = $connection2->prepare($sqlPage);
            $resultPage->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        while ($row = $resultPage->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            if ((strtotime(date('Y-m-d')) - strtotime($row['returnExpected'])) / (60 * 60 * 24) > 0 and $row['status'] == 'On Loan') {
                $rowNum = 'error';
            }

            //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo $count + 1;
            echo '</td>';
            echo '<td>';
            echo '<b>'.$row['id'].'</b>';
            echo '</td>';
            echo '<td>';
            if (strlen($row['name']) > 30) {
                echo '<b>'.substr($row['name'], 0, 30).'...</b><br/>';
            } else {
                echo '<b>'.$row['name'].'</b><br/>';
            }
            echo "<span style='font-size: 85%; font-style: italic'>".$row['producer'].'</span>';
            echo '</td>';
            echo '<td>';
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
            echo $row['status'].'<br/>';
            if ($row['returnExpected'] != '' or $row['borrower'] != '') {
                echo "<span style='font-size: 85%; font-style: italic'>";
                if ($row['returnExpected'] != '') {
                    echo dateConvertBack($guid, $row['returnExpected']).'<br/>';
                }
                if ($row['borrower'] != '') {
                    echo $row['borrower'];
                }
                echo '</span>';
            }
            echo '</td>';
            echo '<td>';
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/library_lending_item.php&pupilsightLibraryItemID='.$row['pupilsightLibraryItemID']."&name=$name&pupilsightLibraryTypeID=$pupilsightLibraryTypeID&pupilsightSpaceID=$pupilsightSpaceID&status=$status'><i title='".__('Edit')."' class='mdi mdi-pencil-box-outline mdi-24px'></i></a> ";
            echo '</td>';
            echo '</tr>';

            ++$count;
        }
        echo '</table>';

        if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
            printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'bottom', "name=$name&pupilsightLibraryTypeID=$pupilsightLibraryTypeID&pupilsightSpaceID=$pupilsightSpaceID&status=$status");
        }
    }
}
