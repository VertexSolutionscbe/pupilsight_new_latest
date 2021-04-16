<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables;

use Pupilsight\Domain\DataSet;
use Pupilsight\Domain\QueryCriteria;
use Pupilsight\Forms\OutputableInterface;
use Pupilsight\Tables\Action;
use Pupilsight\Tables\Columns\Column;
use Pupilsight\Tables\Columns\ActionColumn;
use Pupilsight\Tables\Columns\CheckboxColumn;
use Pupilsight\Tables\Columns\ExpandableColumn;
use Pupilsight\Tables\Renderer\RendererInterfaceResult;
use Pupilsight\Tables\View\DataTableViewResult;
use Pupilsight\Tables\View\PaginatedViewResult;

/**
 * DataTable
 *
 * @version v16
 * @since   v16
 */
class DataTableResult implements OutputableInterface
{
    protected $id;
    protected $title;
    protected $description;
    protected $data;
    protected $renderer;

    protected $columns = array();
    protected $header = array();
    protected $meta = array();

    protected $rowModifiers = [];

    /**
     * Create a data table with optional renderer.
     *
     * @param string $id
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterfaceResult $renderer = null)
    {
        $this->renderer = $renderer;
    }

    /**
     * Static create method, for ease of method chaining. Defaults to a simple table renderer.
     *
     * @param string $id
     * @param RendererInterface $renderer
     * @return self
     */
    public static function create($id, RendererInterfaceResult $renderer = null)
    {
        global $container;

        $renderer = !empty($renderer) ? $renderer : $container->get(DataTableViewResult::class);

        return (new static($renderer))->setID($id);
    }

    /**
     * Helper method to create a default paginated data table, using criteria from a gateway query.
     *
     * @param string $id
     * @param QueryCriteria $criteria
     * @return self
     */
    public static function createPaginated($id, QueryCriteria $criteria)
    {
        global $container;

        $renderer = $container->get(PaginatedViewResult::class)->setCriteria($criteria);

        return (new static($renderer))->setID($id)->setRenderer($renderer);
    }

    /**
     * Set the table ID.
     *
     * @param string $id
     * @return self
     */
    public function setID($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the table ID.
     *
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Get the table title.
     * @return  string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the table title.
     * @param  string  $title
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the table description.
     * @return  string
     */
    public function getDescription()
    {
        return is_callable($this->description)
            ? call_user_func($this->description)
            : $this->description;
    }

    /**
     * Set the table description. Can be a string or a callable that returns a string.
     * @param  string|Callable  $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the table data internally.
     *
     * @param DataSet $data
     * @return self
     */
    public function withData(DataSet $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set the renderer for the data table. Can also be supplied ad hoc in the render method.
     *
     * @param RendererInterface $renderer
     * @return self
     */
    public function setRenderer(RendererInterfaceResult $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Get the current data table renderer.
     *
     * @return RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Add a column to the table, by name and optional label. Returns the created column.
     *
     * @param string $name
     * @param string $label
     * @return Column
     */
    public function addColumn($id, $label = '')
    {
        $this->columns[$id] = new Column($id, $label);

        return $this->columns[$id];
    }

    /**
     * Add an action column to the table, which is generally rendered on the right-hand side.
     *
     * @return ActionColumn
     */
    public function addActionColumn()
    {
        $this->columns['actions'] = new ActionColumn();

        return $this->columns['actions'];
    }
    public function addmultiActionColumn()
    {
        $this->columns['multiactions'] = new ActionColumn();

        return $this->columns['multiactions'];
    }


    /**
     * Add a checkbox column to the table, used for bulk-action tables.
     *
     * @return CheckboxColumn
     */
    public function addCheckboxColumn($id, $key = '')
    {
        $this->columns[$id] = new CheckboxColumn($id, $key);

        return $this->columns[$id];
    }

    /**
     * Add an expander arrow for 
     *
     * @return ExpandableColumn
     */
    public function addExpandableColumn($id)
    {
        $this->columns[$id] = new ExpandableColumn($id, $this);

        return $this->columns[$id];
    }

    /**
     * Remove a column by id.
     *
     * @param string $id
     * @return self
     */
    public function removeColumn($id)
    {
        if (isset($this->columns[$id])) {
            unset($this->columns[$id]);
        }

        return $this;
    }

    /**
     * Get all columns in the table.
     *
     * @return array
     */
    public function getColumns($maxDepth = null)
    {
        $depth = 0;

        $getNestedColumns = function ($columns, &$allColumns = array()) use (&$getNestedColumns, &$depth, &$maxDepth) {
            foreach ($columns as $column) {
                if ($column->hasNestedColumns() && (is_null($maxDepth) || $column->getDepth() < $maxDepth)) {
                    $getNestedColumns($column->getColumns(), $allColumns);
                } else {
                    $allColumns[] = $column;
                }
            }

            return $allColumns;
        };

        return $getNestedColumns($this->columns);
    }

    public function getColumnByIndex($index)
    {
        $keys = array_keys($this->columns);
        return $this->columns[$keys[$index] ?? ''] ?? null;
    }

    /**
     * Calculate how many layers deep the columns are nested.
     *
     * @return int
     */
    public function getTotalColumnDepth()
    {
        $depth = 1;
        foreach ($this->columns as $column) {
            $depth = max($depth, $column->getTotalDepth());
        }

        return $depth;
    }

    /**
     * Calculate the total span of the table, including nested columns.
     *
     * @return int
     */
    public function getTotalColumnSpan()
    {
        $count = 0;
        foreach ($this->getColumns() as $column) {
            $count += $column->getTotalSpan();
        }

        return $count;
    }

    /**
     * Count the columns in the table. Does not count nested columns.
     *
     * @return int
     */
    public function getColumnCount()
    {
        return count($this->columns);
    }

    /**
     * Add an action to the table, generally displayed in the header right-hand side.
     *
     * @param string $name
     * @param string $label
     * @return Action
     */
    public function addHeaderAction($name, $label = '')
    {
        $this->header[$name] = new Action($name, $label);

        return $this->header[$name];
    }

    /**
     * Get all header content in the table.
     *
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Add a piece of meta data to the table. Can be used for renderer-specific details.
     *
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function addMetaData($name, $value)
    {
        $this->meta[$name] = isset($this->meta[$name]) ? array_replace($this->meta[$name], $value) : $value;

        return $this;
    }

    /**
     * Gets the value of a meta data entry by name.
     *
     * @param string $name
     * @return mixed
     */
    public function getMetaData($name, $defaultValue = null)
    {
        return isset($this->meta[$name]) ? $this->meta[$name] : $defaultValue;
    }

    /**
     * Add a callable function that can modify each row based on that row's data.
     *
     * @param callable $callable
     * @return self
     */
    public function modifyRows(callable $callable)
    {
        $this->rowModifiers[] = $callable;

        return $this;
    }

    /**
     * Get the row logic array of callables.
     *
     * @return array
     */
    public function getRowModifiers()
    {
        return $this->rowModifiers;
    }

    /**
     * Render the data table, either with the supplied renderer or default to the built-in one.
     *
     * @param DataSet $dataSet
     * @param RendererInterface $renderer
     * @return string
     */
    public function render(DataSet $dataSet, RendererInterfaceResult $renderer = null)
    {
        $renderer = isset($renderer) ? $renderer : $this->renderer;

        return $renderer->renderTable($this, $dataSet);
    }

    /**
     * Implement the OutputtableInterface to combine DataTables + Forms.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->renderer->renderTable($this, $this->data);
    }
}
