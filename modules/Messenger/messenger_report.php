<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Messenger/messenger_report.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    $dateFormat = $_SESSION[$guid]['i18n']['dateFormatPHP'];
    $date = isset($_REQUEST['date'])? $_REQUEST['date'] : date($dateFormat);
    $fromdate = isset($_REQUEST['fromdate'])? $_REQUEST['fromdate'] : date($dateFormat);
    $msgtype = isset($_REQUEST['msgtype'])? $_REQUEST['msgtype'] : 'All';

    $page->breadcrumbs->add(($date === date($dateFormat)) ?
        __('Messenger Report') :
        __('View Messages').' ('.$fromdate.' to '.$date.')');

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

    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/messenger_report.php');
    $form->setClass('blank fullWidth');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow()->addClass('flex flex-wrap');

    $link = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/messenger_report.php';
    $prevDay = DateTime::createFromFormat($dateFormat, $date)->modify('-1 day')->format($dateFormat);
    $nextDay = DateTime::createFromFormat($dateFormat, $date)->modify('+1 day')->format($dateFormat);

    //$col = $row->addColumn()->addClass('flex items-center');
    //	$col->addButton(__('Previous Day'))->addClass('btn btn-link mr-px')->onClick("window.location.href='{$link}&date={$prevDay}'");
    //	$col->addButton(__('Next Day'))->addClass('btn btn-link')->onClick("window.location.href='{$link}&date={$nextDay}'");


    $displaymsgwalltype = array();
    $displaymsgwalltype =  array('All'=>'All',
        'sms' =>'SMS',
        'email' =>'Email',
    );


    $col = $row->addColumn()->addClass('col-md-4 mb-2');
    $col->addLabel('msgtype', __('Message Type'));
    $col->addSelect('msgtype')->fromArray($displaymsgwalltype)->selected($msgtype);

    $col = $row->addColumn()->addClass('col-md-4 mb-2');
    $col->addLabel('', __(''));
    $col->addSubmit(__('Go'));

    echo $form->getOutput();
    echo "<div class='row'><div class='col-md-8'><a class='btn btn-primary' id='downloadLink' onclick='exportF(this)'>Export</a></div><div class='col-md-4'><input id='myInput' type='text' onkeyup='myFunction()' placeholder='Search' /></div></div>";
    echo queryMembersreceipt($guid, $connection2, 'print', $msgtype);
}
?>
<script>
    function exportF(elem) {
        var table = document.getElementById("example");
        var html = table.outerHTML;
        var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url
        elem.setAttribute("href", url);
        elem.setAttribute("download", "export.xls"); // Choose the file name
        return false;
    }
</script>
<script>
    function myFunction() {
        var input, filter, table, tr, td, i;
        input = document.getElementById("myInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("example");
        tr = table.getElementsByTagName("tr");
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0]; // for column one
            td1 = tr[i].getElementsByTagName("td")[1]; // for column two
            td2 = tr[i].getElementsByTagName("td")[2]; // for column three
            td3 = tr[i].getElementsByTagName("td")[3]; // for column four
            td4 = tr[i].getElementsByTagName("td")[4]; // for column five
            td5 = tr[i].getElementsByTagName("td")[5]; // for column six
            td6 = tr[i].getElementsByTagName("td")[6]; // for column seven
            /* ADD columns here that you want you to filter to be used on */
            if (td) {
                if ( (td.innerHTML.toUpperCase().indexOf(filter) > -1) || (td1.innerHTML.toUpperCase().indexOf(filter) > -1) || (td2.innerHTML.toUpperCase().indexOf(filter) > -1) || (td3.innerHTML.toUpperCase().indexOf(filter) > -1) || (td4.innerHTML.toUpperCase().indexOf(filter) > -1) || (td5.innerHTML.toUpperCase().indexOf(filter) > -1) || (td6.innerHTML.toUpperCase().indexOf(filter) > -1) )  {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
</script>