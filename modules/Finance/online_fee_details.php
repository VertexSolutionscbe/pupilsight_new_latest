<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Finance\FeesGateway;

// Module includes
require_once __DIR__ . "/moduleFunctions.php";

if (
    isActionAccessible(
        $guid,
        $connection2,
        "/modules/Finance/fee_transaction_manage.php"
    ) == false
) {
    // Access denied
    $page->addError(__("You do not have access to this action."));
} else {
    $role = $_SESSION[$guid]["pupilsightRoleIDPrimary"];
    $pupilsightSchoolYearID = $_SESSION[$guid]["pupilsightSchoolYearID"];

    $page->breadcrumbs
        ->add("Transaction", "fee_transaction_manage.php")
        ->add(__("Online Payment Details"));

    if (isset($_GET["return"])) {
        returnProcess($guid, $_GET["return"], null, null);
    }

    $search = isset($_GET["search"]) ? $_GET["search"] : "";

    $feesGateway = $container->get(feesGateway::class);
    $criteria = $feesGateway
        ->newQueryCriteria()
        ->pageSize(5000)
        //->sortBy(['id'])
        ->fromPOST();

    // $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
    // //$form->setClass('mb-4');

    // $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/index.php');

    // $row = $form->addRow();
    // $row->addLabel('search', __('Search For (name, Academic Year)'))->setClass('mb-2');
    // $row->addTextField('search')->setValue($criteria->getSearchText())->setClass('mb-2');

    // //$row = $form->addRow()->addClass('right_align');
    // $row->addSearchSubmit($pupilsight->session, __('Clear Search'))->setClass('mb-2');

    // echo $form->getOutput();

    // QUERY
    //echo '<h2>';
    //echo __('Application List');
    //echo '</h2>';
    //  print_r($criteria);
    //  die();

    echo "&nbsp;&nbsp;<a style='float:right;' class=' btn btn-white' id='export_payment_details' title='Export Excel'  >Export</a>";
    $dataSet = $feesGateway->getAllPaymentDetails(
        $criteria,
        $pupilsightSchoolYearID
    );

    $table = DataTable::createPaginated("userManage", $criteria);

    $table->addColumn("serial_number", __("SI No"));
    $table
        ->addColumn("gateway", __("Gateway"))
        ->width("10%")
        ->translatable();

    $table
        ->addColumn("submission_id", __("Submission Id"))
        ->width("10%")
        ->translatable();

    $table
        ->addColumn("officialName", __("Student Name"))
        ->width("10%")
        ->translatable();

    $table
        ->addColumn("class", __("Class"))
        ->width("10%")
        ->translatable();

    $table
        ->addColumn("section", __("Section"))
        ->width("10%")
        ->translatable();

    $table
        ->addColumn("order_id", __("Order Id"))
        ->width("10%")
        ->translatable();

    $table
        ->addColumn("transaction_ref_no", __("Gateway Reference No"))
        ->width("10%")
        ->translatable();

    $table
        ->addColumn("amount", __("Amount"))
        ->width("10%")
        ->translatable();

    $table
        ->addColumn("cdt", __("Date & Time"))
        ->width("10%")
        ->translatable();

    echo $table->render($dataSet);
}
?>

<script>
    $(document).on('click', '#export_payment_details', function () {
        $("#expore_tbl").table2excel({
            name: "Worksheet Name",
            filename: "Online_Payment_Details.xls",
            fileext: ".xls",
            exclude: ".checkall",
            exclude: ".rm_cell",
            exclude_inputs: true,
            columns: [0, 1, 2, 3, 4, 5]

        });
    });
</script>
