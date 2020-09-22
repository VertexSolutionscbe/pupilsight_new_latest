<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\Tables;

use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;
use Pupilsight\Module\Staff\Tables\AbsenceFormats;
use Pupilsight\Contracts\Services\Session;
use Pupilsight\Contracts\Database\Connection;

/**
 * AbsenceDates
 *
 * Reusable DataTable class for displaying the info and actions available for absence dates.
 *
 * @version v18
 * @since   v18
 */
class AbsenceDates
{
    protected $session;
    protected $db;
    protected $staffAbsenceGateway;
    protected $staffAbsenceDateGateway;

    public function __construct(Session $session, Connection $db, StaffAbsenceGateway $staffAbsenceGateway, StaffAbsenceDateGateway $staffAbsenceDateGateway)
    {
        $this->session = $session;
        $this->db = $db;
        $this->staffAbsenceGateway = $staffAbsenceGateway;
        $this->staffAbsenceDateGateway = $staffAbsenceDateGateway;
    }

    public function create($pupilsightStaffAbsenceID, $includeDetails = false)
    {
        $guid = $this->session->get('guid');
        $connection2 = $this->db->getConnection();

        $absence = $this->staffAbsenceGateway->getAbsenceDetailsByID($pupilsightStaffAbsenceID);
        $dates = $this->staffAbsenceDateGateway->selectDatesByAbsence($pupilsightStaffAbsenceID)->toDataSet();

        $table = DataTable::create('staffAbsenceDates')->withData($dates);

        if ($includeDetails) {
            $dateLabel = __($absence['type']).' '.__($absence['reason']);
            $timeLabel = __n('{count} Day', '{count} Days', $absence['value'], ['count' => $absence['value']]);
        } else {
            $dateLabel = __('Date');
            $timeLabel = __('Time');
        }

        $table->addColumn('date', $dateLabel)
            ->format(Format::using('dateReadable', 'date'));

        $table->addColumn('timeStart', $timeLabel)
            ->format([AbsenceFormats::class, 'timeDetails']);

        if (!empty($absence['coverage'])) {
            $table->addColumn('coverage', __('Coverage'))
                ->width('30%')
                ->format([AbsenceFormats::class, 'coverage']);
        }

        // ACTIONS
        $canRequestCoverage = isActionAccessible($guid, $connection2, '/modules/Staff/coverage_request.php') && $absence['status'] == 'Approved';
        $canManage = isActionAccessible($guid, $connection2, '/modules/Staff/absences_manage.php');
        $canDelete = count($dates) > 1;

        if ($canManage || $absence['pupilsightPersonID'] == $_SESSION[$guid]['pupilsightPersonID']) {
            $table->addActionColumn()
                ->addParam('pupilsightStaffAbsenceID', $pupilsightStaffAbsenceID)
                ->addParam('pupilsightStaffAbsenceDateID')
                ->format(function ($absence, $actions) use ($canManage, $canDelete, $canRequestCoverage) {
                    if ($canManage) {
                        $actions->addAction('edit', __('Edit'))
                            ->setURL('/modules/Staff/absences_manage_edit_edit.php');
                    }

                    if ($canManage && $canDelete) {
                        $actions->addAction('deleteInstant', __('Delete'))
                            ->setIcon('garbage')
                            ->isDirect()
                            ->setURL('/modules/Staff/absences_manage_edit_deleteProcess.php')
                            ->addConfirmation(__('Are you sure you wish to delete this record?'));
                    }

                    if ($canRequestCoverage && empty($absence['pupilsightStaffCoverageID']) && $absence['date'] >= date('Y-m-d')) {
                        $actions->addAction('coverage', __('Request Coverage'))
                            ->setIcon('attendance')
                            ->setURL('/modules/Staff/coverage_request.php');
                    }
                });
        }

        return $table;
    }
}
