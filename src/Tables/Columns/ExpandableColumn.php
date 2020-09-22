<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\Columns;

use Pupilsight\Tables\DataTable;
use Pupilsight\Forms\Input\Checkbox;

/**
 * ExpandableColumn
 *
 * @version v16
 * @since   v16
 */
class ExpandableColumn extends Column
{
    /**
     * Creates a pre-defined column for expanding rows with extra data.
     */
    public function __construct($id, DataTable $table)
    {
        parent::__construct($id);
        $this->sortable(false)->width('5%');
        $this->context('action');

        $table->modifyRows(function($data, $row, $columnCount) {
            return $row->append($this->getExpandedContent($data, $columnCount));
        });

        $this->modifyCells(function($data, $cell) {
            return $cell->addClass('expandable');
        });
    }

    /**
     * Overrides the label.
     * @return string
     */
    public function getLabel()
    {
        return '';
    }

    /**
     * Expander arrow.
     *
     * @param array $data
     * @return string
     */
    public function getOutput(&$data = array())
    {
        if ($content = parent::getOutput($data)) {
            return '<a onclick="return false;" class="expander"></a>';
        } else {
            return '';
        }
    }

    /**
     * Output the content of the expanded row. Can be set by the column ID, or with the column's formatter callable.
     *
     * @param array $data
     * @param int $columnCount
     * @return string
     */
    public function getExpandedContent(&$data = array(), $columnCount)
    {
        $output = '';

        if ($content = parent::getOutput($data)) {
            $output .= '<tr style="display:none;"><td colspan="'.$columnCount.'">';
            $output .= $content;
            $output .= '</td></tr>';
        }
        return $output;
    }
}
