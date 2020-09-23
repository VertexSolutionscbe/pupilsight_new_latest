<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\Renderer;

use Pupilsight\Domain\DataSet;
use Pupilsight\Tables\DataTable;
use Pupilsight\Tables\Columns\Column;
use Pupilsight\Tables\Columns\ActionColumn;
use Pupilsight\Tables\Columns\ExpandableColumn;
use Pupilsight\Tables\Renderer\RendererInterface;

/**
 * PrintableRenderer
 *
 * @version v16
 * @since   v16
 */
class PrintableRenderer extends SimpleRenderer implements RendererInterface
{
    /**
     * @param DataTable $table
     * @param DataSet $dataSet
     * @return string
     */
    protected function renderHeader(DataTable $table, DataSet $dataSet) 
    {
        $table->setHeader([]);
        $table->addHeaderAction('print', __('Print'))
            ->onClick('javascript:window.print(); return false;')
            ->displayLabel();

        return parent::renderHeader($table, $dataSet);
    }

    /**
     * @param DataTable $table
     * @param DataSet $dataSet
     * @return string
     */
    protected function renderFooter(DataTable $table, DataSet $dataSet)
    {
        return '';
    }

    /**
     * @param Column $column
     * @return Element
     */
    protected function createTableHeader(Column $column)
    {
        if ($column instanceof ActionColumn || $column instanceof ExpandableColumn) return null;

        return parent::createTableHeader($column);
    }

    /**
     * @param DataTable $table
     * @param array $data
     * @return Element
     */
    protected function createTableCell(array $data, DataTable $table, Column $column)
    {
        if ($column instanceof ActionColumn || $column instanceof ExpandableColumn) return null;
        
        return parent::createTableCell($data, $table, $column);
    }
}
