<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Students/loginAccount.php') == false) {
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

        $type = $_GET['type'];

        echo '<h2>';
        echo __($type.' Content');
        echo '</h2>';

        echo '<h3 style="color:red;">Please Dont Remove $username and $password.</h3>';

        $sqls = 'SELECT content FROM pupilsightContent WHERE type = "'.$type.'" ';
        $results = $connection2->query($sqls);
        $contentData = $results->fetch();

        $form = Form::create('smsEmail', $_SESSION[$guid]['absoluteURL'].'/modules/Students/sms_email_contentProcess.php');

        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('type', $type);
        $row = $form->addRow();

        
        // $content = 'Dear User,
        // Greetings from '.$_SESSION[$guid]['organisationName'].'. Your account has been activated on our School management software "Pupilpod". Kindly login to Pupilpod at the earliest.
        
        // URL: '.$_SESSION[$guid]['absoluteURL'].'
        // Username: $username 
        // Password: $password 
        
        // Note: Please reset the Password by Clicking on "Change Password" link provided at the right top corner of your homepage. For any queries send a email to '.$_SESSION[$guid]['email'].'';

        $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('content', __('Content'));
        $col->addTextArea('content')->setValue($contentData['content'])->required();

        $row = $form->addRow();
        $row->addLabel('', __(''));
        $row->addContent('');

        $row = $form->addRow();
        $row->addLabel('', __(''));
        $row->addSubmit();



        echo $form->getOutput();
        
}

?>

