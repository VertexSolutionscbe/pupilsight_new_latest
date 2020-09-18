<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\View;

use Pupilsight\View\Page;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Domain\RollGroups\RollGroupGateway;

/**
 * CoverageTodayView
 *
 * @version v18
 * @since   v18
 */
class CoverageTodayView
{
    protected $staffCoverageGateway;
    protected $rollGroupGateway;
    protected $userGateway;
    protected $pupilsightStaffCoverageID;

    public function __construct(StaffCoverageGateway $staffCoverageGateway, RollGroupGateway $rollGroupGateway, UserGateway $userGateway)
    {
        $this->staffCoverageGateway = $staffCoverageGateway;
        $this->rollGroupGateway = $rollGroupGateway;
        $this->userGateway = $userGateway;
    }

    public function setCoverage($pupilsightStaffCoverageID)
    {
        $this->pupilsightStaffCoverageID = $pupilsightStaffCoverageID;

        return $this;
    }

    public function compose(Page $page)
    {
        $coverage = $this->staffCoverageGateway->getByID($this->pupilsightStaffCoverageID);

        $page->write('<details class="border  bg-white rounded-b -mt-5 px-4">');
        $page->write('<summary class="py-4 text-gray text-sm cursor-pointer">'.__('View Details').'</summary>');

        // Coverage Request
        $requester = $this->userGateway->getByID($coverage['pupilsightPersonIDStatus']);
        $page->writeFromTemplate('statusComment.twig.html', [
            'name'    => Format::name($requester['title'], $requester['preferredName'], $requester['surname'], 'Staff', false, true),
            'action'   => __('Requested Coverage'),
            'photo'   => $requester['image_240'],
            'date'    => Format::relativeTime($coverage['timestampStatus']),
            'comment' => $coverage['notesStatus'],
        ]);

        // Attachment
        if (!empty($coverage['attachmentType'])) {
            $page->writeFromTemplate('statusComment.twig.html', [
                'name'       => __('Attachment'),
                'icon'       => 'internalAssessment',
                'tag'        => 'dull',
                'status'     => __($coverage['attachmentType']),
                'attachment' => $coverage['attachmentType'] != 'Text' ? Format::link($coverage['attachmentContent']) : '',
                'html'       => $coverage['attachmentType'] == 'Text' ? $coverage['attachmentContent'] : '',
            ]);
        }

        // Roll Group Info
        $rollGroups = $this->rollGroupGateway->selectRollGroupsByTutor($coverage['pupilsightPersonID'])->toDataSet();

        if (count($rollGroups) > 0) {
            $table = DataTable::create('todaysCoverageTimetable');

            $table->addColumn('name', __('Roll Group'))->context('primary');
            $table->addColumn('spaceName', __('Location'))->context('primary');

            $table->addActionColumn()
                ->addParam('pupilsightRollGroupID')
                ->format(function ($values, $actions) {
                    if ($values['attendance'] == 'Y') {
                        $actions->addAction('attendance', __('Take Attendance'))
                            ->setIcon('attendance')
                            ->setURL('/modules/Attendance/attendance_take_byRollGroup.php');
                    }

                    $actions->addAction('view', __('View Details'))
                        ->setURL('/modules/Roll Groups/rollGroups_details.php');
                });

            $page->write($table->render($rollGroups).'<br/>');
        }

        // Timetable Info
        $timetable = $this->staffCoverageGateway->selectTimetableRowsByCoverageDate($this->pupilsightStaffCoverageID, date('Y-m-d'))->toDataSet();

        if (count($timetable) > 0) {
            $table = DataTable::create('todaysCoverageTimetable');

            $table->addColumn('period', __('Period'));
            $table->addColumn('time', __('Time'))->format(Format::using('timeRange', ['timeStart', 'timeEnd']))->context('primary');
            $table->addColumn('class', __('Class'))->format(Format::using('courseClassName', ['courseNameShort', 'className']))->context('secondary');
            $table->addColumn('spaceName', __('Location'))->context('primary');

            $table->addActionColumn()
                ->addParam('pupilsightCourseClassID')
                ->format(function ($values, $actions) {
                    if ($values['attendance'] == 'Y') {
                        $actions->addAction('attendance', __('Take Attendance'))
                            ->setIcon('attendance')
                            ->setURL('/modules/Attendance/attendance_take_byCourseClass.php');
                    }

                    $actions->addAction('view', __('View Details'))
                        ->setURL('/modules/Departments/department_course_class.php');
                });

            $page->write($table->render($timetable).'<br/>');
        }

        $page->write('</details>');
    }
}
