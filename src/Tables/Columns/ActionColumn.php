<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\Columns;

use Pupilsight\Tables\Action;

/**
 * ActionColumn
 *
 * @version v16
 * @since   v16
 */
class ActionColumn extends Column
{
    protected $actions = array();
    protected $params = array();

    /**
     * Creates a pre-defined column for grouped sets of action icons.
     */
    public function __construct()
    {
        parent::__construct('actions', __('Actions'));
        $this->sortable(false);
        $this->context('action');
        
    }

    /**
     * Adds a named action to the column and returns the new Action object. 
     *
     * @param string $name
     * @param string $label
     * @return Action
     */
    public function addAction($name, $label = '')
    {
        $action = new Action($name, $label);
        $this->actions[$name] = $action;

        return $action;
    }
    public function addmultiAction($name, $label = '')
    {  
        $action = new Action($name, $label);
        $this->actions[$name] = $action;
        $this->sortable(false);
        return $action;
    }
    

    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Adds a URL parameter to the column that is passed to _each_ action.
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function addParam($name, $value = null)
    {
        $this->params[$name] = $value;

        return $this;
    }

    /**
     * Adds an array of URL parameters to be appended to the link URL.
     * 
     * @param array $values
     * @return self
     */
    public function addParams($values)
    {
        if (is_array($values)) {
            $this->params = array_replace($this->params, $values);
        }

        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    /**
     * Sets a column width based on the number of actions.
     *
     * @return string
     */
    public function getWidth()
    {
        return '1%';
    }

    /**
     * Iterates over and renders each action, passing in the row data and URL parameters.
     *
     * @param array $data
     * @return string
     */
    public function getOutput(&$data = array())
    {
        $output = '';

        if ($this->hasFormatter()) {
            $this->actions = [];
            call_user_func($this->formatter, $data, $this);
        }

        return $output;
    }
}
