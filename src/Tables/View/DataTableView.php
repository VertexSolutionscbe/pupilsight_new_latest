<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\View;

use Pupilsight\View\View;
use Pupilsight\Domain\DataSet;
use Pupilsight\Tables\DataTable;
use Pupilsight\Forms\Layout\Element;
use Pupilsight\Tables\Columns\Column;
use Pupilsight\Forms\Layout\TableCell;
use Pupilsight\Tables\Renderer\RendererInterface;

/**
 * TableView
 *
 * @version v18
 * @since   v18
 */
class DataTableView extends View implements RendererInterface
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
        $this->addData('blankSlate', $table->getMetaData('blankSlate'));

        if ($dataSet->count() > 0) {
            $this->preProcessTable($table);

            $this->addData([
                'headers'    => $this->getTableHeaders($table),
                'columns'    => $table->getColumns(),
                'rows'       => $this->getTableRows($table, $dataSet),
            ]);
        }

        return $this->render('components/dataTable.twig.html');
    }

    /**
     * If a table doesn't have pre-defined context, apply some initial contexts.
     * In most cases, the first few columns in a table represent the primary data.
     *
     * @param DataTable $table
     */
    protected function preProcessTable(DataTable $table)
    {
        $contextColumns = array_filter($table->getColumns(), function ($column) {
            return $column->hasContext('primary');
        });

        if (count($contextColumns) == 0) {
            for ($i = 0; $i <= 2; $i++) {
                if ($column = $table->getColumnByIndex($i)) {
                    if ($column->hasContext('action')) continue;

                    $column->context($i < 2 ? 'primary' : 'secondary');
                }
            }
        }
    }

    /**
     * Returns an array of header objects, accounting for nested columns.
     *
     * @param DataTable $table
     * @return array
     */
    protected function getTableHeaders(DataTable $table)
    {
        $headers = [];

        $totalColumnDepth = $table->getTotalColumnDepth();

        for ($i = 0; $i < $totalColumnDepth; $i++) {
            foreach ($table->getColumns($i) as $columnIndex => $column) {
                $th = $this->createTableHeader($column);

                if (!$th) continue; // Can be removed by tableHeader logic
                if ($column->getDepth() < $i) continue;

                // Calculate colspan and rowspan to handle nested column headers
                $th->colSpan($column->getTotalSpan());
                $th->rowSpan($column->getTotalDepth() > 1 ? 1 : ($totalColumnDepth - $column->getDepth()));

                $headers[$i][$columnIndex] = $th;
            }
        }
        
        return $headers;
    }

    /**
     * Returns an array of row objects for the data in this <table class=""></table>
     *
     * @param DataTable $table
     * @param DataSet $dataSet
     * @return array
     */
    protected function getTableRows(DataTable $table, DataSet $dataSet)
    {
        $rows = [];

        foreach ($dataSet as $index => $data) {
            $row = $this->createTableRow($data, $table);
            if (!$row) continue; // Can be removed by rowLogic
            
            $row->addClass($index % 2 == 0? 'odd' : 'even');

            $cells = [];

            // CELLS
            foreach ($table->getColumns() as $columnIndex => $column) {
                $cell = $this->createTableCell($data, $table, $column);
                if (!$cell) continue; // Can be removed by cellLogic

                $cells[$columnIndex] = $cell;
            }

            $rows[$index] = ['data' => $data, 'row' => $row, 'cells' => $cells];
        }

        return $rows;
    }

    /**
     * Creates the HTML object for the <th> tag.
     * 
     * @param Column $column
     * @return Element
     */
    protected function createTableHeader(Column $column)
    {
        $th = new TableCell($column->getLabel());

        $th->setTitle($column->getTitle())
           ->setClass('column')
           ->addData('description', $column->getDescription());

        $this->applyContexts($column, $th);

        return $th;
    }

    /**
     * Creates the HTML object for the <tr> tag, applies optional rowLogic callable to modify the output.
     * 
     * @param DataTable $table
     * @return Element
     */
    protected function createTableRow(array $data, DataTable $table)
    {
        $row = new Element();

        foreach ($table->getRowModifiers() as $callable) {
            $row = $callable($data, $row, $table->getColumnCount());
        }

        return $row;
    }

    /**
     * Creates the HTML object for the <td> tag, applies optional cellLogic callable to modify the output.
     * 
     * @param DataTable $table
     * @param array $data
     * @return Element
     */
    protected function createTableCell(array $data, DataTable $table, Column $column)
    {
        $cell = new Element();
        $cell->addClass('p-2 sm:p-3');
        $this->applyContexts($column, $cell);

        foreach ($column->getCellModifiers() as $callable) {
            $cell = $callable($data, $cell, $table->getColumnCount());
        }
        
        return $cell;
    }

    /**
     * Adds classes to a table element based on it's column's context.
     * 
     * @param Column $column
     * @param Element $element
     */
    protected function applyContexts(Column $column, Element &$element)
    {
        if ($column->hasContext('secondary')) {
            $element->addClass('hidden-1 sm:table-cell');
        } elseif (!$column->hasContext('primary') && !$column->hasContext('action')) {
            $element->addClass('hidden-1 md:table-cell');
        }
    }
}
