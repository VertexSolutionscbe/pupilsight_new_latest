<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Module\Markbook\MarkbookView;

function sidebarExtra($guid, $pdo, $pupilsightPersonID, $pupilsightCourseClassID = '', $basePage = '')
{
    $output = '';

    if (empty($basePage)) $basePage = 'markbook_view.php';

    //Show class picker in sidebar

    $output .= '<div class="column-no-break">';
    $output .= '<h2>';

    $output .= '</h2>';

    $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('q', '/modules/Markbook/'.$basePage);
    $form->setClass('smallIntBorder w-full');

    $row = $form->addRow();
        $row->addSelectClass('pupilsightCourseClassID', $_SESSION[$guid]['pupilsightSchoolYearID'], $pupilsightPersonID)
            ->selected($pupilsightCourseClassID)
            ->placeholder()
            ->setClass('fullWidth');
        $row->addSubmit(__('Go'));

    $output .= $form->getOutput();
    $output .= '</div>';

    return $output;
}

function classChooser($guid, $pdo, $pupilsightCourseClassID)
{
    $enableColumnWeighting = getSettingByScope($pdo->getConnection(), 'Markbook', 'enableColumnWeighting');
    $enableGroupByTerm = getSettingByScope($pdo->getConnection(), 'Markbook', 'enableGroupByTerm');
    $enableRawAttainment = getSettingByScope($pdo->getConnection(), 'Markbook', 'enableRawAttainment');

    $output = '';
    $output .= "<h3 style='margin-top: 0px'>";
    $output .= __('Choose Class');
    $output .= '</h3>';
    $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/markbook_view.php');
    $row = $form->addRow();
        // SEARCH
    $search = $_GET['search'] ?? '';

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('search', __('Search'))->addClass('dte');
    $col->addDate('search')->addClass('txtfield')->required()->setValue($search); 


        // TERM
        if ($enableGroupByTerm == 'Y' ) {
            $selectTerm = (isset($_SESSION[$guid]['markbookTerm']))? $_SESSION[$guid]['markbookTerm'] : 0;
            $selectTerm = (isset($_GET['pupilsightSchoolYearTermID']))? $_GET['pupilsightSchoolYearTermID'] : $selectTerm;
    
            $data = array("pupilsightSchoolYearID"=>$_SESSION[$guid]['pupilsightSchoolYearID']);
            $sql = "SELECT pupilsightSchoolYearTermID as value, name FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY sequenceNumber";
            $result = $pdo->executeQuery($data, $sql);
            $terms = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_KEY_PAIR) : array();
    
          //  $col->addContent(__('').':')->prepend('&nbsp;&nbsp;');
            // $col->addSelect('pupilsightSchoolYearTermID')
            //     ->fromArray(array('-1' => __('All Terms')))
            //     ->fromArray($terms)
            //     ->selected($selectTerm)
            //     ->setClass('shortWidth medium_Width');
             
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('term', __('Term'))->addClass('dte');
    $col->addSelect('pupilsightSchoolYearTermID')->addClass('txtfield')   ->fromArray(array('-1' => __('All Terms')))
    ->fromArray($terms)->selected($selectTerm)->required();
    
            $_SESSION[$guid]['markbookTermName'] = isset($terms[$selectTerm])? $terms[$selectTerm] : $selectTerm;
            $_SESSION[$guid]['markbookTerm'] = $selectTerm;
        } else {
            $_SESSION[$guid]['markbookTerm'] = 0;
            $_SESSION[$guid]['markbookTermName'] = __('All Columns');
        }
    




 


    // SORT BY
    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightSchoolYearID'=>$_SESSION[$guid]['pupilsightSchoolYearID'] );
    $sql = "SELECT COUNT(DISTINCT rollOrder) FROM pupilsightCourseClassPerson INNER JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE role='Student' AND pupilsightCourseClassID=:pupilsightCourseClassID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightSchoolYearID=:pupilsightSchoolYearID";
    $result = $pdo->executeQuery($data, $sql);
    $rollOrderCount = ($result->rowCount() > 0)? $result->fetchColumn(0) : 0;
    if ($rollOrderCount > 0) {
        $selectOrderBy = (isset($_SESSION[$guid]['markbookOrderBy']))? $_SESSION[$guid]['markbookOrderBy'] : 'surname';
        $selectOrderBy = (isset($_GET['markbookOrderBy']))? $_GET['markbookOrderBy'] : $selectOrderBy;

        $orderBy = array(
            'rollOrder'     => __('Roll Order'),
            'surname'       => __('Surname'),
            'preferredName' => __('Preferred Name'),
        );
        $col->addContent(__('Sort By').':')->prepend('&nbsp;&nbsp;');
        $col->addSelect('markbookOrderBy')->fromArray($orderBy)->selected($selectOrderBy)->setClass('shortWidth medium_Width');

        $_SESSION[$guid]['markbookOrderBy'] = $selectOrderBy;
    }

    // SHOW
    $selectFilter = (isset($_SESSION[$guid]['markbookFilter']))? $_SESSION[$guid]['markbookFilter'] : '';
    $selectFilter = (isset($_GET['markbookFilter']))? $_GET['markbookFilter'] : $selectFilter;

    $_SESSION[$guid]['markbookFilter'] = $selectFilter;

    $filters = array('' => __('All Columns'));
    if ($enableColumnWeighting == 'Y') $filters['averages'] = __('Overall Grades');
    if ($enableRawAttainment == 'Y') $filters['raw'] = __('Raw Marks');
    $filters['marked'] = __('Marked');
    $filters['unmarked'] = __('Unmarked');

    // $col->addContent(__('Show').':')->prepend('&nbsp;&nbsp;');
    // $col->addSelect('markbookFilter')
    //     ->fromArray($filters)
    //     ->selected($selectFilter)
    //      ->setClass('shortWidth medium_Width');

         $col = $row->addColumn()->setClass('newdes');
         $col->addLabel('Show', __('Show'))->addClass('dte');
         $col->addSelect('markbookFilter')->addClass('txtfield')
         ->fromArray($filters)->selected($selectFilter)->required();
