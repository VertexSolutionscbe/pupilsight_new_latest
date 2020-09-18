<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\Messages;

use Pupilsight\Module\Staff\Message;
use Pupilsight\Services\Format;

class CoverageDeclined extends Message
{
    protected $coverage;

    public function __construct($coverage)
    {
        $this->coverage = $coverage;
    }

    public function via() : array
    {
        return $this->coverage['urgent']
            ? ['database', 'mail', 'sms']
            : ['database', 'mail'];
    }

    public function getTitle() : string
    {
        return __('Coverage Unavailable');
    }

    public function getText() : string
    {
        return __("Unfortunately {name} isn't available to cover your {type} absence on {date}.", [
            'date' => Format::dateRangeReadable($this->coverage['dateStart'], $this->coverage['dateEnd']),
            'name' => Format::name($this->coverage['titleCoverage'], $this->coverage['preferredNameCoverage'], $this->coverage['surnameCoverage'], 'Staff', false, true),
            'type' => $this->coverage['type'],
        ]);
    }

    public function getDetails() : array
    {
        return [
            __('Reply') => $this->coverage['notesCoverage'],
        ];
    }

    public function getModule() : string
    {
        return __('Staff');
    }

    public function getAction() : string
    {
        return __('New Coverage Request');
    }

    public function getLink() : string
    {
        return 'index.php?q=/modules/Staff/coverage_request.php&pupilsightStaffAbsenceID='.$this->coverage['pupilsightStaffAbsenceID'];
    }
}
