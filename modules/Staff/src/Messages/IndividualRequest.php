<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\Messages;

use Pupilsight\Module\Staff\Message;
use Pupilsight\Services\Format;

class IndividualRequest extends Message
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
        return __('Coverage Request');
    }

    public function getText() : string
    {
        return __("{name} sent you a coverage request for {date}. Are you available? Please login to accept or decline.", [
            'date' => Format::dateRangeReadable($this->coverage['dateStart'], $this->coverage['dateEnd']),
            'name' => Format::name($this->coverage['titleStatus'], $this->coverage['preferredNameStatus'], $this->coverage['surnameStatus'], 'Staff', false, true),
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