//CLASS
         $col = $row->addColumn()->setClass('newdes');
         $col->addLabel('Class', __('Class'))->addClass('dte');
         $col->addSelectClass('pupilsightCourseClassID', $_SESSION[$guid]['pupilsightSchoolYearID'], $_SESSION[$guid]['pupilsightPersonID'])
         ->fromArray($filters)->selected($pupilsightCourseClassID)->required();


    // CLASS
    // $col->addContent(__('Class').':')->prepend('&nbsp;&nbsp;');
    // $col->addSelectClass('pupilsightCourseClassID', $_SESSION[$guid]['pupilsightSchoolYearID'], $_SESSION[$guid]['pupilsightPersonID'])
    //     ->setClass('shortWidth medium_Width')
    //     ->selected($pupilsightCourseClassID);

   


    $col = $form->addRow()->addClass('right_align');
 
    $col->addSubmit(__('Go'))->setClass('ml-10  sumit_css');
    
    

    
    if (!empty($search)) {
        $clearURL = $_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_SESSION[$guid]['address'];
        $clearLink = sprintf('<a href="%s" class="small" style="">%s</a> &nbsp;', $clearURL, __('Clear Search'));

        $form->addRow()->addContent($clearLink)->addClass('right');
    }


    $output .= $form->getOutput();

    return $output;
}

