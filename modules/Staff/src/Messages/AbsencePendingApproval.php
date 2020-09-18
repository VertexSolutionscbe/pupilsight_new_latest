<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\Messages;

use Pupilsight\Module\Staff\Message;
use Pupilsight\Services\Format;

class AbsencePendingApproval extends Message
{
    protected $absence;
    protected $details;

    public function __construct($absence)
    {
        $this->absence = $absence;
        $this->details = [
            'name' => Format::name($absence['titleAbsence'], $absence['preferredNameAbsence'], $absence['surnameAbsence'], 'Staff', false, true),
            'date' => Format::dateRangeReadable($absence['dateStart'], $absence['dateEnd']),
            'time' => $absence['allDay'] == 'Y' ? __('All Day') : Format::timeRange($absence['timeStart'], $absence['timeEnd']),
            'type' => trim($absence['type'].' '.$absence['reason']),
        ];
    }

    public function via() : array
    {
        return $this->absence['urgent']
            ? ['database', 'mail']
            : ['database', 'mail'];
    }

    public function getTitle() : string
    {
        return __('Staff Absence').' '.$this->absence['status'];
    }

    public function getText() : string
    {
        return __("{name} is requesting leave on {date} for {type}. You may choose to approve or decline this request.", $this->details);
    }

    public function getDetails() : array
    {
        $details = [
            __('Staff')   => $this->details['name'],
            __('Type')    => $this->details['type'],
            __('Date')    => $this->details['date'],
            __('Time')    => $this->details['time'],
        ];
        
        $details += !empty($this->absence['commentConfidential'])
            ? [__('Confidential Comment') => $this->absence['commentConfidential']]
            : [__('Comment') => $this->absence['comment']];

        return $details;
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
        return 'index.php?q=/modules/Staff/absences_approval.php&pupilsightStaffAbsenceID='.$this->absence['pupilsightStaffAbsenceID'];
    }
}
