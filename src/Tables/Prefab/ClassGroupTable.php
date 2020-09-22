<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\Prefab;

use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Tables\View\GridView;
use Pupilsight\Forms\Input\Checkbox;
use Pupilsight\Contracts\Services\Session;
use Pupilsight\Contracts\Database\Connection;
use Pupilsight\Domain\Timetable\CourseEnrolmentGateway;

/**
 * ClassGroupTable
 *
 * @version v18
 * @since   v18
 */
class ClassGroupTable extends DataTable
{
    protected $db;
    protected $session;
    protected $enrolmentGateway;

    public function __construct(GridView $renderer, CourseEnrolmentGateway $enrolmentGateway, Connection $db, Session $session)
    {
        parent::__construct($renderer);

        $this->db = $db;
        $this->session = $session;
        $this->enrolmentGateway = $enrolmentGateway;
    }

    public function build($pupilsightSchoolYearID, $pupilsightCourseClassID)
    {
        $guid = $this->session->get('guid');
        $connection2 = $this->db->getConnection();

        $highestAction = getHighestGroupedAction($guid, '/modules/Students/student_view_details.php', $connection2);

        $canViewStaff = isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php');
        $canViewStudents = ($highestAction == 'View Student Profile_brief' || $highestAction == 'View Student Profile_full' || $highestAction == 'View Student Profile_fullNoNotes');
        $canViewConfidential = $highestAction == 'View Student Profile_full' || $highestAction == 'View Student Profile_fullNoNotes';

        $criteria = $this->enrolmentGateway
            ->newQueryCriteria()
            ->sortBy(['roleSortOrder', 'surname', 'preferredName'])
            ->filterBy('nonStudents', !$canViewStudents)
            ->pageSize(0);

        $participants = $this->enrolmentGateway->queryCourseEnrolmentByClass($criteria, $pupilsightSchoolYearID, $pupilsightCourseClassID);
        $this->withData($participants);

        $this->setTitle(__('Participants'));

        $this->addMetaData('gridClass', 'rounded-sm bg-blue-100 border');
        $this->addMetaData('gridItemClass', 'w-1/2 sm:w-1/3 md:w-1/5 my-2 sm:my-4 text-center');

        if ($canViewConfidential) {
            $this->addHeaderAction('export', __('Export to Excel'))
                ->setURL('/modules/Departments/department_course_classExport.php')
                ->addParam('pupilsightCourseClassID', $pupilsightCourseClassID)
                ->addParam('address', $_GET['q'])
                ->setIcon('download')
                ->directLink()
                ->displayLabel();
        }

        if ($canViewConfidential) {
            $checkbox = (new Checkbox('confidential'.$pupilsightCourseClassID))
                ->description(__('Show Confidential Data'))
                ->checked(true)
                ->inline()
                ->wrap('<div class="mt-2 text-right text-xxs text-gray italic">', '</div>');

            $this->addMetaData('gridHeader', $checkbox->getOutput());
            $this->addMetaData('gridFooter', $this->getCheckboxScript($pupilsightCourseClassID));

            $this->addColumn('alerts')
                ->format(function ($person) use ($guid, $connection2, $pupilsightCourseClassID) {
                    $divExtras = ' data-conf="confidential'.$pupilsightCourseClassID.'"';
                    return getAlertBar($guid, $connection2, $person['pupilsightPersonID'], $person['privacy'], $divExtras);
                });
        }

        $this->addColumn('image_240')
            ->setClass('relative')
            ->format(function ($person) {
                return Format::userPhoto($person['image_240'], 'md', '').
                       Format::userBirthdayIcon($person['dob'], $person['preferredName']);
            });
            
        $this->addColumn('name')
            ->setClass('text-xs font-bold mt-1')
            ->format(function ($person) use ($canViewStaff, $canViewStudents) {
                if ($person['role'] == 'Student') {
                    $name = Format::name($person['title'], $person['preferredName'], $person['surname'], 'Student', false, true);
                    $url =  './index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$person['pupilsightPersonID'];
                    $canViewProfile = $canViewStudents;
                } else {
                    $name = Format::name($person['title'], $person['preferredName'], $person['surname'], 'Staff', false, false);
                    $url = './index.php?q=/modules/Staff/staff_view_details.php&pupilsightPersonID='.$person['pupilsightPersonID'];
                    $canViewProfile = $canViewStaff;
                }

                return $canViewProfile
                    ? Format::link($url, $name)
                    : $name;
            });

        $this->addColumn('role')
            ->setClass('text-xs text-gray italic leading-snug')
            ->translatable();
    }

    private function getCheckboxScript($id)
    {
        return '
        <script type="text/javascript">
        $(function () {
            $("#confidential'.$id.'").click(function () {
                $("[data-conf=\'confidential'.$id.'\']").slideToggle(!$(this).is(":checked"));
            });
        });
        </script>';
    }
}
