<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff;

use Pupilsight\Services\BackgroundProcess;
use Pupilsight\Domain\System\SettingGateway;
use Pupilsight\Domain\Messenger\GroupGateway;
use Pupilsight\Module\Staff\MessageSender;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Domain\Staff\StaffCoverageDateGateway;
use Pupilsight\Domain\Staff\SubstituteGateway;
use Pupilsight\Module\Staff\Messages\NewCoverage;
use Pupilsight\Module\Staff\Messages\CoverageAccepted;
use Pupilsight\Module\Staff\Messages\CoveragePartial;
use Pupilsight\Module\Staff\Messages\CoverageCancelled;
use Pupilsight\Module\Staff\Messages\CoverageDeclined;
use Pupilsight\Module\Staff\Messages\IndividualRequest;
use Pupilsight\Module\Staff\Messages\BroadcastRequest;
use Pupilsight\Module\Staff\Messages\NoCoverageAvailable;

/**
 * CoverageNotificationProcess
 *
 * @version v18
 * @since   v18
 */
class CoverageNotificationProcess extends BackgroundProcess
{
    protected $staffCoverageGateway;
    protected $staffCoverageDateGateway;
    protected $substituteGateway;
    protected $groupGateway;

    protected $messageSender;
    protected $urgencyThreshold;
    protected $organisationHR;

    public function __construct(
        StaffCoverageGateway $staffCoverageGateway,
        StaffCoverageDateGateway $staffCoverageDateGateway,
        SubstituteGateway $substituteGateway,
        GroupGateway $groupGateway,
        SettingGateway $settingGateway,
        MessageSender $messageSender
    ) {
        $this->staffCoverageGateway = $staffCoverageGateway;
        $this->staffCoverageDateGateway = $staffCoverageDateGateway;
        $this->substituteGateway = $substituteGateway;
        $this->groupGateway = $groupGateway;
        $this->messageSender = $messageSender;

        $this->urgentNotifications = $settingGateway->getSettingByScope('Staff', 'urgentNotifications');
        $this->urgencyThreshold = intval($settingGateway->getSettingByScope('Staff', 'urgencyThreshold')) * 86400;
        $this->organisationHR = $settingGateway->getSettingByScope('System', 'organisationHR');
    }

    public function runIndividualRequest($pupilsightStaffCoverageID)
    {
        $coverage = $this->getCoverageDetailsByID($pupilsightStaffCoverageID);
        if (empty($coverage)) return false;

        $recipients = [$coverage['pupilsightPersonIDCoverage']];
        $message = new IndividualRequest($coverage);

        if ($sent = $this->messageSender->send($message, $recipients, $coverage['pupilsightPersonID'])) {
            $this->staffCoverageGateway->update($pupilsightStaffCoverageID, [
                'notificationSent' => 'Y',
                'notificationList' => json_encode($recipients),
            ]);
        }

        return $sent;
    }

    public function runBroadcastRequest($pupilsightStaffCoverageID)
    {
        $coverage = $this->getCoverageDetailsByID($pupilsightStaffCoverageID);
        if (empty($coverage)) return false;

        $coverageDates = $this->staffCoverageDateGateway->selectDatesByCoverage($pupilsightStaffCoverageID)->fetchAll();

        // Get available subs
        $availableSubs = [];
        foreach ($coverageDates as $date) {
            $criteria = $this->substituteGateway
                ->newQueryCriteria()
                ->filterBy('substituteTypes', $coverage['substituteTypes']);
            $availableByDate = $this->substituteGateway->queryAvailableSubsByDate($criteria, $date['date'])->toArray();
            $availableSubs = array_merge($availableSubs, $availableByDate);
        }
        
        if (count($availableSubs) > 0) {
            // Send messages to available subs
            $recipients = array_column($availableSubs, 'pupilsightPersonID');
            $message = new BroadcastRequest($coverage);
        } else {
            // Send a message to admin - no coverage
            $recipients = [$this->organisationHR];
            $message = new NoCoverageAvailable($coverage);
        }

        if ($sent = $this->messageSender->send($message, $recipients, $coverage['pupilsightPersonID'])) {
            $this->staffCoverageGateway->update($pupilsightStaffCoverageID, [
                'notificationSent' => 'Y',
                'notificationList' => json_encode($recipients),
            ]);
        }

        return $sent;
    }

    public function runCoverageAccepted($pupilsightStaffCoverageID, $uncoveredDates = [])
    {
        $coverage = $this->getCoverageDetailsByID($pupilsightStaffCoverageID);
        if (empty($coverage)) return false;

        // Send the coverage accepted message to the requesting staff member
        $recipients = [$coverage['pupilsightPersonIDStatus']];
        $message = !empty($uncoveredDates)
            ? new CoveragePartial($coverage, $uncoveredDates)
            : new CoverageAccepted($coverage);

        $sent = $this->messageSender->send($message, $recipients, $coverage['pupilsightPersonIDCoverage']);

        // Send a coverage arranged message to the selected staff for this absence
        if (!empty($coverage['pupilsightStaffAbsenceID'])) {
            $recipients = !empty($coverage['notificationListAbsence']) ? json_decode($coverage['notificationListAbsence']) : [];
            
            // Add the absent person, if this coverage request was created by someone else
            if ($coverage['pupilsightPersonID'] != $coverage['pupilsightPersonIDStatus']) {
                $recipients[] = $coverage['pupilsightPersonID'];
            }

            // Add the notification group members, if selected
            if (!empty($coverage['pupilsightGroupID'])) {
                $groupRecipients = $this->groupGateway->selectPersonIDsByGroup($coverage['pupilsightGroupID'])->fetchAll(\PDO::FETCH_COLUMN, 0);
                $recipients = array_merge($recipients, $groupRecipients);
            }

            $message = new NewCoverage($coverage);
            $sent += $this->messageSender->send($message, $recipients, $coverage['pupilsightPersonID']);
        }

        return $sent;
    }

    public function runCoverageDeclined($pupilsightStaffCoverageID)
    {
        $coverage = $this->getCoverageDetailsByID($pupilsightStaffCoverageID);
        if (empty($coverage)) return false;

        $recipients = [$coverage['pupilsightPersonIDStatus']];
        $message = new CoverageDeclined($coverage);

        return $this->messageSender->send($message, $recipients, $coverage['pupilsightPersonIDCoverage']);
    }

    public function runCoverageCancelled($pupilsightStaffCoverageID)
    {
        $coverage = $this->getCoverageDetailsByID($pupilsightStaffCoverageID);
        if (empty($coverage)) return false;

        $recipients = [$coverage['pupilsightPersonIDCoverage']];
        $message = new CoverageCancelled($coverage);

        return $this->messageSender->send($message, $recipients, $coverage['pupilsightPersonID']);
    }
    
    private function getCoverageDetailsByID($pupilsightStaffCoverageID)
    {
        if ($coverage = $this->staffCoverageGateway->getCoverageDetailsByID($pupilsightStaffCoverageID)) {
            if ($this->urgentNotifications == 'Y') {
                $relativeSeconds = strtotime($coverage['dateStart']) - time();
                $coverage['urgent'] = $relativeSeconds <= $this->urgencyThreshold;
            } else {
                $coverage['urgent'] = false;
            }
        }

        return $coverage ?? [];
    }
}
