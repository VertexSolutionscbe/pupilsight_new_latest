<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

/**
 * Date
 *
 * @version v14
 * @since   v14
 */
class Currency extends Number
{
    protected $decimalPlaces = 2;
    protected $onlyInteger = false;

    /**
     * Adds currency format to the label description (if not already present)
     * @return string|bool
     */
    public function getLabelContext($label)
    {
        global $guid;

        if (stristr($label->getDescription(), 'In ') === false) {
            return sprintf(__('In %1$s.'), $_SESSION[$guid]['currency']);
        }

        return false;
    }
}
