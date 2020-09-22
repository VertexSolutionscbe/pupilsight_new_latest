<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\Messages;

use Pupilsight\Module\Staff\Message;
use Pupilsight\Services\Format;

class AbsenceApproval extends Message
{
    protected $absence;
    protected $details;

    public function __construct($absence)
    {
        $this->absence = $absence;
        $this->details = [
            'name'     => Format::name($absence['titleApproval'], $absence['preferredNameApproval'], $absence['surnameApproval'], 'Staff', false, true),
            'date'     => Format::dateRangeReadable($absence['dateStart'], $absence['dateEnd']),
            'type'     => trim($absence['type'].' '.$absence['reason']),
            'actioned' => strtolower($absence['status']),
        ];
    }

    public function via() : array
    {
        return $this->absence['urgent']
            ? ['database', 'mail', 'sms']
            : ['database', 'mail'];
    }

    public function getTitle() : string
    {
        return __('Staff Absence').' '.$this->absence['status'];
    }

    public function getText() : string
    {
        return __("{name} has {actioned} your {type} absence for {date}.", $this->details);
    }

    public function getDetails() : array
    {
        return [
            __('Date')  => Format::dateTimeReadable($this->absence['timestampApproval']),
            __('Reply') => $this->absence['notesApproval'],
        ];
    }

    public function getModule() : string
    {
        return __('Staff');
    }

    public function getAction() : string
    {
        return __('View Details');
    }

    public function getLink() : string
    {
        return 'index.php?q=/modules/Staff/absences_view_details.php&pupilsightStaffAbsenceID='.$this->absence['pupilsightStaffAbsenceID'];
    }
}
