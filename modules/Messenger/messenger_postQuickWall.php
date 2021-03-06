<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('New Quick Wall Message'));

if (isActionAccessible($guid, $connection2, '/modules/Messenger/messenger_postQuickWall.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Messenger/messenger_manage_edit.php&sidebar=true&pupilsightMessengerID=' . $_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    echo "<div class='alert alert-warning'>";
    echo __('This page allows you to quick post a message wall entry to all users, without needing to set a range of options, making it a quick way to post to the Message Wall.');
    echo '</div>';
    //$signature = getEmailSignature($guid, $connection2, $_SESSION[$guid]["pupilsightPersonID"]);
    $form = Form::create('postQuickWall', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/messenger_postQuickWallProcess.php?address=' . $_GET['q']);

    $form->addHiddenValue('messageWall', 'Y');

    /*$sql = "SELECT DISTINCT category FROM pupilsightRole ORDER BY category";
	$result = $pdo->executeQuery(array(), $sql);
	$categories = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_COLUMN, 0) : array();
	foreach($categories as $key => $category) {
		$form->addHiddenValue("roleCategories[$key]", $category);
	}*/

    $form->addRow()->addHeading(__('Delivery Mode'));

    $row = $form->addRow();
    $row->addLabel('messageWallLabel', __('Message Wall'))->description(__('Place this message on user\'s message wall?'));
    $row->addTextField('messageWallText')->readonly()->setValue(__('Yes'));

    $row = $form->addRow();
    $row->addLabel('date1', __('Publication Dates'))->description(__('Select up to three individual dates.'));
    $col = $row->addColumn('date1')->addClass('stacked');
    $col->addDate('date1')->setValue(dateConvertBack($guid, date('Y-m-d')))->required();
    $col->addDate('date2');
    $col->addDate('date3');

    $form->addRow()->addHeading(__('Message Details'));

    $row = $form->addRow();
    $row->addLabel('subject', __('Subject'));
    $row->addTextField('subject')->required()->maxLength(200);
    $display_fields = array();
    $display_fields =  array(
        '' => 'Select Category',
        'Circular' => 'Circular',
        'Timetable' => 'Timetable',
        'Other' => 'Other',
    );

    $row = $form->addRow();
    $row->addLabel('category', __('Category'));
    //$row->addSelect('category')->fromArray($display_fields)->selected($values['category'])->required();
    $row->addSelect('category')->fromArray($display_fields)->required();

    $row = $form->addRow();
    $col = $row->addColumn('body');
    $col->addLabel('body', __('Body'));
    $col->addEditor('body', $guid)->required()->setRows(20)->showMedia(true);

    //TARGETS
    $form->addRow()->addHeading(__('Targets'));
    $roleCategory = getRoleCategory($_SESSION[$guid]["pupilsightRoleIDCurrent"], $connection2);
    //Role
    if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_role")) {
        $row = $form->addRow();
        $row->addLabel('role', __('Role'))->description(__('Users of a certain type.'));
        $row->addYesNoRadio('role')->checked('N')->required();

        $form->toggleVisibilityByClass('role')->onRadio('role')->when('Y');

        $data = array();
        $sql = 'SELECT pupilsightRoleID AS value, CONCAT(name," (",category,")") AS name FROM pupilsightRole ORDER BY name';
        $row = $form->addRow()->addClass('role hiddenReveal');
        $row->addLabel('roles[]', __('Select Roles'));
        $row->addSelect('roles[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(6)->required()->placeholder();

        //Role Category
        $row = $form->addRow();
        $row->addLabel('roleCategory', __('Role Category'))->description(__('Users of a certain type.'));
        $row->addYesNoRadio('roleCategory')->checked('N')->required();

        $form->toggleVisibilityByClass('roleCategory')->onRadio('roleCategory')->when('Y');

        $data = array();
        $sql = 'SELECT DISTINCT category AS value, category AS name FROM pupilsightRole ORDER BY category';
        $row = $form->addRow()->addClass('roleCategory hiddenReveal');
        $row->addLabel('roleCategories[]', __('Select Role Categories'));
        $row->addSelect('roleCategories[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->setSize(4)->required()->placeholder();
    }

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();
}
?>
<script type='text/javascript'>
	$(document).ready(function() {
		$("#bodyedButtonPreview").trigger('click');
	});
</script>