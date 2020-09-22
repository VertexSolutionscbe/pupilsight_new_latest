<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff;

use Pupilsight\Services\BackgroundProcess;
use Pupilsight\Domain\System\SettingGateway;
use Pupilsight\Domain\Messenger\GroupGateway;
use Pupilsight\Module\Staff\MessageSender;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Module\Staff\Messages\NewAbsence;
use Pupilsight\Module\Staff\Messages\AbsenceApproval;
use Pupilsight\Module\Staff\Messages\AbsencePendingApproval;

/**
 * AbsenceNotificationProcess
 *
 * @version v18
 * @since   v18
 */
class AbsenceNotificationProcess extends BackgroundProcess
{
    protected $staffAbsenceGateway;
    protected $groupGateway;

    protected $messageSender;
    protected $urgencyThreshold;

    public function __construct(StaffAbsenceGateway $staffAbsenceGateway, GroupGateway $groupGateway, SettingGateway $settingGateway, MessageSender $messageSender)
    {
        $this->staffAbsenceGateway = $staffAbsenceGateway;
        $this->groupGateway = $groupGateway;
        $this->messageSender = $messageSender;

        $this->urgentNotifications = $settingGateway->getSettingByScope('Staff', 'urgentNotifications');
        $this->urgencyThreshold = intval($settingGateway->getSettingByScope('Staff', 'urgencyThreshold')) * 86400;
    }
    
    /**
     * Sends a message to alert users of a new absence in the system. Includes anyone selected in a notification
     * group or optional additional list of people to notify.
     *
     * @param string $pupilsightStaffAbsenceID
     * @return array
     */
    public function runNewAbsence($pupilsightStaffAbsenceID)
    {
        $absence = $this->getAbsenceDetailsByID($pupilsightStaffAbsenceID);
        if (empty($absence)) return false;

        $message = new NewAbsence($absence);

        // Target the absence message to the selected staff
        $recipients = !empty($absence['notificationList']) ? json_decode($absence['notificationList']) : [];

        // Add the notification group members, if selected
        if (!empty($absence['pupilsightGroupID'])) {
            $groupRecipients = $this->groupGateway->selectPersonIDsByGroup($absence['pupilsightGroupID'])->fetchAll(\PDO::FETCH_COLUMN, 0);
            $recipients = array_merge($recipients, $groupRecipients);
        }

        // Add the absent person, if this was created by someone else
        if ($absence['pupilsightPersonID'] != $absence['pupilsightPersonIDCreator']) {
            $recipients[] = $absence['pupilsightPersonID'];
        }

        if ($sent = $this->messageSender->send($message, $recipients, $absence['pupilsightPersonID'])) {
            $this->staffAbsenceGateway->update($pupilsightStaffAbsenceID, [
                'notificationSent' => 'Y',
            ]);
        }

        return $sent;
    }

    /**
     * Sends a message back to a staff member that their absence was approved (or declined).
     *
     * @param string $pupilsightStaffAbsenceID
     * @return array
     */
    public function runAbsenceApproval($pupilsightStaffAbsenceID)
    {
        $absence = $this->getAbsenceDetailsByID($pupilsightStaffAbsenceID);
        if (empty($absence)) return false;
        
        $message = new AbsenceApproval($absence);
        $recipients = [$absence['pupilsightPersonID']];

        return $this->messageSender->send($message, $recipients, $absence['pupilsightPersonIDApproval']);
    }

    /**
     * Sends a message to the selected approval to notify them of a new absence neeing approval.
     *
     * @param string $pupilsightStaffAbsenceID
     * @return array
     */
    public function runAbsencePendingApproval($pupilsightStaffAbsenceID)
    {
        $absence = $this->getAbsenceDetailsByID($pupilsightStaffAbsenceID);
        if (empty($absence)) return false;

        $message = new AbsencePendingApproval($absence);
        $recipients = [$absence['pupilsightPersonIDApproval']];

        return $this->messageSender->send($message, $recipients, $absence['pupilsightPersonID']);
    }

    /**
     * Gets the absence details from a gateway and appends the urgency information based on the Staff settings.
     *
     * @param string $pupilsightStaffAbsenceID
     * @return array
     */
    private function getAbsenceDetailsByID($pupilsightStaffAbsenceID)
    {
        if ($absence = $this->staffAbsenceGateway->getAbsenceDetailsByID($pupilsightStaffAbsenceID)) {
            if ($this->urgentNotifications == 'Y') {
                $relativeSeconds = strtotime($absence['dateStart']) - time();
                $absence['urgent'] = $relativeSeconds <= $this->urgencyThreshold;
            } else {
                $absence['urgent'] = false;
            }
        }

        return $absence ?? [];
    }
}
