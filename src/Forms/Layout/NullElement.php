<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Layout;

use Pupilsight\Forms\OutputableInterface;

/**
 * NullElement
 *
 * @version v16
 * @since   v16
 */
class NullElement implements OutputableInterface
{
    public function __call($name, $arguments)
    {
        return $this;
    }

    public function getOutput()
    {
        return '';
    }
}
