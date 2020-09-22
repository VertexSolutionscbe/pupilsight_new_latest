<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\Tables;

use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Staff\StaffCoverageDateGateway;
use Pupilsight\Module\Staff\Tables\AbsenceFormats;

/**
 * CoverageDates
 *
 * Reusable DataTable class for displaying the info for coverage dates.
 *
 * @version v18
 * @since   v18
 */
class CoverageDates
{
    protected $staffCoverageDateGateway;

    public function __construct(StaffCoverageDateGateway $staffCoverageDateGateway)
    {
        $this->staffCoverageDateGateway = $staffCoverageDateGateway;
    }

    public function create($pupilsightStaffCoverageID)
    {
        $dates = $this->staffCoverageDateGateway->selectDatesByCoverage($pupilsightStaffCoverageID)->toDataSet();
        $table = DataTable::create('staffCoverageDates')->withData($dates);

        $table->addColumn('date', __('Date'))
            ->format(Format::using('dateReadable', 'date'));

        $table->addColumn('timeStart', __('Time'))
            ->format([AbsenceFormats::class, 'timeDetails']);

        return $table;
    }
}
