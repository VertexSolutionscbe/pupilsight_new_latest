<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Prefab;

use Pupilsight\Forms\Form;

/**
 * DeleteForm
 *
 * @version v15
 * @since   v15
 */
class DeleteForm extends Form
{
    public static function createForm($action, $confirmation = false, $submit = true)
    {
        $form = parent::create('deleteRecord'.substr(md5(random_bytes(10)), 0, 20), $action);
        $form->addHiddenValue('address', $_GET['q']);

        foreach ($_GET as $key => $value) {
            $form->addHiddenValue($key, $value);
        }

        $row = $form->addRow();
            $col = $row->addColumn();
            $col->addContent(__('Are you sure you want to delete this record?'))->wrap('<strong>', '</strong>');
            $col->addContent(__('This operation cannot be undone, and may lead to loss of vital data in your system. PROCEED WITH CAUTION!'))
                ->wrap('<span style="color: #cc0000"><i>', '</i></span>');

        if ($confirmation) {
            $row = $form->addRow();
            $row->addLabel('confirm', sprintf(__('Type %1$s to confirm'), __('DELETE')));
            $row->addTextField('confirm')
                ->required()
                ->addValidation(
                    'Validate.Inclusion',
                    'within: [\''.__('DELETE').'\'], failureMessage: "'.__('Please enter the text exactly as it is displayed to confirm this action.').'", caseSensitive: false')
                ->addValidationOption('onlyOnSubmit: true');
        }

        if ($submit) {
            $form->addRow()->addConfirmSubmit();
        }

        return $form;
    }
}
