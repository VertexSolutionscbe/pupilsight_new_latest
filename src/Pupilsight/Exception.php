<?php
/*
Pupilsight, Flexible & Open School System
 *
 * @category   Pupilsight
 * @package    Pupilsight
 * @copyright  Copyright (c) 2006 - 2014 GNU (http://www.gnu.org/licenses/)
 * @license    GNU  http://www.gnu.org/licenses/
 * @version    ##VERSION##, ##DATE##
 */
 /**
  */
namespace Pupilsight;


/**
 * Pupilsight Exception
 *
 * @version    v13
 * @since      v13 
 * @category   Pupilsight
 * @package    Pupilsight
 * @copyright  Copyright (c) 2006 - 2014 
 */
class Exception extends \Exception {
    /**
     * Error handler callback
     *
     * @param mixed $code
     * @param mixed $string
     * @param mixed $file
     * @param mixed $line
     * @param mixed $context
     */
    public static function errorHandlerCallback($code, $string, $file, $line, $context) {
        $e = new self($string, $code);
        $e->line = $line;
        $e->file = $file;
        throw $e;
    }
}