<?php
/*
Pupilsight, Flexible & Open School System
 */

namespace Pupilsight\Module\Rubrics;

use Pupilsight\UI\Chart\Chart;

/**
 * Attendance display & edit class
 *
 * @version v18
 * @since   v18
 */
class Visualise
{
    protected $absoluteURL;

    protected $page;

    protected $guid;

    protected $pupilsightPersonID;

    protected $columns;

    protected $rows;

    protected $cells;

    protected $contexts;

    /**
     * Constructor
     *
     * @version  v18
     * @since    v18
     * @return   void
     */
    public function __construct($absoluteURL, $page, $pupilsightPersonID, array $columns, array $rows, array $cells, array $contexts)
    {
        $this->absoluteURL = $absoluteURL;

        $this->page = $page;

        $this->pupilsightPersonID = $pupilsightPersonID;

        $this->columns = $columns;

        $this->rows = $rows;

        $this->cells = $cells;

        $this->contexts = $contexts;
    }

    /**
     * renderVisualise
     *
     * @version  v18
     * @since    v18
     * @param   $legend should the legend be included?
     * @param   $image should the chart be saved as an image
     * @param   $path if image is saved, where should it be saved (defaults to standard upload location)
     * @return   void
     */
    public function renderVisualise($legend = true, $image = false, $path = '')
    {
        //Filter out columns to ignore from visualisation
        $this->columns = array_filter($this->columns, function ($item) {
            return (isset($item['visualise']) && $item['visualise'] == 'Y');
        });

        if (!empty($this->columns) && !empty($this->cells)) {
            //Cycle through rows to calculate means
            $means = array() ;
            foreach ($this->rows as $row) {
                $means[$row['pupilsightRubricRowID']]['title'] = $row['title'];
                $means[$row['pupilsightRubricRowID']]['cumulative'] = 0;
                $means[$row['pupilsightRubricRowID']]['denonimator'] = 0;

                //Cycle through cells, and grab those for this row
                $cellCount = 1 ;
                foreach ($this->cells[$row['pupilsightRubricRowID']] as $cell) {
                    $visualise = false ;
                    foreach ($this->columns as $column) {
                        if ($column['pupilsightRubricColumnID'] == $cell['pupilsightRubricColumnID']) {
                            $visualise = true ;
                        }
                    }

                    if ($visualise) {
                        foreach ($this->contexts as $entry) {
                            if ($entry['pupilsightRubricCellID'] == $cell['pupilsightRubricCellID']) {
                                $means[$row['pupilsightRubricRowID']]['cumulative'] += $cellCount;
                                $means[$row['pupilsightRubricRowID']]['denonimator']++;
                            }
                        }
                        $cellCount++;
                    }
                }
            }

            $columnCount = count($this->columns);
            $data = array_map(function ($mean) use ($columnCount) {
                return !empty($mean['denonimator'])
                ? round((($mean['cumulative']/$mean['denonimator'])/$columnCount), 2)
                : 0;
            }, $means);

            $this->page->scripts->add('chart');

            $chart = Chart::create('visualisation'.$this->pupilsightPersonID, 'polarArea')
                ->setLegend(['display' => $legend, 'position' => 'right'])
                ->setLabels(array_column($means, 'title'))
                ->setColorOpacity(0.6);

            $options = [
                'height' => '120%',
                'scale'  => [
                    'ticks' => [
                        'min' => 0.0,
                        'max' => 1.0,
                        'callback' => $chart->addFunction('function(tickValue, index, ticks) {
                            return Number(tickValue).toFixed(1);
                        }'),
                    ],
                ]
            ];
            if ($image) {
                $path = ($path != '') ? ", path: '$path'" : '';
                $options['animation'] = [
                    'duration' => 0,
                    'onComplete' => $chart->addFunction('function(e) {
                        var img = visualisation'.$this->pupilsightPersonID.'.toDataURL("image/png");
                        $.ajax({ url: "'.$this->absoluteURL.'/modules/Rubrics/src/visualise_saveAjax.php", type: "POST", data: {img: img, pupilsightPersonID: \''.$this->pupilsightPersonID.$path.'\'}, dataType: "html"})
                        $.ajax({ url: "./"});
                    }'),
                ];
            }
            $chart->setOptions($options);

            $chart->addDataset('rubric')->setData($data);

            return $chart->render();
        }
    }
}
