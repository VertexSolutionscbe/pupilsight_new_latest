<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\Renderer;

use Pupilsight\Domain\DataSet;
use Pupilsight\Tables\DataTable;
use Pupilsight\Tables\Columns\Column;
use Pupilsight\Tables\Renderer\RendererInterface;

/**
 * JSON Renderer
 *
 * @version v17
 * @since   v17
 */
class JsonRenderer implements RendererInterface
{
    protected $jsonOptions;

    /**
     * @param int json_encode options (optional)
     */
    public function __construct($jsonOptions = JSON_PRETTY_PRINT)
    {
        $this->jsonOptions = $jsonOptions;
    }

    /**
     * Render the table to JSON.
     *
     * @param DataTable $table
     * @param DataSet $dataSet
     * @return string
     */
    public function renderTable(DataTable $table, DataSet $dataSet)
    {
        $jsonData = array();

        foreach ($dataSet as $index => $data) {
            $jsonData[$index] = $this->jsonifyColumns($table->getColumns(0), $data);
        }

        return json_encode($jsonData, $this->jsonOptions);
    }

    /**
     * Recursively build a set of column data, handling nested columns as nested JSON objects.
     *
     * @param array $columns
     * @param array $data
     * @return array
     */
    protected function jsonifyColumns(array $columns, array &$data)
    {
        $columnData = array();
        foreach ($columns as $column) {
            $columnData[$column->getID()] = ($column->getTotalDepth() > 1)
                ? $this->jsonifyColumns($column->getColumns(), $data)
                : $this->stripTags($column->getOutput($data));
        }
        return $columnData;
    }

    protected function stripTags($content)
    {
        $content = preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $content);
        return strip_tags($content);
    }
}
