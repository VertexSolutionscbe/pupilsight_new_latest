<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Layout;

use Pupilsight\Forms\OutputableInterface;
use Pupilsight\Forms\RowDependancyInterface;

/**
 * Content
 *
 * @version v14
 * @since   v14
 */
class Heading extends Element implements OutputableInterface, RowDependancyInterface
{
    protected $row;

    /**
     * Add a generic heading element.
     * @param  string  $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Method for RowDependancyInterface to automatically set a reference to the parent Row object.
     * @param  object  $row
     */
    public function setRow($row)
    {
        $this->row = $row;

        $this->row->setClass('break');
    }

    /**
     * Get the content text of the element.
     * @return  string
     */
    protected function getElement()
    {
        return '<h3>'.$this->content.'</h3>';
    }
}
