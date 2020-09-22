<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\View;

use Pupilsight\View\Page;
use Pupilsight\Services\Format;
use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\Staff\StaffCoverageGateway;


/**
 * CoverageView
 *
 * A view composer class: receives a coverage ID and displays the status information.
 *
 * @version v18
 * @since   v18
 */
class CoverageView
{
    protected $staffCoverageGateway;
    protected $userGateway;

    public function __construct(StaffCoverageGateway $staffCoverageGateway, UserGateway $userGateway)
    {
        $this->staffCoverageGateway = $staffCoverageGateway;
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
        if (empty($coverage)) return;

        $requester = $this->userGateway->getByID($coverage['pupilsightPersonIDStatus']);
        $substitute = !empty($coverage['pupilsightPersonIDCoverage'])
            ? $this->userGateway->getByID($coverage['pupilsightPersonIDCoverage'])
            : null;

        if ($coverage['status'] == 'Requested') {
            if ($coverage['requestType'] == 'Individual') {
                $params = [
                    'type' => __('Individual'),
                    'name' => Format::name($substitute['title'], $substitute['preferredName'], $substitute['surname'], 'Staff', false, true),
                ];
            } elseif ($coverage['requestType'] == 'Broadcast') {
                if ($notificationList = json_decode($coverage['notificationList'])) {
                    $notified = $this->userGateway->selectNotificationDetailsByPerson($notificationList)->fetchGroupedUnique();
                    $notified = Format::nameList($notified, 'Staff', false, true, ', ');
                }

                $params = [
                    'type' => $coverage['substituteTypes'] ?? __('Open'),
                    'name' => $notified ?? __('Pending'),
                ];
            }
            $message = __('{type} request sent to {name}', $params);
        }

        // Coverage Request
        $page->writeFromTemplate('statusComment.twig.html', [
            'name'    => Format::name($requester['title'], $requester['preferredName'], $requester['surname'], 'Staff', false, true),
            'action'   => __('Requested Coverage'),
            'photo'   => $requester['image_240'],
            'date'    => Format::relativeTime($coverage['timestampStatus']),
            'status'  => $coverage['status'] == 'Requested' || $coverage['status'] == 'Cancelled' ? __($coverage['status']) : '',
            'tag'     => $this->getStatusColor($coverage['status']),
            'comment' => $coverage['notesStatus'],
            'message' => $message ?? '',
        ]);

        // Coverage Reply
        if ($substitute && ($coverage['status'] == 'Accepted' || $coverage['status'] == 'Declined')) {
            $page->writeFromTemplate('statusComment.twig.html', [
                'name'    => Format::name($substitute['title'], $substitute['preferredName'], $substitute['surname'], 'Staff', false, true),
                'action'  => __($coverage['status']),
                'photo'   => $substitute['image_240'],
                'date'    => Format::relativeTime($coverage['timestampCoverage']),
                'status'  => __($coverage['status']),
                'tag'     => $this->getStatusColor($coverage['status']),
                'comment' => $coverage['notesCoverage'],
            ]);
        }
    }

    protected function getStatusColor($status)
    {
        switch ($status) {
            case 'Accepted':
                return 'success';

            case 'Declined':
                return 'error';

            case 'Cancelled':
                return 'dull';

            default:
                return 'message';
        }
    }
}
