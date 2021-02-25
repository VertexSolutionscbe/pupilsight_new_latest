<?php
/**
 * Created by PhpStorm.
 * User: Preetam
 * Date: 25-Feb-21
 * Time: 2:58 PM
 */


use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Helper\HelperGateway;
use Pupilsight\Domain\Messenger\GroupGateway;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;


$page->breadcrumbs
    ->add(__('Messagewall Category'), 'messagewall_category_master.php');


if (isActionAccessible($guid, $connection2, '/modules/Messenger/messagewall_category_master.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {

    if($_GET['return']=='errorcategoryname'){
        echo "<div class='alert alert-danger'>This Category already exists. Category name must be unique.</div>";
    }else{
        returnProcess($guid, $_GET['return'], null, null);
    }

    $categorystatus = array('1'=> 'Active','0'=> 'Inactive');

    $form = Form::create('categories', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . "/messagewall_category_masterProcess.php");
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('createdby', $_SESSION[$guid]['pupilsightPersonID']);

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('categoryname', __('Category Name'))->addClass('dte');
    $col->addTextField('categoryname')->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('categorystatus', __('Category Status'))->addClass('dte');
    $col->addSelect('categorystatus')->fromArray($categorystatus)->required();

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();

    echo '<h2>';
    echo __('Current Messenger Categories');
    echo '</h2>';
    $groupGateway = $container->get(GroupGateway::class);
    $criteria = $groupGateway->newQueryCriteria()
        ->sortBy(['categoryname'])
        ->pageSize(5000)
        ->fromPOST();

    $messengercategory = $groupGateway->queryMessengercategory($criteria);

    $table = DataTable::createPaginated('messengercategory', $criteria);

    $table->addColumn('messagewall_category_masterID', __('Sl no'))
        ->sortable(['messagewall_category_masterID', 'Sl no']);

    $table->addColumn('categoryname', __('Category Name'))
        ->sortable(['categoryname', 'Category Name']);

    $table->addColumn('categorystatus', __('Status'))
        ->sortable(['categorystatus', 'Status']);

    echo $table->render($messengercategory);
}
?>