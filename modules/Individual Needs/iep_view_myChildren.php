<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Individual Needs/iep_view_myChildren.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $entryCount = 0;
    $page->breadcrumbs->add(__('View Individual Education Plans'));

    echo '<p>';
    echo __('This section allows you to view individual education plans, where they exist, for children within your family.').'<br/>';
    echo '</p>';

    //Test data access field for permission
    try {
        $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
        $sql = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() < 1) {
        echo "<div class='alert alert-danger'>";
        echo __('Access denied.');
        echo '</div>';
    } else {
        //Get child list
        $count = 0;
        $options = array();

        while ($row = $result->fetch()) {
            try {
                $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                $resultChild = $connection2->prepare($sqlChild);
                $resultChild->execute($dataChild);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            while ($rowChild = $resultChild->fetch()) {
                $options[$rowChild['pupilsightPersonID']]=Format::name('', $rowChild['preferredName'], $rowChild['surname'], 'Student', true);
            }
        }

        $pupilsightPersonID = (isset($_GET['pupilsightPersonID']))? $_GET['pupilsightPersonID'] : null;

        if (count($options) == 0) {
            echo "<div class='alert alert-danger'>";
            echo __('Access denied.');
            echo '</div>';
        } elseif (count($options) == 1) {
            $pupilsightPersonID = key($options);
        } else {
            echo '<h2>';
            echo 'Choose Student';
            echo '</h2>';

            $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
            $form->setClass('noIntBorder fullWidth');

            $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/iep_view_myChildren.php');
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('pupilsightPersonID', __('Student'));
                $row->addSelect('pupilsightPersonID')->fromArray($options)->selected($pupilsightPersonID)->placeholder();

            $row = $form->addRow();
                $row->addSearchSubmit($pupilsight->session);

            echo $form->getOutput();
        }

        if ($pupilsightPersonID != '' && count($options) > 0) {
            //Confirm access to this student
            try {
                $dataChild = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2 AND childDataAccess='Y'";
                $resultChild = $connection2->prepare($sqlChild);
                $resultChild->execute($dataChild);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            if ($resultChild->rowCount() < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $rowChild = $resultChild->fetch();

                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sql = 'SELECT * FROM pupilsightIN WHERE pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() != 1) {
                    echo '<h3>';
                    echo __('View');
                    echo '</h3>';

                    echo "<div class='alert alert-danger'>";
                    echo __('There are no records to display.');
                    echo '</div>';
                } else {
                    echo '<h3>';
                    echo __('View');
                    echo '</h3>';

                    $row = $result->fetch(); ?>	
					<table class='smallIntBorder fullWidth' cellspacing='0'>	
						<tr>
							<td colspan=2 style='padding-top: 25px'> 
								<span style='font-weight: bold; font-size: 135%'><?php echo __('Targets') ?></span><br/>
								<?php
                                echo '<p>'.$row['targets'].'</p>'; ?>
							</td>
						</tr>
						<tr>
							<td colspan=2> 
								<span style='font-weight: bold; font-size: 135%'><?php echo __('Teaching Strategies') ?></span><br/>
								<?php
                                echo '<p>'.$row['strategies'].'</p>'; ?>
							</td>
						</tr>
						<tr>
							<td colspan=2 style='padding-top: 25px'> 
								<span style='font-weight: bold; font-size: 135%'><?php echo __('Notes & Review') ?></span><br/>
								<?php
                                echo '<p>'.$row['notes'].'</p>'; ?>
							</td>
						</tr>
					</table>
					<?php

                }
            }
        }
    }
}
?>
