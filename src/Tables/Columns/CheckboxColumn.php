<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\Columns;

use Pupilsight\Forms\Input\Checkbox;

/**
 * CheckboxColumn
 *
 * @version v16
 * @since   v16
 */
class CheckboxColumn extends Column
{
    protected $key;
    protected $checked;

    /**
     * Creates a pre-defined column for bulk-action checkboxes.
     */
    public function __construct($id, $key = null)
    {
        parent::__construct($id);
        
        $this->sortable(false)->width('6%');
        $this->context('action');
        $this->key = !empty($key)? $key : $id;

        $this->modifyCells(function ($data, $cell) {
            return $cell->addClass('bulkCheckbox textCenter');
        });
    }

    public function checked($value = true)
    {
        $this->checked = $value;
        return $this;
    }

    /**
     * Overrides the label with a checkall checkbox.
     * @return string
     */
    public function getLabel()
    {
        return (new Checkbox('checkall'))
            ->setClass('floatNone checkall')
            ->checked($this->checked)
            ->wrap('<div class="textCenter">', '</div>')
            ->getOutput();
    }

    /**
     * Renders a bulk-action checkbox, grabbing the value by key from $data.
     *
     * @param array $data
     * @return string
     */
    public function getOutput(&$data = array())
    {
        $value = isset($data[$this->key])? $data[$this->key] : '';

        $contents = $this->hasFormatter() ? call_user_func($this->formatter, $data) : '';

        return !empty($contents)
            ? $contents 
            : (new Checkbox($this->getID().'[]'))
            ->setID($this->getID().$value)
            ->setValue($value)
            ->checked($this->checked ? $value : false)
            ->getOutput();
    }
}
