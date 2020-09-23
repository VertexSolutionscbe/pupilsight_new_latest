<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\Prefab;

use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Tables\Renderer\PaginatedRenderer;
use Pupilsight\Tables\Renderer\PrintableRenderer;
use Pupilsight\Tables\Renderer\SpreadsheetRenderer;

/**
 * ReportTable
 *
 * @version v17
 * @since   v17
 */
class ReportTable extends DataTable
{
    /**
     * Helper method to create a report data table, which can display as a table, printable page or export.
     *
     * @param string $id
     * @param QueryCriteria $criteria
     * @param string $viewMode
     * @param string $guid
     * @return self
     */
    public static function createPaginated($id, QueryCriteria $criteria)
    {
        $table = parent::createPaginated($id, $criteria);

        $table->addHeaderAction('print', __('Print'))
            ->setURL('/report.php')
            ->addParams($_GET)
            ->addParam('format', 'print')
            ->addParam('search', $criteria->getSearchText(true))
            ->setTarget('_blank')
            ->directLink()
            ->append('&nbsp;');

        $table->addHeaderAction('export', __('Export'))
            ->setURL('/export.php')
            ->addParams($_GET)
            ->addParam('format', 'export')
            ->addParam('search', $criteria->getSearchText(true))
            ->directLink();

        return $table;
    }

    public function setViewMode($viewMode, $session)
    {
        switch ($viewMode) {
            case 'print':   $this->setRenderer(new PrintableRenderer()); break;
            
            case 'export':  $this->setRenderer(new SpreadsheetRenderer($session->get('absolutePath'))); break;
        }

        $this->addMetaData('filename', 'pupilsightExport_'.$this->getID());
        $this->addMetaData('creator', formatName('', $session->get('preferredName'), $session->get('surname'), 'Staff'));

        return $this;
    }

    /**
     * Add an incremental row count. For paginated tables, the starting count from DataSet::getPageFrom should be passed in.
     *
     * @param int $count
     * @return Column
     */
    public function addRowCountColumn($count = 1)
    {
        return $this->addColumn('count', '')
            ->notSortable()
            ->width('35px')
            ->format(function ($row) use (&$count) {
                return '<span class="subdued">'.$count++.'</span>';
            });
    }
}
