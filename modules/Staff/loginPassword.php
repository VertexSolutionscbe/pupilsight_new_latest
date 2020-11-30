<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Staff/loginAccount.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    // $page->breadcrumbs
    //     ->add(__('Manage Fee Receipts Template'), 'fee_receipts_manage.php')
    //     ->add(__('Add Fee Receipts Template'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

   
        echo '<h2>';
        echo __('Password for Accounts');
        echo '</h2>';

        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        $password = implode($pass);

        $form = Form::create('filter', '');

        $form->setClass('noIntBorder fullWidth');
        $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view.php');
        $row = $form->addRow();

        //$col = $row->addColumn()->setClass('newdes');
        $row->addLabel('pass', __('Enter Password for Login Id '));
        $row->addTextField('pass')->setValue($password)->placeholder('Enter Password for Login Id')->required();

        // $content = 'Dear User,
        // Greetings from '.$_SESSION[$guid]['organisationName'].'. Your account has been activated on our School management software "Pupilpod". Kindly login to Pupilpod at the earliest.
        
        // URL: '.$_SESSION[$guid]['absoluteURL'].'
        // Username: $username 
        // Password: $password 
        
        // Note: Please reset the Password by Clicking on "Change Password" link provided at the right top corner of your homepage. For any queries send a email to '.$_SESSION[$guid]['email'].'';

        $types = array('Sms' => 'Sms', 'Email' => 'Email');
        $row = $form->addRow();
        //$col = $row->addColumn()->setClass('newdes');
        $row->addLabel('type', __('Notification'));
        $row->addCheckBox('type')->fromArray($types);

        $row = $form->addRow();
        $row->addLabel('', __(''));
        $row->addContent('');

        $row = $form->addRow();
        $row->addLabel('', __(''));
        $row->addContent('<a class="btn btn-primary" id="donePassword">Done</a>&nbsp;&nbsp;<a class="btn btn-primary" id="closePassword">Close</a>');



        echo $form->getOutput();
        
}

?>

