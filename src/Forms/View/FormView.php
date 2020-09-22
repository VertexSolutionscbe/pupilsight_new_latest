<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\View;

use Pupilsight\View\View;
use Pupilsight\Forms\Form;
use Pupilsight\Forms\ValidatableInterface;
use Pupilsight\Forms\View\FormRendererInterface;

/**
 * FormView
 *
 * @version v18
 * @since   v18
 */
class FormView extends View implements FormRendererInterface
{
    /**
     * Transform a Form object into HTML and javascript output using a Twig template.
     * @param   Form    $form
     * @return  string
     */
    public function renderForm(Form $form)
    {
        $this->addData('form', $form);
        $this->addData('javascript', $this->getInlineJavascript($form));
        $this->addData('totalColumns', $this->getColumnCount($form));
        return $this->render('components/form.twig.html');
    }

    protected function getInlineJavascript(Form $form)
    {
        $javascript = [];

        foreach (array_reverse($form->getTriggers()) as $trigger) {
            $javascript[] = $trigger->getOutput();
        }
        
        return $javascript;
    }

    /**
     * Get the maximum columns required to render this form.
     * @return  int
     */
    protected function getColumnCount(Form $form)
    {
        return array_reduce($form->getRows(), function ($count, $row) {
            return max($count, $row->getElementCount());
        }, 0);
    }

    /**
     * @deprecated Empty-method for module backwards compatibility.
     * Will be removed by the end of the mobile-responsive refactoring.
     */
    public function setWrapper($name, $value) {
        return $this;
    }
}
