<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\View;

use Pupilsight\View\Page;
use Pupilsight\Services\Format;
use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Module\Staff\View\CoverageView;

/**
 * AbsenceView
 *
 * A view composer class: receives an absence ID and displays the status information for the absence.
 *
 * @version v18
 * @since   v18
 */
class AbsenceView
{
    protected $staffAbsenceGateway;
    protected $userGateway;
    protected $staffCoverageGateway;
    protected $coverageView;

    public function __construct(StaffAbsenceGateway $staffAbsenceGateway, StaffCoverageGateway $staffCoverageGateway, UserGateway $userGateway, CoverageView $coverageView)
    {
        $this->staffAbsenceGateway = $staffAbsenceGateway;
        $this->userGateway = $userGateway;
        $this->staffCoverageGateway = $staffCoverageGateway;
        $this->coverageView = $coverageView;
    }

    public function setAbsence($pupilsightStaffAbsenceID, $pupilsightPersonIDViewing)
    {
        $this->pupilsightStaffAbsenceID = $pupilsightStaffAbsenceID;
        $this->pupilsightPersonIDViewing = $pupilsightPersonIDViewing;

        return $this;
    }

    public function compose(Page $page)
    {
        $absence = $this->staffAbsenceGateway->getAbsenceDetailsByID($this->pupilsightStaffAbsenceID);
        if (empty($absence)) return;

        $person = $this->userGateway->getByID($absence['pupilsightPersonIDCreator']);
        $canViewConfidential = $absence['pupilsightPersonIDApproval'] == $this->pupilsightPersonIDViewing || $absence['pupilsightPersonID'] == $this->pupilsightPersonIDViewing;
        
        // Absence Details
        $page->writeFromTemplate('statusComment.twig.html', [
            'name'    => Format::name($person['title'], $person['preferredName'], $person['surname'], 'Staff', false, true),
            'action'   => !empty($absence['pupilsightPersonIDApproval'])? __('Requested Leave') : __('Submitted Leave'),
            'photo'   => $person['image_240'],
            'date'    => Format::relativeTime($absence['timestampCreator']),
            'comment' => $absence['comment'],
            'message' => $canViewConfidential && !empty($absence['commentConfidential']) ? __('Confidential Comment').': '.$absence['commentConfidential'] : '',
        ]);

        // Approval Details
        if (!empty($absence['pupilsightPersonIDApproval'])) {
            $approver = $this->userGateway->getByID($absence['pupilsightPersonIDApproval']);
            $page->writeFromTemplate('statusComment.twig.html', [
                'name'    => Format::name($approver['title'], $approver['preferredName'], $approver['surname'], 'Staff', false, true),
                'action'  => $absence['status'] != 'Pending Approval' ? __($absence['status']) : '',
                'photo'   => $approver['image_240'],
                'date'    => Format::relativeTime($absence['timestampApproval']),
                'status'  => __($absence['status']),
                'tag'     => $this->getStatusColor($absence['status']),
                'comment' => $canViewConfidential ? $absence['notesApproval'] : '',
            ]);
        }

        $coverageList = $this->staffCoverageGateway->selectCoverageByAbsenceID($absence['pupilsightStaffAbsenceID'])->fetchAll();
        
        // Coverage Details
        if (!empty($coverageList)) {
            foreach ($coverageList as $coverage) {
                $this->coverageView->setCoverage($coverage['pupilsightStaffCoverageID'])->compose($page);
            }
        }
    }

    protected function getStatusColor($status)
    {
        switch ($status) {
            case 'Approved':
                return 'success';

            case 'Declined':
                return 'error';

            default:
                return 'message';
        }
    }
}
