<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms;

interface FormFactoryInterface
{
    public function createRow($id);
    public function createColumn($id);
    public function createTrigger($selector);
    public function createContent($content);
}
