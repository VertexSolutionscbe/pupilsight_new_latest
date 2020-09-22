<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\View;

use Pupilsight\Domain\Staff\StaffGateway;
use Pupilsight\Domain\RollGroups\RollGroupGateway;
use Pupilsight\Contracts\Services\Session;
use Pupilsight\Contracts\Database\Connection;
use Pupilsight\View\Page;

/**
 * StaffCard
 * 
 * A view composer class for the staff card template: set a pupilsightPersonID and display the staff details and links to their info.
 *
 * @version v18
 * @since   v18
 */
class StaffCard
{
    protected $session;
    protected $db;
    protected $staffGateway;
    protected $rollGroupGateway;
    protected $pupilsightPersonID;
    protected $status;
    protected $tag;

    public function __construct(Session $session, Connection $db, StaffGateway $staffGateway, RollGroupGateway $rollGroupGateway)
    {
        $this->session = $session;
        $this->db = $db;
        $this->staffGateway = $staffGateway;
        $this->rollGroupGateway = $rollGroupGateway;
    }

    public function setPerson($pupilsightPersonID)
    {
        $this->pupilsightPersonID = $pupilsightPersonID;

        return $this;
    }

    public function setStatus($status, $tag = '')
    {
        $this->status = $status;
        $this->tag = $tag;

        return $this;
    }

    public function compose(Page $page)
    {
        $guid = $this->session->get('guid');
        $connection2 = $this->db->getConnection();

        $page->writeFromTemplate('staffCard.twig.html', [
            'staff'             => $this->staffGateway->selectStaffByID($this->pupilsightPersonID ?? '')->fetch(),
            'rollGroup'         => $this->rollGroupGateway->selectRollGroupsByTutor($this->pupilsightPersonID ?? '')->fetch(),
            'canViewProfile'    => isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php'),
            'canViewAbsences'   => isActionAccessible($guid, $connection2, '/modules/Staff/absences_view_byPerson.php', 'View Absences_any'),
            'canViewTimetable'  => isActionAccessible($guid, $connection2, '/modules/Timetable/tt_view.php'),
            'canViewRollGroups' => isActionAccessible($guid, $connection2, '/modules/Roll Groups/rollGroups.php'),
            'status'            => $this->status,
            'tag'               => $this->tag,
        ]);
    }
}
