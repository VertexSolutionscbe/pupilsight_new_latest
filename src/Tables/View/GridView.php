<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\View;

use Pupilsight\Domain\DataSet;
use Pupilsight\Tables\DataTable;
use Pupilsight\Tables\View\DataTableView;
use Pupilsight\Tables\Renderer\RendererInterface;
/**
 * Grid View
 *
 * @version v18
 * @since   v18
 */
class GridView extends DataTableView implements RendererInterface
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
        $this->addData([
            'table'      => $table,
            'columns'    => $table->getColumns(),
            'dataSet'    => $dataSet,
            'gridHeader' => $table->getMetaData('gridHeader'),
            'gridFooter' => $table->getMetaData('gridFooter'),
            'blankSlate' => $table->getMetaData('blankSlate'),
        ]);

        return $this->render('components/gridTable.twig.html');
    }
}
