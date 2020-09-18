<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\System\SettingGateway;
use Pupilsight\Domain\Staff\SubstituteGateway;
use Pupilsight\Services\Format;
use Pupilsight\Tables\Action;

if (isActionAccessible($guid, $connection2, '/modules/Staff/substitutes_manage_edit.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Substitutes'), 'substitutes_manage.php', ['search' => $search])
        ->add(__('Edit Substitute'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSubstituteID = $_GET['pupilsightSubstituteID'] ?? '';
    $smsGateway = getSettingByScope($connection2, 'Messenger', 'smsGateway');

    if (empty($pupilsightSubstituteID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $values = $container->get(SubstituteGateway::class)->getByID($pupilsightSubstituteID);

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $person = $container->get(UserGateway::class)->getByID($values['pupilsightPersonID']);

    if (empty($person)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    echo "<div class='linkTop'>";
    if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage_edit.php')) {
        echo (new Action('edit', __('Edit User')))
            ->setURL('/modules/User Admin/user_manage_edit.php')
            ->addParam('pupilsightPersonID', $values['pupilsightPersonID'])
            ->displayLabel()
            ->getOutput();
    }

    if ($search != '') {
        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Staff/substitutes_manage.php&search=$search'>".__('Back to Search Results').'</a>  ';
    }
    echo '</div>';

    $form = Form::create('subsManage', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/substitutes_manage_editProcess.php?search=$search");

    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightSubstituteID', $pupilsightSubstituteID);

    $form->addRow()->addHeading(__('Basic Information'));

    $row = $form->addRow();
        $row->addLabel('pupilsightPersonID', __('Person'));
        $row->addSelectUsers('pupilsightPersonID')
            ->placeholder()
            ->required()
            ->readonly()
            ->selected($values['pupilsightPersonID']);

    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
        $row->addYesNo('active')->required();

    $types = $container->get(SettingGateway::class)->getSettingByScope('Staff', 'substituteTypes');
    $types = array_filter(array_map('trim', explode(',', $types)));

    $row = $form->addRow();
        $row->addLabel('type', __('Type'));
        $row->addSelect('type')->fromArray($types);

    $row = $form->addRow();
        $row->addLabel('priority', __('Priority'))->description(__('Higher priority substitutes appear first when booking coverage.'));
        $row->addSelect('priority')->fromArray(range(-9, 9))->required()->selected(0);
        
    $row = $form->addRow();
        $row->addLabel('details', __('Details'))->description(__('Additional information such as year group preference, language preference, etc.'));
        $row->addTextArea('details')->setRows(2)->maxlength(255);

    $form->addRow()->addHeading(__('Contact Information'));

    $row = $form->addRow();
        $row->addLabel('phone1Label', __('Phone').' 1');
        $phone = $row->addTextField('phone1')
            ->readonly()
            ->setValue(Format::phone($person['phone1'], $person['phone1CountryCode'], $person['phone1Type']));

    if (!empty($person['phone1']) && !empty($smsGateway)) {
        $phone->append(
            $form->getFactory()
                ->createButton(__('Test SMS'))
                ->addClass('testSMS alignRight')
                ->setTabIndex(-1)
                ->getOutput()
        );
    }

    $row = $form->addRow();
        $row->addLabel('emailLabel', __('Email'));
        $row->addTextField('email')->readonly()->setValue($person['email']);


    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

     $form->loadAllValuesFrom($values);

    echo $form->getOutput();
}
?>

<script>
$(document).ready(function() {
    $('.testSMS').on('click', function() {
        if (confirm("<?php echo __('Test SMS').'?'; ?>")) {
            $.ajax({
                url: './modules/Staff/substitutes_manage_edit_smsAjax.php',
                data: {
                    from: "<?php echo $_SESSION[$guid]['preferredName'].' '.$_SESSION[$guid]['surname']; ?>",    
                    phoneNumber: "<?php echo $person['phone1CountryCode'].$person['phone1']; ?>"
                },
                type: 'POST',
                success: function(data) {
                    alert(data);
                }
            });
        }
    });
}) ;
</script>
