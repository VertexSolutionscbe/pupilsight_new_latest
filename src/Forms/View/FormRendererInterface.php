<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\View;

use Pupilsight\Forms\Form;

interface FormRendererInterface
{
    public function renderForm(Form $form);
}
