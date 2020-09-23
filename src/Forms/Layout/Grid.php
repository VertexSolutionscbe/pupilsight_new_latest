<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Layout;

use Pupilsight\Forms\OutputableInterface;
use Pupilsight\Forms\ValidatableInterface;
use Pupilsight\Forms\FormFactoryInterface;
use Pupilsight\Forms\Traits\BasicAttributesTrait;

/**
 * Grid
 *
 * @version v15
 * @since   v15
 */
class Grid implements OutputableInterface, ValidatableInterface
{
    use BasicAttributesTrait;

    protected $factory;
    protected $elements = array();
    protected $columns; 

    /**
     * Create an element that displays a collection of elements in a flexible grid,
     * @param  FormFactoryInterface  $factory
     * @param  string                $id
     */
    public function __construct(FormFactoryInterface $factory, $id = '', $breakpoints = 'w-1/2 sm:w-1/3')
    {
        $this->factory = $factory;
        $this->setBreakpoints($breakpoints);
        $this->setID($id);
    }

    /**
     * Sets the breakpoints in the grid with css classes, eg: w-1/2 sm:w-1/3
     * @param int $columns
     * @return self
     */
    public function setBreakpoints($breakpoints)
    {
        $this->breakpoints = $breakpoints;

        return $this;
    }
    
    /**
     * Add a cell to the internal collection and return the resulting object.
     * @param  string  $id
     * @return object  Column
     */
    public function addCell($id = '')
    {
        $element = $this->factory->createColumn($id);
        $this->elements[] = $element;

        return $element;
    }

    /**
     * Get all cells in the grid.
     * @return  array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Get the HTML output of the element. Iterate over elements to build a grid.
     * @return  string
     */
    public function getOutput()
    {
        $this->addClass('w-full flex flex-wrap items-stretch');

        $output = '<div '.$this->getAttributeString().'>';
        
        foreach ($this->getElements() as $cell) {
            $cell->addClass($this->breakpoints);

            $output .= '<div '.$cell->getAttributeString().'>';
            $output .= $cell->getOutput();
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Dead-end stub for interface: grids cannot validate.
     * @param   string  $name
     * @return  self
     */
    public function addValidation($name)
    {
        return $this;
    }

    /**
     * Iterate over each element in the collection and get the combined validation output.
     * @return  string
     */
    public function getValidationOutput()
    {
        $output = '';

        foreach ($this->getElements() as $cell) {
            foreach ($cell->getElements() as $element) {
                if ($element instanceof ValidatableInterface) {
                    $output .= $element->getValidationOutput();
                }
            }
        }

        return $output;
    }

    /**
     * Pass an array of $key => $value pairs into each element in the collection.
     * @param   array  &$data
     * @return  self
     */
    public function loadFrom(&$data)
    {
        foreach ($this->getElements() as $cell) {
            $cell->loadFrom($data);
        }

        return $this;
    }
}
