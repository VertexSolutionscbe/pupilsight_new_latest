<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Staff\Tables;

use Pupilsight\Services\Format;
use Pupilsight\Domain\DataSet;
use Pupilsight\Tables\DataTable;
use DateTime;
use DateInterval;
use DatePeriod;

/**
 * AbsenceCalendar
 * 
 * A reusable DataTable class for displaying absences in a colour-coded calendar view.
 * 
 * @version v18
 * @since   v18
 */
class AbsenceCalendar
{
    public static function create($absences, $dateStart, $dateEnd)
    {
        $calendar = [];
        $dateRange = new DatePeriod(
            new DateTime(substr($dateStart, 0, 7).'-01'),
            new DateInterval('P1M'),
            new DateTime($dateEnd)
        );

        foreach ($dateRange as $month) {
            $days = [];
            for ($dayCount = 1; $dayCount <= $month->format('t'); $dayCount++) {
                $date = new DateTime($month->format('Y-m').'-'.$dayCount);
                $absenceListByDay = $absences[$date->format('Y-m-d')] ?? [];
                $absenceCount = count($absenceListByDay);

                $days[$dayCount] = [
                    'date'    => $date,
                    'number'  => $dayCount,
                    'count'   => $absenceCount,
                    'weekend' => $date->format('N') >= 6,
                    'absence' => current($absenceListByDay),
                ];
            }

            $calendar[] = [
                'name'  => $month->format('M'),
                'days'  => $days,
            ];
        }

        $table = DataTable::create('staffAbsenceCalendar');
        $table->setTitle(__('Calendar'));
        $table->getRenderer()->addData('class', 'calendarTable border-collapse bg-transparent border-r-0');
        $table->addMetaData('hidePagination', true);
        $table->modifyRows(function ($values, $row) {
            return $row->setClass('bg-transparent');
        });

        $table->addColumn('name', '')->notSortable()->context('primary');

        for ($dayCount = 1; $dayCount <= 31; $dayCount++) {
            $table->addColumn($dayCount, '')
                ->context('primary')
                ->notSortable()
                ->format(function ($month) use ($dayCount) {
                    $day = $month['days'][$dayCount] ?? null;
                    if (empty($day) || $day['count'] <= 0) return '';

                    $url = 'fullscreen.php?q=/modules/Staff/absences_view_details.php&pupilsightStaffAbsenceID='.$day['absence']['pupilsightStaffAbsenceID'].'&width=800&height=550';
                    $title = $day['date']->format('l').'<br/>'.$day['date']->format('M j, Y');
                    $title .= '<br/>'.$day['absence']['type'];
                    $classes = ['thickbox'];
                    if ($day['absence']['allDay'] == 'N') {
                        $classes[] = $day['absence']['timeStart'] < '12:00:00' ? 'half-day-am' : 'half-day-pm';
                    }

                    return Format::link($url, $day['number'], ['title' => $title, 'class' => implode(' ', $classes)]);
                })
                ->modifyCells(function ($month, $cell) use ($dayCount) {
                    $day = $month['days'][$dayCount] ?? null;
                    if (empty($day)) return '';

                    $cell->addClass($day['date']->format('Y-m-d') == date('Y-m-d') ? 'border-2 border-gray' : 'border');
                    
                    if ($day['count'] > 0) $cell->addClass('bg-chart'.($day['absence']['sequenceNumber'] % 10));
                    elseif ($day['weekend']) $cell->addClass('bg-gray');
                    else $cell->addClass('bg-white');

                    $cell->addClass('h-3 sm:h-6');

                    return $cell;
                });
        }

        return $table->withData(new DataSet($calendar));
    }
}
