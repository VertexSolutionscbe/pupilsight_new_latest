<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Domain\DataSet;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Module\Attendance\StudentHistoryData;
use Pupilsight\Module\Attendance\StudentHistoryView;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Attendance/report_studentHistory.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
?>
    
<?php
    //Proceed!
    $page->breadcrumbs->add(__('Student History'));
    $page->scripts->add('chart');

    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {

        $canTakeAttendanceByPerson = isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson.php');
        $pupilsightSchoolYearID = $pupilsight->session->get('pupilsightSchoolYearID');

        if ($highestAction == 'Student History_all') {
            echo '<h2>';
            echo __('Choose Student');
            echo '</h2>';

            $pupilsightPersonID = null;
            if (isset($_GET['pupilsightPersonID'])) {
                $pupilsightPersonID = $_GET['pupilsightPersonID'];
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');

            $form->setFactory(DatabaseFormFactory::create($pdo));
            $form->setClass('noIntBorder fullWidth');

            $form->addHiddenValue('q', "/modules/" . $_SESSION[$guid]['module'] . "/report_studentHistory.php");

            $row = $form->addRow();
            $row->addLabel('pupilsightPersonID', __('Student'));
            $row->addSelectStudent('pupilsightPersonID', $pupilsightSchoolYearID)->selected($pupilsightPersonID)->placeholder()->required();

            $row = $form->addRow();
            $row->addFooter();
            $row->addSearchSubmit($pupilsight->session);

            echo $form->getOutput();

            if ($pupilsightPersonID != '') {
                $output = '';
                echo '<h2>';
                echo __('Report Data');
                echo '</h2>';

                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }
                if ($result->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The specified record does not exist.');
                    echo '</div>';
                } else {
                    $row = $result->fetch();

                    // ATTENDANCE DATA
                    $attendanceData = $container
                        ->get(StudentHistoryData::class)
                        ->getAttendanceData($pupilsightSchoolYearID, $pupilsightPersonID, $row['dateStart'], $row['dateEnd']);

                    // DATA TABLE
                    $renderer = $container->get(StudentHistoryView::class);
                    $renderer->addData('canTakeAttendanceByPerson', $canTakeAttendanceByPerson);

                    $table = DataTable::create('studentHistory', $renderer);
                    $table->addHeaderAction('print', __('Print'))
                        ->setURL('/report.php')
                        ->addParam('q', '/modules/Attendance/report_studentHistory_print.php')
                        ->addParam('pupilsightPersonID', $pupilsightPersonID)
                        ->addParam('viewMode', 'print')
                        ->setIcon('print')
                        ->setTarget('_blank')
                        ->directLink()
                        ->displayLabel();

                    echo $table->render($attendanceData);
                }
            }
        } else if ($highestAction == 'Student History_myChildren') {
            $pupilsightPersonID = null;
            if (isset($_GET['pupilsightPersonID'])) {
                $pupilsightPersonID = $_GET['pupilsightPersonID'];
            }
            //Test data access field for permission
            try {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            if ($result->rowCount() < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('Access denied.');
                echo '</div>';
            } else {
                //Get child list
                $countChild = 0;
                $options = [];
                while ($row = $result->fetch()) {
                    try {
                        $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                        $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date('Y-m-d') . "') AND (dateEnd IS NULL  OR dateEnd>='" . date('Y-m-d') . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                        $resultChild = $connection2->prepare($sqlChild);
                        $resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }
                    if ($resultChild->rowCount() > 0) {
                        if ($resultChild->rowCount() == 1) {
                            $rowChild = $resultChild->fetch();
                            $pupilsightPersonID = $rowChild['pupilsightPersonID'];
                            $options[$rowChild['pupilsightPersonID']] = formatName('', $rowChild['preferredName'], $rowChild['surname'], 'Student', true);
                            ++$countChild;
                        } else {
                            while ($rowChild = $resultChild->fetch()) {
                                $options[$rowChild['pupilsightPersonID']] = formatName('', $rowChild['preferredName'], $rowChild['surname'], 'Student', true);
                                ++$countChild;
                            }
                        }
                    }
                }

                if ($countChild == 0) {
                    echo "<div class='alert alert-danger'>";
                    echo __('Access denied.');
                    echo '</div>';
                } else {
                    echo '<h2>';
                    echo __('Choose');
                    echo '</h2>';

                    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');

                    $form->setFactory(DatabaseFormFactory::create($pdo));
                    $form->setClass('noIntBorder fullWidth');

                    $form->addHiddenValue('q', "/modules/" . $_SESSION[$guid]['module'] . "/report_studentHistory.php");

                    if ($countChild > 0) {
                        $row = $form->addRow();
                        $row->addLabel('pupilsightPersonID', __('Child'));
                        if ($countChild > 1) {
                            $row->addSelect('pupilsightPersonID')->fromArray($options)->selected($pupilsightPersonID)->placeholder()->required();
                        } else {
                            $row->addSelect('pupilsightPersonID')->fromArray($options)->selected($pupilsightPersonID)->required();
                        }
                    }

                    $row = $form->addRow();
                    $row->addFooter();
                    $row->addSearchSubmit($pupilsight->session);

                    echo $form->getOutput();
                }

                if ($pupilsightPersonID != '' and $countChild > 0) {
                    //Confirm access to this student
                    try {
                        $dataChild = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date('Y-m-d') . "') AND (dateEnd IS NULL  OR dateEnd>='" . date('Y-m-d') . "') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2 AND childDataAccess='Y'";
                        $resultChild = $connection2->prepare($sqlChild);
                        @$resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }

                    if ($resultChild->rowCount() < 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The selected record does not exist, or you do not have access to it.');
                        echo '</div>';
                    } else {
                        $rowChild = $resultChild->fetch();

                        if ($pupilsightPersonID != '') {
                            $output = '';
                            echo '<h2>';
                            echo __('Report Data');
                            echo '</h2>';

                            try {
                                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                                $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                            }
                            if ($result->rowCount() != 1) {
                                echo "<div class='alert alert-danger'>";
                                echo __('The specified record does not exist.');
                                echo '</div>';
                            } else {
                                $row = $result->fetch();

                                // ATTENDANCE DATA
                                $attendanceData = $container
                                    ->get(StudentHistoryData::class)
                                    ->getAttendanceData($pupilsightSchoolYearID, $pupilsightPersonID, $row['dateStart'], $row['dateEnd']);

                                // DATA TABLE
                                $renderer = $container->get(StudentHistoryView::class);
                                $table = DataTable::create('studentHistory', $renderer);
                                echo $table->render($attendanceData);
                            }
                        }
                    }
                }
            }
        } else if ($highestAction == 'Student History_my') {
            $output = '';
            echo '<h2>';
            echo __('Report Data');
            echo '</h2>';

            try {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified record does not exist.');
                echo '</div>';
            } else {
                $row = $result->fetch();

                // ATTENDANCE DATA
                $attendanceData = $container
                    ->get(StudentHistoryData::class)
                    ->getAttendanceData($pupilsightSchoolYearID, $_SESSION[$guid]['pupilsightPersonID'], $row['dateStart'], $row['dateEnd']);

                // DATA TABLE
                $renderer = $container->get(StudentHistoryView::class);
                $table = DataTable::create('studentHistory', $renderer);
                echo $table->render($attendanceData);
            }
        }
    }
}
?>