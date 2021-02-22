<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$page->breadcrumbs->add(__('Manage Catalog'));

if (isActionAccessible($guid, $connection2, '/modules/Library/library_manage_catalog.php') == false) {
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
    $pupilsightPersonIDOwnership = null;
    if (isset($_POST['pupilsightPersonIDOwnership'])) {
        $pupilsightPersonIDOwnership = trim($_POST['pupilsightPersonIDOwnership']);
    }
    if ($pupilsightPersonIDOwnership == '') {
        if (isset($_GET['pupilsightPersonIDOwnership'])) {
            $pupilsightPersonIDOwnership = trim($_GET['pupilsightPersonIDOwnership']);
        }
    }
    $typeSpecificFields = null;
    if (isset($_POST['typeSpecificFields'])) {
        $typeSpecificFields = trim($_POST['typeSpecificFields']);
    }
    if ($typeSpecificFields == '') {
        if (isset($_GET['typeSpecificFields'])) {
            $typeSpecificFields = trim($_GET['typeSpecificFields']);
        }
    }

    $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', "/modules/" . $_SESSION[$guid]['module'] . "/library_manage_catalog.php");

    $row = $form->addRow();
    $row->addLabel('name', __('ID/Name/Producer'));
    $row->addTextField('name')->setValue($name);

    $sql = "SELECT pupilsightLibraryTypeID AS value, name FROM pupilsightLibraryType WHERE active='Y' ORDER BY name";
    $row = $form->addRow();
    $row->addLabel('pupilsightLibraryTypeID', __('Type'));
    $row->addSelect('pupilsightLibraryTypeID')->fromQuery($pdo, $sql, array())->selected($pupilsightLibraryTypeID)->placeholder();

    $row = $form->addRow();
    $row->addLabel('pupilsightSpaceID', __('Location'));
    $row->addSelectSpace('pupilsightSpaceID')->selected($pupilsightSpaceID)->placeholder();

    $statuses = array(
        'Available' => __('Available'),
        'Decommissioned' => __('Decommissioned'),
        'In Use' => __('In Use'),
        'Lost' => __('Lost'),
        'On Loan' => __('On Loan'),
        'Repair' => __('Repair'),
        'Reserved' => __('Reserved')
    );
    $row = $form->addRow();
    $row->addLabel('status', __('Status'));
    $row->addSelect('status')->fromArray($statuses)->selected($status)->placeholder();


    $sql = 'SELECT  P.pupilsightPersonID, P.officialName FROM pupilsightLibraryItem AS L
    LEFT JOIN pupilsightPerson AS P  ON L.pupilsightPersonIDOwnership = P.pupilsightPersonID
    WHERE P.officialName != "" ';

    $result = $connection2->query($sql);
    $staffs = $result->fetchAll();
    $owner1 = array('' => 'Please Select ');
    $owner2 = array();
    foreach ($staffs as $dt) {
        $owner2[$dt['pupilsightPersonID']] = $dt['officialName'];
    }
    if ($owner2) {
        $owner = $owner1 + $owner2;
    }



    $row = $form->addRow();
    $row->addLabel('pupilsightPersonIDOwnership', __('Owner/User'));
    //$row->addSelectUsers('pupilsightPersonIDOwnership')->selected($pupilsightPersonIDOwnership)->placeholder();
    $row->addSelectUsers('pupilsightPersonIDOwnership')->fromArray($owner)->selected($pupilsightPersonIDOwnership)->placeholder();

    $row = $form->addRow();
    $row->addLabel('typeSpecificFields', __('Type-Specific Fields'))->description(__('For example, a computer\'s MAC address or a book\'s ISBN.'));
    $row->addTextField('typeSpecificFields')->setValue($typeSpecificFields);

    $row = $form->addRow()
        ->addClass('right_align');
    $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

    echo $form->getOutput();

    //Set pagination variable
    $page = 1;
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }
    if ((!is_numeric($page)) or $page < 1) {
        $page = 1;
    }

    //Search with filters applied
    try {
        $data = array();
        $sqlWhere = 'WHERE ';
        if ($name != '') {
            $data['name'] = '%' . $name . '%';
            $data['producer'] = '%' . $name . '%';
            $data['id'] = '%' . $name . '%';
            $sqlWhere .= '(name LIKE :name  OR producer LIKE :producer OR id LIKE :id) AND ';
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
        if ($pupilsightPersonIDOwnership != '') {
            $data['pupilsightPersonIDOwnership'] = $pupilsightPersonIDOwnership;
            $sqlWhere .= 'pupilsightPersonIDOwnership=:pupilsightPersonIDOwnership AND ';
        }
        if ($typeSpecificFields != '') {
            $data['fields'] = '%' . $typeSpecificFields . '%';
            $sqlWhere .= 'fields LIKE :fields AND ';
        }
        if ($sqlWhere == 'WHERE ') {
            $sqlWhere = '';
        } else {
            $sqlWhere = substr($sqlWhere, 0, -5);
        }

        $sql = "SELECT * FROM pupilsightLibraryItem $sqlWhere ORDER BY id";
        $sqlPage = $sql . ' LIMIT ' . $_SESSION[$guid]['pagination'] . ' OFFSET ' . (($page - 1) * $_SESSION[$guid]['pagination']);
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
    }

    echo "<div class='linkTop'>";
    echo "<a  style='width:111px !important' class = 'btn btn-primary addbtncss' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . "/library_manage_catalog_add.php&name=$name&pupilsightLibraryTypeID=$pupilsightLibraryTypeID&pupilsightSpaceID=$pupilsightSpaceID&status=$status&pupilsightPersonIDOwnership=$pupilsightPersonIDOwnership&typeSpecificFields=" . urlencode($typeSpecificFields) . "'>" . __('Add') . "<i style='margin-left: 5px' class='mdi mdi-plus-circle-outline mdi-24px' title='" . __('Add') . "' ></i></a>";


    echo '</div>';

    if ($result->rowCount() < 1) {
        echo '<h3>';
        echo __('View');
        echo '</h3>';

        echo "<div class='alert alert-danger'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        echo '<h3>';
        echo __('View');
        echo "<span style='font-weight: normal; font-style: italic; font-size: 55%'> " . sprintf(__('%1$s record(s) in current view'), $result->rowCount()) . '</span>';
        echo '</h3>';

        if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
            printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top', "name=$name&pupilsightLibraryTypeID=$pupilsightLibraryTypeID&pupilsightSpaceID=$pupilsightSpaceID&status=$status");
        }

        echo "<table cellspacing='0' class='table display data-table text-nowrap dataTable no-footer' style='width: 100%'>";
        echo "<thead>";
        echo "<tr class='head'>";
        echo "<th style='width: 80px'>";
        echo __('School ID') . '<br/>';
        echo "<span style='font-size: 85%; font-style: italic'>" . __('Type') . '</span>';
        echo '</th>';
        echo '<th>';
        echo __('Name') . '<br/>';
        echo "<span style='font-size: 85%; font-style: italic'>" . __('Producer') . '</span>';
        echo '</th>';
        echo '<th>';
        echo __('Location');
        echo '</th>';
        echo '<th>';
        echo __('Ownership') . '<br/>';
        echo "<span style='font-size: 85%; font-style: italic'>" . __('User/Owner') . '</span>';
        echo '</th>';
        echo '<th>';
        echo __('Status') . '<br/>';
        echo "<span style='font-size: 85%; font-style: italic'>" . __('Borrowable') . '</span>';
        echo '</th>';
        echo "<th style='width: 125px'>";
        echo __('Actions');
        echo '</th>';
        echo '</tr>';
        echo "</thead>";

        $count = 0;
        $rowNum = 'odd';
        try {
            $resultPage = $connection2->prepare($sqlPage);
            $resultPage->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
        while ($row = $resultPage->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }

            //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo '<b>' . $row['id'] . '</b><br/>';
            try {
                $dataType = array('pupilsightLibraryTypeID' => $row['pupilsightLibraryTypeID']);
                $sqlType = 'SELECT name FROM pupilsightLibraryType WHERE pupilsightLibraryTypeID=:pupilsightLibraryTypeID';
                $resultType = $connection2->prepare($sqlType);
                $resultType->execute($dataType);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            if ($resultType->rowCount() == 1) {
                $rowType = $resultType->fetch();
                echo "<span style='font-size: 85%; font-style: italic'>" . __($rowType['name']) . '</span>';
            }
            echo '</td>';
            echo '<td>';
            echo '<b>' . $row['name'] . '</b><br/>';
            echo "<span style='font-size: 85%; font-style: italic'>" . $row['producer'] . '</span>';
            echo '</td>';
            echo '<td>';
            if ($row['pupilsightSpaceID'] != '') {
                try {
                    $dataSpace = array('pupilsightSpaceID' => $row['pupilsightSpaceID']);
                    $sqlSpace = 'SELECT * FROM pupilsightSpace WHERE pupilsightSpaceID=:pupilsightSpaceID';
                    $resultSpace = $connection2->prepare($sqlSpace);
                    $resultSpace->execute($dataSpace);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }
                if ($resultSpace->rowCount() == 1) {
                    $rowSpace = $resultSpace->fetch();
                    echo $rowSpace['name'] . '<br/>';
                }
            }
            if ($row['locationDetail'] != '') {
                echo "<span style='font-size: 85%; font-style: italic'>" . $row['locationDetail'] . '</span>';
            }
            echo '</td>';
            echo '<td>';
            if ($row['ownershipType'] == 'School') {
                echo $_SESSION[$guid]['organisationNameShort'] . '<br/>';
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
                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }
                if ($resultPerson->rowCount() == 1) {
                    $rowPerson = $resultPerson->fetch();
                    echo "<span style='font-size: 85%; font-style: italic'>" . formatName($rowPerson['title'], $rowPerson['preferredName'], $rowPerson['surname'], 'Staff', false, true) . '</span>';
                }
            }
            echo '</td>';
            echo '<td>';
            echo __($row['status']) . '<br/>';
            echo "<span style='font-size: 85%; font-style: italic'>" . ynExpander($guid, $row['borrowable']) . '</span>';
            echo '</td>';
            echo '<td>';
            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/library_manage_catalog_edit.php&pupilsightLibraryItemID=' . $row['pupilsightLibraryItemID'] . "&name=$name&pupilsightLibraryTypeID=$pupilsightLibraryTypeID&pupilsightSpaceID=$pupilsightSpaceID&status=$status&pupilsightPersonIDOwnership=$pupilsightPersonIDOwnership&typeSpecificFields=" . urlencode($typeSpecificFields) . "'><i title='" . __('Edit') . "' class='mdi mdi-pencil-box-outline mdi-24px'></i></a> ";
            if ($row['borrowable'] == "Y") {
                echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/library_lending_item.php&pupilsightLibraryItemID=' . $row['pupilsightLibraryItemID'] . "&name=$name&pupilsightLibraryTypeID=$pupilsightLibraryTypeID&pupilsightSpaceID=$pupilsightSpaceID&status=$status&pupilsightPersonIDOwnership=$pupilsightPersonIDOwnership&typeSpecificFields=" . urlencode($typeSpecificFields) . "'><i title='" . __('Lending') . "' class='mdi mdi-account-circle-outline mdi-24px'></i></a> ";
            }
            echo "<a class='thickbox' href='" . $_SESSION[$guid]['absoluteURL'] . '/fullscreen.php?q=/modules/' . $_SESSION[$guid]['module'] . '/library_manage_catalog_delete.php&pupilsightLibraryItemID=' . $row['pupilsightLibraryItemID'] . "&name=$name&pupilsightLibraryTypeID=$pupilsightLibraryTypeID&pupilsightSpaceID=$pupilsightSpaceID&status=$status&pupilsightPersonIDOwnership=$pupilsightPersonIDOwnership&typeSpecificFields=" . urlencode($typeSpecificFields) . "&width=650&height=135'><i title='" . __('Delete') . "' class='mdi mdi-trash-can-outline mdi-24px'></i></a>";
            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/library_manage_catalog_duplicate.php&pupilsightLibraryItemID=' . $row['pupilsightLibraryItemID'] . "&name=$name&pupilsightLibraryTypeID=$pupilsightLibraryTypeID&pupilsightSpaceID=$pupilsightSpaceID&status=$status&pupilsightPersonIDOwnership=$pupilsightPersonIDOwnership&typeSpecificFields=" . urlencode($typeSpecificFields) . "'><i title='" . __('Duplicate') . "' class='mdi mdi-content-copy mdi-24px'></i></a>";
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
?>

<style>
    .paginationTop {
        margin-bottom: 15px;
    }
</style>