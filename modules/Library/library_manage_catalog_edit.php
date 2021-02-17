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
    ->add(__('Edit Item'));

if (isActionAccessible($guid, $connection2, '/modules/Library/library_manage_catalog_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightLibraryItemID = $_GET['pupilsightLibraryItemID'];
    if ($pupilsightLibraryItemID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID);
            $sql = 'SELECT pupilsightLibraryItem.*, pupilsightLibraryType.name AS type FROM pupilsightLibraryItem JOIN pupilsightLibraryType ON (pupilsightLibraryItem.pupilsightLibraryTypeID=pupilsightLibraryType.pupilsightLibraryTypeID) WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            //Let's go!
			$values = $result->fetch();

			$urlParamKeys = array('name' => '', 'pupilsightLibraryTypeID' => '', 'pupilsightSpaceID' => '', 'status' => '', 'pupilsightPersonIDOwnership' => '', 'typeSpecificFields' => '');

			$urlParams = array_intersect_key($_GET, $urlParamKeys);
			$urlParams = array_merge($urlParamKeys, $urlParams);

            if ($_GET['name'] != '' or $_GET['pupilsightLibraryTypeID'] != '' or $_GET['pupilsightSpaceID'] != '' or $_GET['status'] != '' or $_GET['pupilsightPersonIDOwnership'] != '' or $_GET['typeSpecificFields'] != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Library/library_manage_catalog.php&'.http_build_query($urlParams)."'>".__('Back to Search Results').'</a>';
                echo '</div>';
			}

			$form = Form::create('libraryCatalog', $_SESSION[$guid]['absoluteURL'].'/modules/Library/library_manage_catalog_editProcess.php?'.http_build_query($urlParams));
			$form->setFactory(DatabaseFormFactory::create($pdo));

			$form->addHiddenValue('address', $_SESSION[$guid]['address']);
			$form->addHiddenValue('pupilsightLibraryTypeID', $values['pupilsightLibraryTypeID']);
			$form->addHiddenValue('pupilsightLibraryItemID', $pupilsightLibraryItemID);

			$form->addRow()->addHeading(__('Catalog Type'));

			$sql = "SELECT pupilsightLibraryTypeID AS value, name FROM pupilsightLibraryType WHERE active='Y' ORDER BY name";
			$row = $form->addRow();
				$row->addLabel('type', __('Type'));
				$row->addTextField('type')->required()->readOnly();

			$form->toggleVisibilityByClass('general')->onSelect('pupilsightLibraryTypeID')->whenNot('Please select...');

			$form->addRow()->addHeading(__('General Details'));

			$row = $form->addRow();
				$row->addLabel('name', __('Name'))->description(__('Volume or product name.'));
				$row->addTextField('name')->required()->maxLength(255);

			$row = $form->addRow();
				$row->addLabel('id', __('ID'));
				$row->addTextField('id')
					->uniqueField('./modules/Library/library_manage_catalog_idCheckAjax.php', array('pupilsightLibraryItemID' => $pupilsightLibraryItemID))
					->required()
					->maxLength(255);

			$row = $form->addRow();
				$row->addLabel('producer', __('Author/Brand'))->description(__('Who created the item?'));
				$row->addTextField('producer')->required()->maxLength(255);

			$row = $form->addRow();
				$row->addLabel('vendor', __('Vendor'))->description(__('Who supplied the item?'));
				$row->addTextField('vendor')->maxLength(100);

			$row = $form->addRow();
				$row->addLabel('purchaseDate', __('Purchase Date'));
				$row->addDate('purchaseDate');

			$row = $form->addRow();
				$row->addLabel('invoiceNumber', __('Invoice Number'));
				$row->addTextField('invoiceNumber')->maxLength(50);

			$row = $form->addRow();
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
				$row->addURL('imageLink')->maxLength(255)->required()->setValue($values['imageLocation']);

			$row = $form->addRow();
				$row->addLabel('pupilsightSpaceID', __('Location'));
				$row->addSelectSpace('pupilsightSpaceID')->placeholder();

			$row = $form->addRow();
				$row->addLabel('locationDetail', __('Location Detail'))->description(__('Shelf, cabinet, sector, etc'));
				$row->addTextField('locationDetail')->maxLength(255);

			$row = $form->addRow();
				$row->addLabel('ownershipType', __('Ownership Type'));
				$row->addSelect('ownershipType')->addId('ownershipType')->fromArray(array('School' => __('School'), 'Individual' => __('Individual')))->placeholder();
			
			$sql = 'SELECT a.type, b.pupilsightPersonID, b.officialName FROM pupilsightStaff AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE b.officialName != "" ';
			$result = $connection2->query($sql);
			$staffs = $result->fetchAll();
			$owner1 = array('' => 'Please Select ');
			
			foreach ($staffs as $dt) {
				$owner2[$dt['pupilsightPersonID']] = $dt['officialName'];
			}
			$owner = $owner1 + $owner2;
			
				

			$form->toggleVisibilityByClass('ownershipSchool')->onSelect('ownershipType')->when('School');

			$row = $form->addRow()->addClass('ownershipSchool');
				$row->addLabel('pupilsightPersonIDOwnershipSchool', __('Main User'))->description(__('Person the device is assigned to.'));
				$row->addSelectUsers('pupilsightPersonIDOwnershipSchool')->addId('pupilsightPersonIDOwnershipSchool')->placeholder()->fromArray($owner)->selected($values['pupilsightPersonIDOwnership']);

			$form->toggleVisibilityByClass('ownershipIndividual')->onSelect('ownershipType')->when('Individual');

			$row = $form->addRow()->addClass('ownershipIndividual');
				$row->addLabel('pupilsightPersonIDOwnershipIndividual', __('Owner'));
				$row->addSelectUsers('pupilsightPersonIDOwnershipIndividual')->addId('pupilsightPersonIDOwnershipIndividual')->placeholder()->fromArray($owner)->selected($values['pupilsightPersonIDOwnership']);

			$sql = "SELECT pupilsightDepartmentID AS value, name FROM pupilsightDepartment ORDER BY name";
			$row = $form->addRow();
				$row->addLabel('pupilsightDepartmentID', __('Department'))->description(__('Which department is responsible for the item?'));
				$row->addSelect('pupilsightDepartmentID')->fromQuery($pdo, $sql, array())->placeholder();

			$row = $form->addRow();
				$row->addLabel('bookable', __('Bookable As Facility?'))->description(__('Can item be booked via Facility Booking in Timetable? Useful for laptop carts, etc.'));
				$row->addYesNo('bookable');

			$row = $form->addRow();
				$row->addLabel('borrowable', __('Borrowable?'))->description(__('Is item available for loan?'));
				$row->addYesNo('borrowable');


			$form->toggleVisibilityByClass('statusBorrowable')->onSelect('borrowable')->when('Y');
			$form->toggleVisibilityByClass('statusNotBorrowable')->onSelect('borrowable')->when('N');

			$statuses = array(
				'Available' => __('Available'),
				'In Use' => __('In Use'),
				'Reserved' => __('Reserved'),
				'Decommissioned' => __('Decommissioned'),
				'Lost' => __('Lost'),
				'Repair' => __('Repair')
			);
			$row = $form->addRow()->addClass('statusBorrowable');
				$row->addLabel('statusBorrowable', __('Status?'));
				$row->addTextField('statusBorrowable')->required()->readOnly()->setValue(__('Available'));

			$row = $form->addRow()->addClass('statusNotBorrowable');
				$row->addLabel('statusNotBorrowable', __('Status?'));
				$row->addSelect('statusNotBorrowable')->fromArray($statuses)->required();

			$row = $form->addRow();
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
			$row = $form->addRow();
				$row->addLabel('physicalCondition', __('Physical Condition'))->description(__('Initial availability.'));
				$row->addSelect('physicalCondition')->fromArray($conditions)->placeholder();

			$row = $form->addRow();
				$row->addLabel('comment', __('Comments/Notes'));
				$row->addTextArea('comment')->setRows(10);

			$form->addRow()->addHeading(__('Type-Specific Details'));

			// Type-specific form fields loaded via ajax
			$row = $form->addRow('detailsRow')->addContent('');

			$row = $form->addRow();
				$row->addSubmit();

			$form->loadAllValuesFrom($values);

			echo $form->getOutput();
        }
    }
}
?>
<script type='text/javascript'>
	$(document).ready(function(){

		var path = '<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/Library/library_manage_catalog_fields_ajax.php'; ?>';

		$('#detailsRow').html("<div id='details' name='details' style='min-height: 100px; text-align: center'><img style='margin: 10px 0 5px 0' src='<?php echo $_SESSION[$guid]['absoluteURL']; ?>/themes/<?php echo $_SESSION[$guid]['pupilsightThemeName']; ?>/img/loading.gif' alt='Loading' onclick='return false;' /><br/>Loading</div>");

		$('#detailsRow').load(path, { 'pupilsightLibraryTypeID': '<?php echo $values['pupilsightLibraryTypeID']; ?>', 'pupilsightLibraryItemID': '<?php echo $pupilsightLibraryItemID; ?>' });

	});

	$('#ownershipType').change(function(){
		//alert('Changed');
		$('#pupilsightPersonIDOwnershipSchool').val('');
		$('#pupilsightPersonIDOwnershipIndividual').val('');
	});
</script>
