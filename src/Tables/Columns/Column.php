<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\Columns;

use Pupilsight\Forms\Traits\BasicAttributesTrait;

/**
 * Column
 *
 * @version v16
 * @since   v16
 */
class Column
{
    use BasicAttributesTrait;

    protected $label;
    protected $description;
    protected $contexts = [];
    protected $width = 'auto';
    protected $depth = 0;
    protected $sortable = false;
    protected $translatable = false;
    protected $formatter;

    protected $columns = array();
    protected $cellModifiers = [];

    public function __construct($id, $label = '', $depth = 0)
    {
        $this->setID($id);
        $this->label = $label;
        $this->sortable = [$id];
        $this->depth = $depth;
    }

    /**
     * Gets the column label, often displayed in the table heading.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the column width.
     *
     * @param string|int $width
     * @return self
     */
    public function width($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Gets the column width.
     *
     * @return string|int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get the nested depth of the current column, counting from 0.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    public function context($context)
    {
        $this->contexts[] = $context;

        return $this;
    }

    public function getContexts()
    {
        return $this->contexts;
    }

    public function hasContext($context)
    {
        return in_array($context, $this->contexts);
    }

    /**
     * Sets the column description, often displayed as smaller text below the label.
     *
     * @param string $description
     * @return self
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the column description, often displayed as smaller text below the label.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the names of table columns to sort on. Blank defaults to the column name, false disables sorting.
     *
     * @param array|bool $value
     * @return self
     */
    public function sortable($value = null) 
    {
        $this->sortable = is_null($value) ? [$this->getID()] : $value;

        return $this;
    }

    /**
     * Disables sorting for this column.
     * 
     * @return self
     */
    public function notSortable() 
    {
        $this->sortable = false;

        return $this;
    }

    /**
     * Gets the name of table columns to sort on, or false if sorting is disabled.
     *
     * @return array|bool
     */
    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * Sets the formatter as a callable, which should accept a $data param of row data.
     *
     * @param callable $formatter
     * @return self
     */
    public function format(callable $formatter) 
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * Does the column have a valid formatter?
     *
     * @return bool
     */
    public function hasFormatter() 
    {
        return !empty($this->formatter) && is_callable($this->formatter);
    }

    /**
     * Sets that this column of table must be translated
     *
     * @return self
     */
    public function translatable() 
    {
        $this->translatable = true;
        
        return $this;
    }    

    /**
     * Gets if the column of table must be translated or not
     *
     * @return bool
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }    
    
    /**
     * Set a callable function that can modify each cell and/or row based on that row's data.
     *
     * @param callable $callable
     * @return self
     */
    public function modifyCells(callable $callable)
    {
        $this->cellModifiers[] = $callable;

        return $this;
    }

    /**
     * Get the array of column logic callables.
     *
     * @return callable
     */
    public function getCellModifiers()
    {
        return $this->cellModifiers;
    }

    /**
     * Add a nested column, by name and optional label. Returns the created column.
     *
     * @param string $name
     * @param string $label
     * @return Column
     */
    public function addColumn($id, $label = '')
    {
        $this->columns[$id] = new Column($id, $label, $this->depth + 1);
        $this->sortable = false;

        return $this->columns[$id];
    }

    /**
     * Get all nested columns under this column.
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Returns true if this column has other columns nested under it.
     *
     * @return bool
     */
    public function hasNestedColumns()
    {
        return count($this->columns) > 0;
    }

    /**
     * Gets the total column depth of all nested columns.
     *
     * @return int
     */
    public function getTotalDepth()
    {
        if (!$this->hasNestedColumns()) return 1;

        $depth = 1;
        foreach ($this->getColumns() as $column) {
            $depth = max($depth, $column->getTotalDepth());
        }
        
        return $depth + 1;
    }

    /**
     * Gets the total column span of all nested columns.
     *
     * @return int
     */
    public function getTotalSpan()
    {
        if (!$this->hasNestedColumns()) return 1;

        $count = 0;
        foreach ($this->getColumns() as $column) {
            $count += $column->getTotalSpan();
        }

        return $count;
    }

    /**
     * Renders the column by either passing the row $data to a formatter, 
     * or grabbing the row data by key based on the column name.
     *
     * @param array $data
     * @return string
     */
    public function getOutput(&$data = array())
    {
        if ($this->hasFormatter()) {
            return call_user_func($this->formatter, $data);
        } else {
            $content = isset($data[$this->getID()])? $data[$this->getID()] : '';
            return $this->getTranslatable() ? __($content) : $content;
        }
    }
}
