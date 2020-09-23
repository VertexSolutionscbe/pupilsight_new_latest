<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\Messages;

use Pupilsight\Module\Staff\Message;
use Pupilsight\Services\Format;

class NewCoverage extends Message
{
    protected $coverage;
    protected $details;

    public function __construct($coverage)
    {
        $this->coverage = $coverage;
        $this->details = [
            'nameAbsent'   => Format::name($coverage['titleAbsence'], $coverage['preferredNameAbsence'], $coverage['surnameAbsence'], 'Staff', false, true),
            'nameCoverage' => Format::name($coverage['titleCoverage'], $coverage['preferredNameCoverage'], $coverage['surnameCoverage'], 'Staff', false, true),
            'date'         => Format::dateRangeReadable($coverage['dateStart'], $coverage['dateEnd']),
            'time'         => $coverage['allDay'] == 'Y' ? __('All Day') : Format::timeRange($coverage['timeStart'], $coverage['timeEnd']),
            'type'         => trim($coverage['type'].' '.$coverage['reason']),
        ];
    }

    public function via() : array
    {
        return $this->coverage['urgent']
            ? ['database', 'mail']
            : ['database', 'mail'];
    }

    public function getTitle() : string
    {
        return __('Staff Coverage');
    }

    public function getText() : string
    {
        return __("{nameAbsent} arranged for {nameCoverage} to cover their {type} absence on {date}.", $this->details);
    }

    public function getDetails() : array
    {
        return [
            __('Staff')      => $this->details['nameAbsent'],
            __('Type')       => $this->details['type'],
            __('Date')       => $this->details['date'],
            __('Time')       => $this->details['time'],
            __('Comment')    => $this->coverage['notesStatus'],
            __('Substitute') => $this->details['nameCoverage'],
            __('Reply')      => $this->coverage['notesCoverage'],
        ];
    }

    public function getModule() : string
    {
        return 'Staff';
    }

    public function getAction() : string
    {
        return __('View Details');
    }

    public function getLink() : string
    {
        return 'index.php?q=/modules/Staff/coverage_view_details.php&pupilsightStaffCoverageID='.$this->coverage['pupilsightStaffCoverageID'];
    }
}