function isDepartmentCoordinator( $pdo, $pupilsightPersonID ) {
    try {
        $data = array('pupilsightPersonID' => $pupilsightPersonID );
        $sql = "SELECT count(*) FROM pupilsightDepartmentStaff WHERE pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)')";
        $result = $pdo->executeQuery($data, $sql);

    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    return ($result->rowCount() > 0)? ($result->fetchColumn() >= 1) : false;
}

function getAnyTaughtClass( $pdo, $pupilsightPersonID, $pupilsightSchoolYearID ) {
    try {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightPersonID' => $pupilsightPersonID);
        $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY course, class LIMIT 1';
        $result = $pdo->executeQuery($data, $sql);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    return ($result->rowCount() > 0)? $result->fetch() : NULL;
}

function getClass( $pdo, $pupilsightPersonID, $pupilsightCourseClassID, $highestAction ) {
    try {
        if ($highestAction == 'View Markbook_allClassesAllData') {
            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourse.name AS courseName, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
        } else if ($highestAction == 'View Markbook_myClasses') {
            $data = array( 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourse.name AS courseName, pupilsightCourseClass.nameShort AS class, pupilsightCourse.pupilsightYearGroupIDList, pupilsightCourseClass.pupilsightCourseClassID FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class";
        } else {
            return null;
        }
        $result = $pdo->executeQuery($data, $sql);
    } catch (PDOException $e) {
        return null;
    }

    return ($result->rowCount() > 0)? $result->fetch() : NULL;
}

function getTeacherList( $pdo, $pupilsightCourseClassID ) {
    try {
        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
        $sql = "SELECT pupilsightPerson.pupilsightPersonID, title, surname, preferredName FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE role='Teacher' AND pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY surname, preferredName";
        $result = $pdo->executeQuery($data, $sql);

    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    $teacherList = array();
    if ($result->rowCount() > 0) {
        foreach ($result->fetchAll() as $teacher) {
            $teacherList[ $teacher['pupilsightPersonID'] ] = formatName($teacher['title'], $teacher['preferredName'], $teacher['surname'], 'Staff', false, false);
        }
    }

    return $teacherList;
}

function getAlertStyle( $alert, $concern ) {

    if ($concern == 'Y') {
        return "style='color: #".$alert['color'].'; font-weight: bold; border: 2px solid #'.$alert['color'].'; padding: 2px 4px; background-color: #'.$alert['colorBG'].";margin:0 auto;'";
    } else if ($concern == 'P') {
        return "style='color: #390; font-weight: bold; border: 2px solid #390; padding: 2px 4px; background-color: #D4F6DC;margin:0 auto;'";
    } else {
        return '';
    }
}

function renderStudentCumulativeMarks($pupilsight, $pdo, $pupilsightPersonID, $pupilsightCourseClassID) {

    require_once __DIR__ . '/src/MarkbookView.php';

    // Build the markbook object for this class & student
    $markbook = new MarkbookView($pupilsight, $pdo, $pupilsightCourseClassID);
    $assessmentScale = $markbook->getDefaultAssessmentScale();

    // Cancel our now if this isnt a percent-based mark
    if (empty($assessmentScale) || (stripos($assessmentScale['name'], 'percent') === false && $assessmentScale['nameShort'] !== '%')) {
        return;
    }

    // Calculate & get the cumulative average
    $markbook->cacheWeightings($pupilsightPersonID);
    $cumulativeMark = round($markbook->getCumulativeAverage($pupilsightPersonID));

    // Only display if there are marks
    if (!empty($cumulativeMark)) {
        // Divider
        echo '<tr class="break">';
            echo '<th colspan="7" style="height: 4px; padding: 0px;"></th>';
        echo '</tr>';

        // Display the cumulative average
        echo '<tr>';
            echo '<td style="width:120px;">';
                echo '<b>'.__('Cumulative Average').'</b>';
            echo '</td>';
            echo '<td style="padding: 10px !important; text-align: center;">';
                echo round( $cumulativeMark ).'%';
            echo '</td>';
            echo '<td colspan="3" class="dull"></td>';
         echo '</tr>';
    }
}

function renderStudentSubmission($student, $submission, $markbookColumn)
{
    global $guid;

    $output = '';

    if (!empty($submission)) {
        if ($submission['status'] == 'Exemption') {
            $linkText = __('Exe');
        } elseif ($submission['version'] == 'Final') {
            $linkText = __('Fin');
        } else {
            $linkText = __('Dra').$submission['count'];
        }

        $style = '';
        $status = __('On Time');
        if ($submission['status'] == 'Exemption') {
            $status = __('Exemption');
        } elseif ($submission['status'] == 'Late') {
            $style = "style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px'";
            $status = __('Late');
        }

        if ($submission['type'] == 'File') {
            $output .= "<span title='".$submission['version'].". $status. ".__('Submitted at').' '.substr($submission['timestamp'], 11, 5).' '.__('on').' '.dateConvertBack($guid, substr($submission['timestamp'], 0, 10))."' $style><a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/'.$submission['location']."'>$linkText</a></span>";
        } elseif ($submission['type'] == 'Link') {
            $output .= "<span title='".$submission['version'].". $status. ".__('Submitted at').' '.substr($submission['timestamp'], 11, 5).' '.__('on').' '.dateConvertBack($guid, substr($submission['timestamp'], 0, 10))."' $style><a target='_blank' href='".$submission['location']."'>$linkText</a></span>";
        } else {
            $output .= "<span title='$status. ".__('Recorded at').' '.substr($submission['timestamp'], 11, 5).' '.__('on').' '.dateConvertBack($guid, substr($submission['timestamp'], 0, 10))."' $style>$linkText</span>";
        }
    } else {
        if (date('Y-m-d H:i:s') < $markbookColumn['homeworkDueDateTime']) {
            $output .= "<span title='".__('Pending')."'>".__('Pen').'</span>';
        } else {
            if (!empty($student['dateStart']) && $student['dateStart'] > $markbookColumn['lessonDate']) {
                $output .= "<span title='".__('Student joined school after assessment was given.')."' style='color: #000; font-weight: normal; border: 2px none #ff0000; padding: 2px 4px'>NA</span>";
            } else {
                if ($markbookColumn['homeworkSubmissionRequired'] == 'Compulsory') {
                    $output .= "<span title='".__('Incomplete')."' style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px'>".__('Inc').'</span>';
                } else {
                    $output .= "<span title='".__('Not submitted online')."'>".__('NA').'</span>';
                }
            }
        }
    }

    return $output;
}