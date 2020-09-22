<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Attendance;

use Pupilsight\Domain\DataSet;
use Pupilsight\UI\Chart\Chart;
use Pupilsight\Tables\DataTable;
use Pupilsight\Tables\View\DataTableView;
use Pupilsight\Tables\Renderer\RendererInterface;

/**
 * Student History View
 *
 * @version v18
 * @since   v18
 */
class StudentHistoryView extends DataTableView implements RendererInterface
{
    /**
     * Render the table to HTML.
     *
     * @param DataTable $table
     * @param DataSet $dataSet
     * @return string
     */
    public function renderTable(DataTable $table, DataSet $dataSet)
    {
        $this->addData('table', $table);

        if ($dataSet->count() > 0) {
            $summary = $this->getSummaryCounts($dataSet);
            $this->addData([
                'dataSet' => $dataSet,
                'summary' => $summary,
                'chart'   => $this->getChart($summary),
            ]);
        }

        return $this->render('components/studentHistory.twig.html');
    }

    protected function getSummaryCounts(DataSet $dataSet)
    {
        $summary = ['total' => 0, 'present' => 0, 'partial' => 0, 'absent' => 0, '' => 0];

        foreach ($dataSet as $terms) {
            if (empty($terms['weeks'])) continue;
            
            foreach ($terms['weeks'] as $week) {
                foreach ($week as $dayData) {
                    if (!$dayData['specialDay'] && !$dayData['outsideTerm']) {
                        $summary['total'] += 1;
                        $summary[$dayData['endOfDay']['status']] += 1;
                    }
                }
            }
        }

        return $summary;
    }

    protected function getChart($summary)
    {
        $chart = Chart::create('attendanceSummary', 'doughnut')
            ->setOptions(['height' => 200])
            ->setLabels([__('Present'), __('Partial'), __('Absent'), __('No Data')])
            ->setColors(['#9AE6B4', '#FFD2A8', '#FC8181', 'rgba(0, 0, 0, 0.05)']);
    
        $chart->addDataset('pie')
            ->setData([$summary['present'], $summary['partial'], $summary['absent'], $summary['']]);

        return $chart->render();
    }
}
