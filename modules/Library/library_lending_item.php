<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs
    ->add(__('Lending & Activity Log'), 'library_lending.php')
    ->add(__('View Item'));

if (isActionAccessible($guid, $connection2, '/modules/Library/library_lending_item.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('success0' => 'Your request was completed successfully.'));
    }

    //Check if school year specified
    $pupilsightLibraryItemID = $_GET['pupilsightLibraryItemID'];
    if ($pupilsightLibraryItemID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID);
            $sql = 'SELECT pupilsightLibraryItem.*, pupilsightLibraryType.name AS type FROM pupilsightLibraryItem JOIN pupilsightLibraryType ON (pupilsightLibraryItem.pupilsightLibraryTypeID=pupilsightLibraryType.pupilsightLibraryTypeID) WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID';
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
            //Let's go!
            $row = $result->fetch();

            $overdue = (strtotime(date('Y-m-d')) - strtotime($row['returnExpected'])) / (60 * 60 * 24);
            if ($overdue > 0 and $row['status'] == 'On Loan') {
                echo "<div class='alert alert-danger'>";
                echo sprintf(__('This item is now %1$s%2$s days overdue'), '<u><b>', $overdue).'</b></u>.';
                echo '</div>';
            }

            $name = '';
            if (isset($_GET['name'])) {
                $name = $_GET['name'];
            }
            $pupilsightLibraryTypeID = '';
            if (isset($_GET['pupilsightLibraryTypeID'])) {
                $pupilsightLibraryTypeID = $_GET['pupilsightLibraryTypeID'];
            }
            $pupilsightSpaceID = '';
            if (isset($_GET['pupilsightSpaceID'])) {
                $pupilsightSpaceID = $_GET['pupilsightSpaceID'];
            }
            $status = '';
            if (isset($_GET['status'])) {
                $status = $_GET['status'];
            }

            if ($name != '' or $pupilsightLibraryTypeID != '' or $pupilsightSpaceID != '' or $status != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Library/library_lending.php&name='.$name.'&pupilsightLibraryTypeID='.$pupilsightLibraryTypeID.'&pupilsightSpaceID='.$pupilsightSpaceID.'&status='.$status."'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            echo '<h3>';
            echo __('Item Details');
            echo '</h3>';

            echo "<table class='table'>";
            echo '<tr>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Type').'</span><br/>';
            echo '<i>'.__($row['type']).'</i>';
            echo '</td>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('ID').'</span><br/>';
            echo '<i>'.$row['id'].'</i>';
            echo '</td>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span class='form-label'>".__('Name').'</span><br/>';
            echo '<i>'.$row['name'].'</i>';
            echo '</td>';
            echo '</tr>';
            echo '<tr>';
            echo "<td style='padding-top: 15px; vertical-align: top'>";
            echo "<span class='form-label'>".__('Author/Brand').'</span><br/>';
            echo '<i>'.$row['producer'].'</i>';
            echo '</td>';
            echo "<td style='padding-top: 15px; vertical-align: top'>";
            echo "<span class='form-label'>".__('Status').'</span><br/>';
            echo '<i>'.$row['status'].'</i>';
            echo '</td>';
            echo "<td style='padding-top: 15px; vertical-align: top'>";
            echo "<span class='form-label'>".__('Borrowable').'</span><br/>';
            echo '<i>'.$row['borrowable'].'</i>';
            echo '</td>';
            echo '</tr>';
            echo '</table>';

            echo '<h3>';
            echo __('Lending & Activity Log');
            echo '</h3>';
            //Set pagination variable
            $page = 1;
            if (isset($_GET['page'])) {
                $page = $_GET['page'];
            }
            if ((!is_numeric($page)) or $page < 1) {
                $page = 1;
            }
            try {
                $dataEvent = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID);
                $sqlEvent = 'SELECT * FROM pupilsightLibraryItemEvent WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID ORDER BY timestampOut DESC';
                $sqlEventPage = $sqlEvent.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($page - 1) * $_SESSION[$guid]['pagination']);
                $resultEvent = $connection2->prepare($sqlEvent);
                $resultEvent->execute($dataEvent);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            echo "<div class='linkTop'>";
            if ($row['status'] == 'Available') {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/library_lending_item_signout.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&name=".$name.'&pupilsightLibraryTypeID='.$pupilsightLibraryTypeID.'&pupilsightSpaceID='.$pupilsightSpaceID.'&status='.$status."'>".__('Sign Out')." <img  style='margin: 0 0 -4px 3px' title='".__('Sign Out')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_right.png'/></a>";
            } else {
                echo '<i>'.__('This item has already been signed out.').'</i>';
            }
            echo '</div>';

            if ($resultEvent->rowCount() < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('There are no records to display.');
                echo '</div>';
            } else {
                if ($resultEvent->rowCount() > $_SESSION[$guid]['pagination']) {
                    printPagination($guid, $resultEvent->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top', '');
                }

                echo "<table cellspacing='0' style='width: 100%'>";
                echo "<tr class='head'>";
                echo "<th style='text-align: center; min-width: 90px'>";
                echo __('User');
                echo '</th>';
                echo '<th>';
                echo __('Status').'<br/>';
                echo "<span style='font-size: 85%; font-style: italic'>".__('Date Out & In').'</span><br/>';
                echo '</th>';
                echo '<th>';
                echo __('Due Date');
                echo '</th>';
                echo '<th>';
                echo __('Return Action');
                echo '</th>';
                echo '<th>';
                echo __('Recorded By');
                echo '</th>';
                echo "<th style='width: 110px'>";
                echo __('  Actions ');
                echo '</th>';
                echo '</tr>';

                $count = 0;
                $rowNum = 'odd';
                try {
                    $resultEventPage = $connection2->prepare($sqlEventPage);
                    $resultEventPage->execute($dataEvent);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                while ($rowEvent = $resultEventPage->fetch()) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$count;

					//COLOR ROW BY STATUS!
					echo "<tr class=$rowNum>";
                    if ($rowEvent['pupilsightPersonIDStatusResponsible'] != '') {
                        try {
                            $dataPerson = array('pupilsightPersonID' => $rowEvent['pupilsightPersonIDStatusResponsible']);
                            $sqlPerson = 'SELECT title, preferredName, surname, image_240 FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
                            $resultPerson = $connection2->prepare($sqlPerson);
                            $resultPerson->execute($dataPerson);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultPerson->rowCount() == 1) {
                            $rowPerson = $resultPerson->fetch();
                        }
                    }
                    echo '<td style=\'text-align: center\'>';
                    if (is_array($rowPerson)) {
                        echo getUserPhoto($guid, $rowPerson['image_240'], 75);
                    }
                    if (is_array($rowPerson)) {
                        echo "<div style='margin-top: 3px; font-weight: bold'>".formatName($rowPerson['title'], $rowPerson['preferredName'], $rowPerson['surname'], 'Staff', false, true).'</div>';
                    }
                    echo '</td>';
                    echo '<td>';
                    echo $rowEvent['status'].'<br/>';
                    if ($rowEvent['timestampOut'] != '') {
                        echo "<span style='font-size: 85%; font-style: italic'>".dateConvertBack($guid, substr($rowEvent['timestampOut'], 0, 10));

                        if ($rowEvent['timestampReturn'] != '') {
                            echo ' - '.dateConvertBack($guid, substr($rowEvent['timestampReturn'], 0, 10));
                        }
                        echo '</span>';
                    }
                    echo '</td>';
                    echo '<td>';
                    if ($rowEvent['status'] != 'Returned' and $rowEvent['returnExpected'] != '') {
                        echo dateConvertBack($guid, substr($rowEvent['returnExpected'], 0, 10)).'<br/>';
                    }
                    echo '</td>';
                    echo '<td>';
                    if ($rowEvent['status'] != 'Returned' and  $rowEvent['returnAction'] != '') {
                        echo $rowEvent['returnAction'];
                    }
                    echo '</td>';
                    echo '<td>';
                    if ($rowEvent['pupilsightPersonIDOut'] != '') {
                        try {
                            $dataPerson = array('pupilsightPersonID' => $rowEvent['pupilsightPersonIDOut']);
                            $sqlPerson = 'SELECT title, preferredName, surname, image_240 FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
                            $resultPerson = $connection2->prepare($sqlPerson);
                            $resultPerson->execute($dataPerson);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultPerson->rowCount() == 1) {
                            $rowPerson = $resultPerson->fetch();
                        }
                        echo __('Out:').' '.formatName($rowPerson['title'], $rowPerson['preferredName'], $rowPerson['surname'], 'Staff', false, true).'<br/>';
                    }
                    if ($rowEvent['pupilsightPersonIDIn'] != '') {
                        try {
                            $dataPerson = array('pupilsightPersonID' => $rowEvent['pupilsightPersonIDIn']);
                            $sqlPerson = 'SELECT title, preferredName, surname, image_240 FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
                            $resultPerson = $connection2->prepare($sqlPerson);
                            $resultPerson->execute($dataPerson);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        if ($resultPerson->rowCount() == 1) {
                            $rowPerson = $resultPerson->fetch();
                        }
                        echo __('In:').' '.formatName($rowPerson['title'], $rowPerson['preferredName'], $rowPerson['surname'], 'Staff', false, true);
                    }
                    echo '</td>';
                    echo '<td>';
                    if ($count == 1 and $rowEvent['status'] != 'Returned') {
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/library_lending_item_edit.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&pupilsightLibraryItemEventID=".$rowEvent['pupilsightLibraryItemEventID'].'&name='.$name.'&pupilsightLibraryTypeID='.$pupilsightLibraryTypeID.'&pupilsightSpaceID='.$pupilsightSpaceID.'&status='.$status."'><i title='".__('Edit')."' class='mdi mdi-pencil-box-outline mdi-24px px-2'></i></a> ";
                        // echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/library_lending_item_return.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&pupilsightLibraryItemEventID=".$rowEvent['pupilsightLibraryItemEventID'].'&name='.$name.'&pupilsightLibraryTypeID='.$pupilsightLibraryTypeID.'&pupilsightSpaceID='.$pupilsightSpaceID.'&status='.$status."'><i title='Return' class='mdi-arrow-left-circle-outline' px-2></i></a>";
                        // echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/library_lending_item_renew.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&pupilsightLibraryItemEventID=".$rowEvent['pupilsightLibraryItemEventID'].'&name='.$name.'&pupilsightLibraryTypeID='.$pupilsightLibraryTypeID.'&pupilsightSpaceID='.$pupilsightSpaceID.'&status='.$status."'><i title='Renew'  class='mdi mdi-arrow-right-circle-outline px-2'></i></a>";
                         echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/library_lending_item_return.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&pupilsightLibraryItemEventID=".$rowEvent['pupilsightLibraryItemEventID'].'&name='.$name.'&pupilsightLibraryTypeID='.$pupilsightLibraryTypeID.'&pupilsightSpaceID='.$pupilsightSpaceID.'&status='.$status."'><i title='Return' class='mdi mdi-keyboard-return'  style='font-size:24px;'></i></a>";
                         echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/library_lending_item_renew.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&pupilsightLibraryItemEventID=".$rowEvent['pupilsightLibraryItemEventID'].'&name='.$name.'&pupilsightLibraryTypeID='.$pupilsightLibraryTypeID.'&pupilsightSpaceID='.$pupilsightSpaceID.'&status='.$status."'><i title='Renew' class='mdi mdi-autorenew mdi-24px px-2'></i></a>";
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';

                if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                    printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'bottom', '');
                }
            }

            $_SESSION[$guid]['sidebarExtra'] = '';
            $_SESSION[$guid]['sidebarExtra'] .= getImage($guid, $row['imageType'], $row['imageLocation']);
        }
    }
}
