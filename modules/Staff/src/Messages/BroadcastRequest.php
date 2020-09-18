<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\Messages;

use Pupilsight\Module\Staff\Message;
use Pupilsight\Services\Format;

class BroadcastRequest extends Message
{
    protected $coverage;

    public function __construct($coverage)
    {
        $this->coverage = $coverage;
    }

    public function via() : array
    {
        return $this->coverage['urgent']
            ? ['mail', 'sms']
            : ['mail'];
    }

    public function getTitle() : string
    {
        return __('Coverage Request');
    }

    public function getText() : string
    {
        return __("{name} is looking for coverage on {date}. This request is open for the first available substitute to accept.", [
            'date' => Format::dateRangeReadable($this->coverage['dateStart'], $this->coverage['dateEnd']),
            'name' => Format::name($this->coverage['titleAbsence'], $this->coverage['preferredNameAbsence'], $this->coverage['surnameAbsence'], 'Staff', false, true),
        ]);
    }

    public function getDetails() : array
    {
        return [
            __('Comment') => $this->coverage['notesStatus'],
            __('Date')    => Format::dateRangeReadable($this->coverage['dateStart'], $this->coverage['dateEnd']),
            __('Time')    => $this->coverage['allDay'] == 'Y' ? __('All Day') : Format::timeRange($this->coverage['timeStart'], $this->coverage['timeEnd']),
        ];
    }

    public function getModule() : string
    {
        return __('Staff');
    }

    public function getAction() : string
    {
        return __('View Coverage Requests');
    }

    public function getLink() : string
    {
        return 'index.php?q=/modules/Staff/coverage_view.php';
    }
}
