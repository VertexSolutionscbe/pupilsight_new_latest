<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Prefab;

use Pupilsight\Forms\Form;
use Pupilsight\Forms\FormFactory;
use Pupilsight\Forms\FormRenderer;

/**
 * BulkActionForm
 *
 * @version v15
 * @since   v15
 */
class BulkActionForm extends Form
{
    public static function create($id, $action, $method = 'post', $class = 'w-full blank bulkActionForm border-0 bg-transparent p-0')
    {
        global $container;

        $form = $container->get(BulkActionForm::class)
            ->setID($id)
            ->setClass($class)
            ->setAction($action)
            ->setMethod($method);

        $form->addConfirmation(__('Are you sure you wish to process this action? It cannot be undone.'));
        $form->addHiddenValue('address', $_GET['q']);

        return $form;
    }

    public function addBulkActionRow($actions = [])
    {
        $row = $this->addRow()->setClass('');
        $col = $row->addElement($this->createBulkActionColumn($actions));

        return $col;
    }

    public function createBulkActionColumn($actions = [])
    {
        $col = $this->getFactory()->createColumn()->addClass('');

        $col->addSelect('action')
            ->fromArray($actions)
            ->required()
            ->setClass('relative w-32 sm:w-48 mr-1 flex items-center')
            ->placeholder(__('Select action'));

        return $col;
    }
}
