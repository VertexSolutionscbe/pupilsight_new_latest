<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms;

interface ValidatableInterface
{
    public function addValidation($name);
    public function getValidationOutput();
}