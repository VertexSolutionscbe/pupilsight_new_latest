<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs
    ->add(__('Manage Catalog'), 'library_manage_catalog.php')
    ->add(__('Add Item'));

if (isActionAccessible($guid, $connection2, '/modules/Library/library_manage_catalog_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $urlParamKeys = array('name' => '', 'pupilsightLibraryTypeID' => '', 'pupilsightSpaceID' => '', 'status' => '', 'pupilsightPersonIDOwnership' => '', 'typeSpecificFields' => '');

    $urlParams = array_intersect_key($_GET, $urlParamKeys);
    $urlParams = array_merge($urlParamKeys, $urlParams);

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Library/library_manage_catalog_edit.php&pupilsightLibraryItemID='.$_GET['editID'].'&'.http_build_query($urlParams);
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    if (array_filter($urlParams)) {
        echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Library/library_manage_catalog.php&'.http_build_query($urlParams)."'>".__('Back to Search Results').'</a>';
        echo '</div>';
	}

    $form = Form::create('libraryCatalog', $_SESSION[$guid]['absoluteURL'].'/modules/Library/library_manage_catalog_addProcess.php?'.http_build_query($urlParams));
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $form->addRow()->addHeading(__('Catalog Type'));

    $sql = "SELECT pupilsightLibraryTypeID AS value, name FROM pupilsightLibraryType WHERE active='Y' ORDER BY name";
    $row = $form->addRow();
        $row->addLabel('pupilsightLibraryTypeID', __('Type'));
        $row->addSelect('pupilsightLibraryTypeID')
            ->fromQuery($pdo, $sql, array())
            ->placeholder()
            ->required()
            ->selected($urlParams['pupilsightLibraryTypeID']);

    $form->toggleVisibilityByClass('general')->onSelect('pupilsightLibraryTypeID')->whenNot('Please select...');

    $form->addRow()->addHeading(__('General Details'))->addClass('general');

    $row = $form->addRow()->addClass('general');
        $row->addLabel('name', __('Name'))->description(__('Volume or product name.'));
        $row->addTextField('name')->required()->maxLength(255);

    $row = $form->addRow()->addClass('general');
        $row->addLabel('idCheck', __('ID'));
        $row->addTextField('idCheck')
            ->uniqueField('./modules/Library/library_manage_catalog_idCheckAjax.php')
            ->required()
            ->maxLength(255);

    $row = $form->addRow()->addClass('general');
        $row->addLabel('producer', __('Author/Brand'))->description(__('Who created the item?'));
        $row->addTextField('producer')->required()->maxLength(255);

    $row = $form->addRow()->addClass('general');
        $row->addLabel('vendor', __('Vendor'))->description(__('Who supplied the item?'));
        $row->addTextField('vendor')->maxLength(100);

    $row = $form->addRow()->addClass('general');
        $row->addLabel('purchaseDate', __('Purchase Date'));
        $row->addDate('purchaseDate');

    $row = $form->addRow()->addClass('general');
        $row->addLabel('invoiceNumber', __('Invoice Number'));
        $row->addTextField('invoiceNumber')->maxLength(50);

    $row = $form->addRow()->addClass('general');
        $row->addLabel('imageType', __('Image Type'));
        $row->addSelect('imageType')->fromArray(array('File' => __('File'), 'Link' => __('Link')))->placeholder();

    $form->toggleVisibilityByClass('imageFile')->onSelect('imageType')->when('File');

    $row = $form->addRow()->addClass('imageFile');
        $row->addLabel('imageFile', __('Image File'))
            ->description(__('240px x 240px or smaller.'));
        $row->addFileUpload('imageFile')
            ->accepts('.jpg,.jpeg,.gif,.png')
            ->setMaxUpload(false)
            ->required();

    $form->toggleVisibilityByClass('imageLink')->onSelect('imageType')->when('Link');

    $row = $form->addRow()->addClass('imageLink');
        $row->addLabel('imageLink', __('Image Link'))
            ->description(__('240px x 240px or smaller.'));
        $row->addURL('imageLink')->maxLength(255)->required();

    $row = $form->addRow()->addClass('general');
        $row->addLabel('pupilsightSpaceID', __('Location'));
        $row->addSelectSpace('pupilsightSpaceID')->placeholder();

    $row = $form->addRow()->addClass('general');
        $row->addLabel('locationDetail', __('Location Detail'))->description(__('Shelf, cabinet, sector, etc'));
        $row->addTextField('locationDetail')->maxLength(255);

    $row = $form->addRow()->addClass('general');
        $row->addLabel('ownershipType', __('Ownership Type'));
        $row->addSelect('ownershipType')->fromArray(array('School' => __('School'), 'Individual' => __('Individual')))->placeholder();

    $form->toggleVisibilityByClass('ownershipSchool')->onSelect('ownershipType')->when('School');

    $row = $form->addRow()->addClass('ownershipSchool');
        $row->addLabel('pupilsightPersonIDOwnershipSchool', __('Main User'))->description(__('Person the device is assigned to.'));
        $row->addSelectUsers('pupilsightPersonIDOwnershipSchool')->placeholder();

    $form->toggleVisibilityByClass('ownershipIndividual')->onSelect('ownershipType')->when('Individual');

    $row = $form->addRow()->addClass('ownershipIndividual');
        $row->addLabel('pupilsightPersonIDOwnershipIndividual', __('Owner'));
        $row->addSelectUsers('pupilsightPersonIDOwnershipIndividual')->placeholder();

    $sql = "SELECT pupilsightDepartmentID AS value, name FROM pupilsightDepartment ORDER BY name";
    $row = $form->addRow()->addClass('general');
        $row->addLabel('pupilsightDepartmentID', __('Department'))->description(__('Which department is responsible for the item?'));
        $row->addSelect('pupilsightDepartmentID')->fromQuery($pdo, $sql, array())->placeholder();

    $row = $form->addRow()->addClass('general');
        $row->addLabel('bookable', __('Bookable As Facility?'))->description(__('Can item be booked via Facility Booking in Timetable? Useful for laptop carts, etc.'));
        $row->addYesNo('bookable')->selected('N');

    $row = $form->addRow()->addClass('general');
        $row->addLabel('borrowable', __('Borrowable?'))->description(__('Is item available for loan?'));
        $row->addYesNo('borrowable');

    $statuses = array(
        'Available' => __('Available'),
        'In Use' => __('In Use'),
        'Reserved' => __('Reserved'),
        'Decommissioned' => __('Decommissioned'),
        'Lost' => __('Lost'),
        'Repair' => __('Repair')
    );
    $row = $form->addRow()->addClass('general');
        $row->addLabel('status', __('Status?'))->description(__('Initial availability.'));
        $row->addSelect('status')->fromArray($statuses)->required();

    $row = $form->addRow()->addClass('general');
        $row->addLabel('replacement', __('Plan Replacement?'));
        $row->addYesNo('replacement')->required()->selected('N');

    $form->toggleVisibilityByClass('replacement')->onSelect('replacement')->when('Y');

    $row = $form->addRow()->addClass('replacement');
            $row->addLabel('pupilsightSchoolYearIDReplacement', __('Replacement Year'))->description(__('When is this item scheduled for replacement.'));
            $row->addSelectSchoolYear('pupilsightSchoolYearIDReplacement', 'All', 'DESC');

    $row = $form->addRow()->addClass('replacement');
        $row->addLabel('replacementCost', __('Replacement Cost'));
        $row->addCurrency('replacementCost')->maxLength(9);

    $conditions = array(
        'As New' => __('As New'),
        'Lightly Worn' => __('Lightly Worn'),
        'Moderately Worn' => __('Moderately Worn'),
        'Damaged' => __('Damaged'),
        'Unusable' => __('Unusable')
    );
    $row = $form->addRow()->addClass('general');
        $row->addLabel('physicalCondition', __('Physical Condition'))->description(__('Initial availability.'));
        $row->addSelect('physicalCondition')->fromArray($conditions)->placeholder();

    $row = $form->addRow()->addClass('general');
        $row->addLabel('comment', __('Comments/Notes'));
        $row->addTextArea('comment')->setRows(10);

    $form->addRow()->addHeading(__('Type-Specific Details'))->addClass('general');

    // Type-specific form fields loaded via ajax
    $row = $form->addRow('detailsRow')->addContent('')->addClass('general');

    $row = $form->addRow()->addClass('general');
        $row->addSubmit();

    echo $form->getOutput();
}
?>
<script type='text/javascript'>
	$(document).ready(function(){
		$('#pupilsightLibraryTypeID').change(function(){
			var path = '<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/Library/library_manage_catalog_fields_ajax.php'; ?>';

            $('#detailsRow').html("<div id='details' name='details' style='min-height: 100px; text-align: center'><img style='margin: 10px 0 5px 0' src='<?php echo $_SESSION[$guid]['absoluteURL']; ?>/themes/<?php echo $_SESSION[$guid]['pupilsightThemeName']; ?>/img/loading.gif' alt='Loading' onclick='return false;' /><br/>Loading</div>");

			$('#detailsRow').load(path, { 'pupilsightLibraryTypeID': $(this).val() });
		});
	});
</script>
