<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Messenger/messageWall_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    $dateFormat = $_SESSION[$guid]['i18n']['dateFormatPHP'];
    $date = isset($_REQUEST['date'])? $_REQUEST['date'] : date($dateFormat);
    $fromdate = isset($_REQUEST['fromdate'])? $_REQUEST['fromdate'] : date($dateFormat);
    $category = isset($_REQUEST['category'])? $_REQUEST['category'] : 'All';

    $page->breadcrumbs->add(($date === date($dateFormat)) ?
        __('Today\'s Messages').' ('.$date.')' :
        __('View Messages').' ('.$date.')');

    if (isset($_GET['return'])) {
        $status = (!empty($_GET['status'])) ? $_GET['status'] : __('Unknown');
        $emailLink = getSettingByScope($connection2, 'System', 'emailLink');
        if (empty($emailLink)) {
            $suggest = sprintf(__('Why not read the messages below, or %1$scheck your email%2$s?'), '', '');
        }
        else {
            $suggest = sprintf(__('Why not read the messages below, or %1$scheck your email%2$s?'), "<a target='_blank' href='$emailLink'>", '</a>');
        }
        $suggest = '<b>'.$suggest.'</b>';
        returnProcess($guid, $_GET['return'], null, array('message0' => sprintf(__('Attendance has been taken for you today. Your current status is: %1$s.'), "<b>".$status."</b>").'<br/><br/>'.$suggest));
    }

	$form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/messageWall_view.php');
	$form->setClass('blank fullWidth');

	$form->addHiddenValue('address', $_SESSION[$guid]['address']);

	$row = $form->addRow()->addClass('flex flex-wrap');

	$link = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/messageWall_view.php';
	$prevDay = DateTime::createFromFormat($dateFormat, $date)->modify('-1 day')->format($dateFormat);
	$nextDay = DateTime::createFromFormat($dateFormat, $date)->modify('+1 day')->format($dateFormat);

	$col = $row->addColumn()->addClass('flex items-center');
		$col->addButton(__('Previous Day'))->addClass('btn btn-link mr-px')->onClick("window.location.href='{$link}&date={$prevDay}'");
		$col->addButton(__('Next Day'))->addClass('btn btn-link')->onClick("window.location.href='{$link}&date={$nextDay}'");

	$col = $row->addColumn();
        $col->addLabel('fromdate', __('From Date'));
	    $col->addDate('fromdate')->setValue($fromdate);
    $col = $row->addColumn();
        $col->addLabel('date', __('To Date'));
		$col->addDate('date')->setValue($date);

    $displaycategory = array();
    $displaycategory =  array('All'=>'Select Category',
        'All' =>'All',
        'Circular' =>'Circular',
        'Timetable' =>'Timetable',
        'Other' =>'Other',
    );

    $col = $row->addColumn();
    $col->addLabel('category', __('Category'));
    $col->addSelect('category')->fromArray($displaycategory)->selected($values['category']);

        $col->addSubmit(__('Go'));

	echo $form->getOutput();

    echo getMessages($guid, $connection2, 'print', dateConvert($guid, $date), dateConvert($guid, $fromdate) , $category);
}
?>
