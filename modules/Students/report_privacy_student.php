<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Tables\Prefab\ReportTable;
use Pupilsight\Domain\Students\StudentReportGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_privacy_student.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $viewMode = $_REQUEST['format'] ?? '';
    $pupilsightSchoolYearID = $pupilsight->session->get('pupilsightSchoolYearID');

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Privacy Choices by Student'));
    }

    
    $privacy = getSettingByScope($connection2, 'User Admin', 'privacy');
    $privacyOptions = array_map('trim', explode(',', getSettingByScope($connection2, 'User Admin', 'privacyOptions')));

    if (count($privacyOptions) < 1 or $privacy == 'N') {
        $page->addMessage(__('There are no privacy options in place.'));
        return;
    }

    $reportGateway = $container->get(StudentReportGateway::class);

    // CRITERIA
    $criteria = $reportGateway->newQueryCriteria()
        ->sortBy(['pupilsightYearGroup.sequenceNumber', 'pupilsightRollGroup.nameShort'])
        ->pageSize(0)
        ->fromPOST();

    $privacyChoices = $reportGateway->queryStudentPrivacyChoices($criteria, $pupilsightSchoolYearID);

    // DATA TABLE
    $table = ReportTable::createPaginated('privacyByStudent', $criteria)->setViewMode($viewMode, $pupilsight->session);
    $table->setTitle(__('Privacy Choices by Student'));

    $table->addRowCountColumn($privacyChoices->getPageFrom());
    $table->addColumn('rollGroup', __('Roll Group'))
        ->context('secondary')
        ->sortable(['pupilsightYearGroup.sequenceNumber', 'rollGroup']);

    $table->addColumn('image_240', __('Student'))
        ->context('primary')
        ->width('10%')
        ->sortable(['surname', 'preferredName'])
        ->format(function ($student) use ($guid) {
            $name = Format::name('', $student['preferredName'], $student['surname'], 'Student', true);
            $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$student['pupilsightPersonID'];

            return Format::userPhoto($student['image_240']).'<br/>'.Format::link($url, $name);
        });

    $privacyColumn = $table->addColumn('privacy', __('Privacy'));
    foreach ($privacyOptions as $index => $privacyOption) {
        $privacyColumn->addColumn('privacy'.$index, $privacyOption)
            ->context('primary')
            ->notSortable()
            ->format(function ($student) use ($privacyOption, $guid) {
                $studentPrivacy = array_map('trim', explode(',', $student['privacy']));
                return in_array($privacyOption, $studentPrivacy) 
                    ? "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/> ".__('Required')
                    : '';
            });
    }

    echo $table->render($privacyChoices);
}
