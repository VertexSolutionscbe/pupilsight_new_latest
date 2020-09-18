<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\Messages;

use Pupilsight\Module\Staff\Message;
use Pupilsight\Services\Format;

class CoverageCancelled extends Message
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
        return __('Coverage Cancelled');
    }

    public function getText() : string
    {
        return __("{name}'s coverage request for {date} has been cancelled.", [
            'date' => Format::dateRangeReadable($this->coverage['dateStart'], $this->coverage['dateEnd']),
            'name' => Format::name($this->coverage['titleAbsence'], $this->coverage['preferredNameAbsence'], $this->coverage['surnameAbsence'], 'Staff', false, true),
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
        return __('View Details');
    }

    public function getLink() : string
    {
        return 'index.php?q=/modules/Staff/coverage_view_details.php&pupilsightStaffCoverageID='.$this->coverage['pupilsightStaffCoverageID'];
    }
}
