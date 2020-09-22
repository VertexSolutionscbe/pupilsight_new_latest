<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fees_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Fees'));

    echo '<p>';
    echo __('In this area you can create the various fee options which apply to students. Fees are specific to a school year, cannot be deleted and must be linked to a category. When you come to create invoices later on, you will be able to use these fees, as well as ad hoc charges.');
    echo '</p>';

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
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        if ($result->rowcount() != 1) {
            echo "<div class='error'>";
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
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/fees_manage.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
        echo ' | ';
        if (getNextSchoolYearID($pupilsightSchoolYearID, $connection2) != false) {
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/fees_manage.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
        } else {
            echo __('Next Year').' ';
        }
        echo '</div>';

        echo '<h3>';
        echo __('Search');
        echo '</h3>';

        $search = isset($_GET['search'])? $_GET['search'] : '';

        $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/fees_manage.php');
        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        $row = $form->addRow();
            $row->addLabel('search', __('Search For'))->description(__('Fee name, category name.'));
            $row->addTextField('search')->setValue($search);

        $row = $form->addRow();
            $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

        echo $form->getOutput();

        echo '<h3>';
        echo __('View');
        echo '</h3>';
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
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT pupilsightFinanceFee.*, pupilsightFinanceFeeCategory.name AS category FROM pupilsightFinanceFee LEFT JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY name';
            if ($search != '') {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'search1' => "%$search%", 'search2' => "%$search%");
                $sql = 'SELECT pupilsightFinanceFee.*, pupilsightFinanceFeeCategory.name AS category FROM pupilsightFinanceFee LEFT JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND (pupilsightFinanceFee.name LIKE :search1 OR pupilsightFinanceFeeCategory.name LIKE :search2) ORDER BY name';
            }
            $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($page - 1) * $_SESSION[$guid]['pagination']);
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        echo "<div style='height:50px;'><div class='float-right mb-2'><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/fees_manage_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  
        
       
        if ($result->rowCount() < 1) {
            echo "<div class='error'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top', "pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");
            }

            echo "<table class='table' cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo '<th>';
            echo __('Name').'<br/>';
            echo "<span style='font-style: italic; font-size: 85%'>".__('Short Name').'</span>';
            echo '</th>';
            echo '<th>';
            echo __('Category');
            echo '</th>';
            echo '<th>';
            echo __('Fee').'<br/>';
            echo "<span style='font-style: italic; font-size: 85%'>".$_SESSION[$guid]['currency'].'</span>';
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
                echo "<div class='error'>".$e->getMessage().'</div>';
            }
            while ($row = $resultPage->fetch()) {
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count;

                    //Color rows based on start and end date
                    if ($row['active'] != 'Y') {
                        $rowNum = 'error';
                    }

                echo "<tr class=$rowNum>";
                echo '<td>';
                echo '<b>'.$row['name'].'</b><br/>';
                echo "<span style='font-style: italic; font-size: 85%'>".$row['nameShort'].'</span>';
                echo '</td>';
                echo '<td>';
                echo $row['category'];
                echo '</td>';
                echo '<td>';
                if (substr($_SESSION[$guid]['currency'], 4) != '') {
                    echo substr($_SESSION[$guid]['currency'], 4).' ';
                }
                echo number_format($row['fee'], 2, '.', ',');
                echo '</td>';
                echo '<td>';
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/fees_manage_edit.php&pupilsightFinanceFeeID='.$row['pupilsightFinanceFeeID']."&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search'><i title='Edit' class='fas fa-edit px-2'></i></a> ";
                echo "<script type='text/javascript'>";
                echo '$(document).ready(function(){';
                echo "\$(\".comment-$count-$count\").hide();";
                echo "\$(\".show_hide-$count-$count\").fadeIn(1000);";
                echo "\$(\".show_hide-$count-$count\").click(function(){";
                echo "\$(\".comment-$count-$count\").fadeToggle(1000);";
                echo '});';
                echo '});';
                echo '</script>';
                
                if ($row['description'] != '') {
                    echo "<a title='".__('View Description')."' class='show_hide-$count-$count' onclick='false' href='#'>
                    <i  class='fas fa-eye px-2' onclick='return false;'></i>
                    </a>";
                    // echo "<a title='".__('View Description')."' class='show_hide-$count-$count' onclick='false' href='#'><img style='padding-right: 5px' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/page_down.png' alt='".__('Show Comment')."' onclick='return false;' /></a>";
                }
                echo '</td>';
                echo '</tr>';
                if ($row['description'] != '') {
                    echo "<tr class='comment-$count-$count' id='comment-$count-$count'>";
                    echo '<td colspan=6>';
                    echo $row['description'];
                    echo '</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';

            if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'bottom', "pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search");
            }
        }
    }
}
?>
