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

	$col = $row->addColumn()->addClass('flex items-center justify-end');
		$col->addDate('date')->setValue($date)->setClass('shortWidth');
		$col->addSubmit(__('Go'));

	echo $form->getOutput();

    echo getMessages($guid, $connection2, 'print', dateConvert($guid, $date));
}
?>
