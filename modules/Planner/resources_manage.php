<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('Manage Resources'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/resources_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Set pagination variable
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        if ((!is_numeric($page)) or $page < 1) {
            $page = 1;
        }

        $search = null;
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        }

        echo '<h2>';
        echo __('Search');
        echo '</h2>';

        $form = Form::create('resourcesManage', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/resources_manage.php');

        $row = $form->addRow();
            $row->addLabel('search', __('Search For'))->description(__('Resource name.'));
            $row->addTextField('search')->setValue($search);

        $row = $form->addRow();
            $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

        echo $form->getOutput();

        echo '<h2>';
        echo __('View');
        echo '</h2>';

        try {
            if ($highestAction == 'Manage Resources_all') {
                $data = array();
                $sql = 'SELECT pupilsightResource.*, surname, preferredName, title FROM pupilsightResource JOIN pupilsightPerson ON (pupilsightResource.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) ORDER BY timestamp DESC';
                if ($search != '') {
                    $data = array('name' => "%$search%");
                    $sql = 'SELECT pupilsightResource.*, surname, preferredName, title FROM pupilsightResource JOIN pupilsightPerson ON (pupilsightResource.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) AND (name LIKE :name) ORDER BY timestamp DESC';
                }
            } elseif ($highestAction == 'Manage Resources_my') {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = 'SELECT pupilsightResource.*, surname, preferredName, title FROM pupilsightResource JOIN pupilsightPerson ON (pupilsightResource.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightResource.pupilsightPersonID=:pupilsightPersonID ORDER BY timestamp DESC';
                if ($search != '') {
                    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'name' => "%$search%");
                    $sql = 'SELECT pupilsightResource.*, surname, preferredName, title FROM pupilsightResource JOIN pupilsightPerson ON (pupilsightResource.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightResource.pupilsightPersonID=:pupilsightPersonID AND (name LIKE :name) ORDER BY timestamp DESC';
                }
            }
            $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($page - 1) * $_SESSION[$guid]['pagination']);
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        echo "<div class='linkTop'>";    
        echo "<div style='height:50px;'><div class='float-right mb-2'>";  
        echo "&nbsp;&nbsp;<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/resources_manage_add.php&search='.$search."' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>"; 
        echo '</div>';

        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top');
            }

            echo "<table cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo '<th>';
            echo __('Name').'<br/>';
            echo "<span style='font-size: 85%; font-style: italic'>".__('Contributor').'</span>';
            echo '</th>';
            echo '<th>';
            echo __('Type');
            echo '</th>';
            echo '<th>';
            echo __('Category').'<br/>';
            echo "<span style='font-size: 85%; font-style: italic'>".__('Purpose').'</span>';
            echo '</th>';
            echo '<th>';
            echo __('Tags');
            echo '</th>';
            echo '<th>';
            echo __('Year Groups');
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
                ++$count;

                //COLOR ROW BY STATUS!
                echo "<tr class=$rowNum>";
                echo '<td>';
                echo getResourceLink($guid, $row['pupilsightResourceID'], $row['type'], $row['name'], $row['content']);
                echo "<span style='font-size: 85%; font-style: italic'>".formatName($row['title'], $row['preferredName'], $row['surname'], 'Staff').'</span>';
                echo '</td>';
                echo '<td>';
                echo $row['type'];
                echo '</td>';
                echo '<td>';
                echo '<b>'.$row['category'].'</b><br/>';
                echo "<span style='font-size: 85%; font-style: italic'>".$row['purpose'].'</span>';
                echo '</td>';
                echo '</td>';
                echo '<td>';
                $output = '';
                $tags = explode(',', $row['tags']);
                natcasesort($tags);
                foreach ($tags as $tag) {
                    $output .= trim($tag).', ';
                }
                echo substr($output, 0, -2);
                echo '</td>';
                echo '<td>';
                try {
                    $dataYears = array();
                    $sqlYears = 'SELECT pupilsightYearGroupID, nameShort, sequenceNumber FROM pupilsightYearGroup ORDER BY sequenceNumber';
                    $resultYears = $connection2->prepare($sqlYears);
                    $resultYears->execute($dataYears);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                $years = explode(',', $row['pupilsightYearGroupIDList']);
                if (count($years) > 0 and $years[0] != '') {
                    if (count($years) == $resultYears->rowCount()) {
                        echo '<i>'.__('All Years').'</i>';
                    } else {
                        $count3 = 0;
                        $count4 = 0;
                        while ($rowYears = $resultYears->fetch()) {
                            for ($i = 0; $i < count($years); ++$i) {
                                if ($rowYears['pupilsightYearGroupID'] == $years[$i]) {
                                    if ($count3 > 0 and $count4 > 0) {
                                        echo ', ';
                                    }
                                    echo __($rowYears['nameShort']);
                                    ++$count4;
                                }
                            }
                            ++$count3;
                        }
                    }
                } else {
                    echo '<i>'.__('None').'</i>';
                }
                echo '</td>';
                echo '<td>';
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/resources_manage_edit.php&pupilsightResourceID='.$row['pupilsightResourceID']."&search=$search'><img title='".__('Edit')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/config.png'/></a> ";
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';

            if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'bottom');
            }
        }
    }
}
?>
