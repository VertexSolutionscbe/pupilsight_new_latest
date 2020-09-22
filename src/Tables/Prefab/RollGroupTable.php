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
use Pupilsight\Domain\Students\StudentGateway;

/**
 * RollGroupTable
 *
 * @version v18
 * @since   v18
 */
class RollGroupTable extends DataTable
{
    protected $db;
    protected $session;
    protected $studentGateway;

    public function __construct(GridView $renderer, StudentGateway $studentGateway, Connection $db, Session $session)
    {
        parent::__construct($renderer);

        $this->db = $db;
        $this->session = $session;
        $this->studentGateway = $studentGateway;
    }

    public function build($pupilsightRollGroupID, $canViewConfidential, $canPrint, $sortBy = 'rollOrder, surname, preferredName')
    {
        $guid = $this->session->get('guid');
        $connection2 = $this->db->getConnection();

        $highestAction = getHighestGroupedAction($guid, '/modules/Students/student_view_details.php', $connection2);
        
        $canViewStudents = ($highestAction == 'View Student Profile_brief' || $highestAction == 'View Student Profile_full' || $highestAction == 'View Student Profile_fullNoNotes');
        
        if ($canViewConfidential && !$canViewStudents) {
            $canViewConfidential = false;
        }

        if ($canPrint && isActionAccessible($guid, $connection2, '/modules/Students/report_students_byRollGroup_print.php') == false) {
            $canPrint = false;
        }

        $sortByArray = is_array($sortBy) ? $sortBy : array_map('trim', explode(',', $sortBy));
        $criteria = $this->studentGateway
            ->newQueryCriteria()
            ->sortBy($sortByArray)
            ->pageSize(0);

        $students = $this->studentGateway->queryStudentEnrolmentByRollGroup($criteria, $pupilsightRollGroupID);
        $this->withData($students);

        $this->setTitle(__('Students'));

        $this->addMetaData('gridClass', 'rounded-sm bg-blue-100 border');
        $this->addMetaData('gridItemClass', 'w-1/2 sm:w-1/3 md:w-1/5 my-2 sm:my-4 text-center');
        

        if ($canPrint) {
            $this->addHeaderAction('print', __('Print'))
                ->setURL('/report.php')
                ->addParam('q', '/modules/Students/report_students_byRollGroup_print.php')
                ->addParam('pupilsightRollGroupID', $pupilsightRollGroupID)
                ->addParam('view', 'Basic')
                ->setIcon('print')
                ->setTarget('_blank')
                ->directLink()
                ->displayLabel();
        }

        if ($canViewConfidential) {
            $checkbox = (new Checkbox('confidential'.$pupilsightRollGroupID))
                ->description(__('Show Confidential Data'))
                ->checked(true)
                ->inline()
                ->wrap('<div class="mt-2 text-right text-xxs text-gray italic">', '</div>');

            $this->addMetaData('gridHeader', $checkbox->getOutput());
            $this->addMetaData('gridFooter', $this->getCheckboxScript($pupilsightRollGroupID));

            $this->addColumn('alerts')
                ->format(function ($person) use ($guid, $connection2, $pupilsightRollGroupID) {
                    $divExtras = ' data-conf="confidential'.$pupilsightRollGroupID.'"';
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
            ->format(function ($person) use ($canViewStudents) {
                $name = Format::name($person['title'], $person['preferredName'], $person['surname'], 'Student', false, true);
                $url =  './index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$person['pupilsightPersonID'];

                return $canViewStudents
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
