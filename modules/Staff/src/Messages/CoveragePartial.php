<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\Messages;

use Pupilsight\Module\Staff\Message;
use Pupilsight\Services\Format;

class CoveragePartial extends Message
{
    protected $coverage;
    protected $uncoveredDates;

    public function __construct($coverage, $uncoveredDates)
    {
        $this->coverage = $coverage;

        $this->uncoveredDates = array_map(function ($date) {
            return Format::dateReadable($date, '%b %e');
        }, $uncoveredDates);
    }

    public function via() : array
    {
        return $this->coverage['urgent']
            ? ['database', 'mail', 'sms']
            : ['database', 'mail'];
    }

    public function getTitle() : string
    {
        return __('Coverage Partially Accepted');
    }

    public function getText() : string
    {
        return __("{name} has partially accepted your coverage request for {date}. They are unavailable to cover {otherDates}.", [
            'date' => Format::dateRangeReadable($this->coverage['dateStart'], $this->coverage['dateEnd']),
            'name' => Format::name($this->coverage['titleCoverage'], $this->coverage['preferredNameCoverage'], $this->coverage['surnameCoverage'], 'Staff', false, true),
            'otherDates' => implode(', ', $this->uncoveredDates),
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
