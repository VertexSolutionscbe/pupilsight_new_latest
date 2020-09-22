<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Layout;

/**
 * TableCell
 *
 * @version v15
 * @since   v15
 */
class TableCell extends Element
{
    public function colSpan($value)
    {
        $this->setAttribute('colspan', $value);
        return $this;
    }

    public function rowSpan($value)
    {
        $this->setAttribute('rowspan', $value);
        return $this;
    }
}
