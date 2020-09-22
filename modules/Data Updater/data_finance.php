<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_finance.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        $page->breadcrumbs->add(__('Update Finance Data'));

        if ($highestAction == 'Update Finance Data_any') {
            echo '<p>';
            echo __('This page allows a user to request selected finance data updates for any user. If a user does not appear in the list, please visit the Manage Invoicees page to create any missing students.');
            echo '</p>';
        } else {
            echo '<p>';
            echo sprintf(__('This page allows any adult with data access permission to request selected finance data updates for any children in their family. If any of your children do not appear in this list, please contact %1$s.'), "<a href='mailto:".$_SESSION[$guid]['organisationAdministratorEmail']."'>".$_SESSION[$guid]['organisationAdministratorName'].'</a>');
            echo '</p>';
        }

        $customResponces = array();
        $error3 = __('Your request was successful, but some data was not properly saved. An administrator will process your request as soon as possible. <u>You will not see the updated data in the system until it has been processed and approved.</u>');
        if ($_SESSION[$guid]['organisationDBAEmail'] != '' and $_SESSION[$guid]['organisationDBAName'] != '') {
            $error3 .= ' '.sprintf(__('Please contact %1$s if you have any questions.'), "<a href='mailto:".$_SESSION[$guid]['organisationDBAEmail']."'>".$_SESSION[$guid]['organisationDBAName'].'</a>');
        }
        $customResponces['error3'] = $error3;

        $success0 = __('Your request was completed successfully. An administrator will process your request as soon as possible. You will not see the updated data in the system until it has been processed and approved.');
        if ($_SESSION[$guid]['organisationDBAEmail'] != '' and $_SESSION[$guid]['organisationDBAName'] != '') {
            $success0 .= ' '.sprintf(__('Please contact %1$s if you have any questions.'), "<a href='mailto:".$_SESSION[$guid]['organisationDBAEmail']."'>".$_SESSION[$guid]['organisationDBAName'].'</a>');
        }
        $customResponces['success0'] = $success0;

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, $customResponces);
        }

        echo '<h2>';
        echo __('Choose User');
        echo '</h2>';

		$pupilsightFinanceInvoiceeID = isset($_GET['pupilsightFinanceInvoiceeID'])? $_GET['pupilsightFinanceInvoiceeID'] : null;

        $form = Form::create('selectInvoicee', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/data_finance.php');

        if ($highestAction == 'Update Finance Data_any') {
            $data = array();
            $sql = "SELECT username, surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFinanceInvoiceeID FROM pupilsightFinanceInvoicee JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' ORDER BY surname, preferredName";
        } else {
            $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "SELECT pupilsightFamilyAdult.pupilsightFamilyID, pupilsightFamily.name as familyName, child.surname, child.preferredName, child.pupilsightPersonID, pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID
					FROM pupilsightFamilyAdult
					JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
					JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
					JOIN pupilsightPerson as child ON (pupilsightFamilyChild.pupilsightPersonID=child.pupilsightPersonID)
					JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoicee.pupilsightPersonID=child.pupilsightPersonID)
					WHERE pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID
					AND pupilsightFamilyAdult.childDataAccess='Y' AND child.status='Full'
					ORDER BY pupilsightFamily.name, child.surname, child.preferredName";
		}
		$result = $pdo->executeQuery($data, $sql);
		$resultSet = ($result && $result->rowCount() > 0)? $result->fetchAll() : array();

		$invoicees = array_reduce($resultSet, function($carry, $person) use ($highestAction) {
			$id = $person['pupilsightFinanceInvoiceeID'];
			$carry[$id] = formatName('', htmlPrep($person['preferredName']), htmlPrep($person['surname']), 'Student', true);
			if ($highestAction == 'Update Finance Data_any') {
				$carry[$id] .= ' ('.$person['username'].')';
			}
			return $carry;
		}, array());

        $row = $form->addRow();
            $row->addLabel('pupilsightFinanceInvoiceeID', __('Invoicee'))->description(__('Individual for whom invoices are generated.'));
            $row->addSelect('pupilsightFinanceInvoiceeID')
                ->fromArray($invoicees)
                ->required()
                ->selected($pupilsightFinanceInvoiceeID)
                ->placeholder();
         $row = $form->addRow()->addClass('right_align');
            $row->addSubmit();

		echo $form->getOutput();


        if ($pupilsightFinanceInvoiceeID != '') {
            echo '<h2>';
            echo __('Update Data');
            echo '</h2>';

            //Check access to person
            $checkCount = 0;
            if ($highestAction == 'Update Finance Data_any') {
                try {
                    $dataSelect = array('pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
                    $sqlSelect = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFinanceInvoiceeID FROM pupilsightFinanceInvoicee JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID ORDER BY surname, preferredName";
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                }
                $checkCount = $resultSelect->rowCount();
            } else {
                try {
                    $dataCheck = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlCheck = "SELECT pupilsightFamilyAdult.pupilsightFamilyID, name FROM pupilsightFamilyAdult JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y' ORDER BY name";
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                }
                while ($rowCheck = $resultCheck->fetch()) {
                    try {
                        $dataCheck2 = array('pupilsightFamilyID' => $rowCheck['pupilsightFamilyID']);
                        $sqlCheck2 = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFamilyID, pupilsightFinanceInvoiceeID FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND pupilsightFamilyID=:pupilsightFamilyID";
                        $resultCheck2 = $connection2->prepare($sqlCheck2);
                        $resultCheck2->execute($dataCheck2);
                    } catch (PDOException $e) {
                    }
                    while ($rowCheck2 = $resultCheck2->fetch()) {
                        if ($pupilsightFinanceInvoiceeID == $rowCheck2['pupilsightFinanceInvoiceeID']) {
                            ++$checkCount;
                        }
                    }
                }
            }

            if ($checkCount < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                //Check if there is already a pending form for this user
                $existing = false;
                $proceed = false;
                try {
                    $data = array('pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT * FROM pupilsightFinanceInvoiceeUpdate WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID AND pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater AND status='Pending'";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() > 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('Your request failed due to a database error.');
                    echo '</div>';
                } elseif ($result->rowCount() == 1) {
                    $existing = true;
                    echo "<div class='alert alert-warning'>";
                    echo __('You have already submitted a form, which is pending approval by an administrator. If you wish to make changes, please edit the data below, but remember your data will not appear in the system until it has been approved.');
                    echo '</div>';
                    $proceed = true;
                } else {
                    //Get user's data
                    try {
                        $data = array('pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
                        $sql = 'SELECT * FROM pupilsightFinanceInvoicee WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($result->rowCount() != 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The specified record cannot be found.');
                        echo '</div>';
                    } else {
                        $proceed = true;
                    }
                }

                if ($proceed == true) {

                    //Let's go!
					$values = $result->fetch();

					$required = ($highestAction != 'Update Finance Data_any');

					$form = Form::create('updateFinance', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/data_financeProcess.php?pupilsightFinanceInvoiceeID='.$pupilsightFinanceInvoiceeID);

                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
					$form->addHiddenValue('existing', isset($values['pupilsightFinanceInvoiceeUpdateID'])? $values['pupilsightFinanceInvoiceeUpdateID'] : 'N');

					$form->addRow()->addHeading(__('Invoice To'));

					$form->addRow()->addContent(__('If you choose family, future invoices will be sent according to your family\'s contact preferences, which can be changed at a later date by contacting the school. For example you may wish both parents to receive the invoice, or only one. Alternatively, if you choose Company, you can choose for all or only some fees to be covered by the specified company.'))->wrap('<p>', '</p>');

					$row = $form->addRow();
						$row->addLabel('invoiceTo', __('Send Invoices To'));
						$row->addRadio('invoiceTo')
							->fromArray(array('Family' => __('Family'), 'Company' => __('Company')))
							->inline();

					$form->toggleVisibilityByClass('paymentCompany')->onRadio('invoiceTo')->when('Company');

					// COMPANY DETAILS
					$row = $form->addRow()->addClass('paymentCompany');
						$row->addLabel('companyName', __('Company Name'));
						$row->addTextField('companyName')->setRequired($required)->maxLength(100);

					$row = $form->addRow()->addClass('paymentCompany');
						$row->addLabel('companyContact', __('Company Contact Person'));
						$row->addTextField('companyContact')->setRequired($required)->maxLength(100);

					$row = $form->addRow()->addClass('paymentCompany');
						$row->addLabel('companyAddress', __('Company Address'));
						$row->addTextField('companyAddress')->setRequired($required)->maxLength(255);

					$row = $form->addRow()->addClass('paymentCompany');
						$row->addLabel('companyEmail', __('Company Emails'))->description(__('Comma-separated list of email address'));
						$row->addTextField('companyEmail')->setRequired($required);

					$row = $form->addRow()->addClass('paymentCompany');
						$row->addLabel('companyCCFamily', __('CC Family?'))->description(__('Should the family be sent a copy of billing emails?'));
						$row->addYesNo('companyCCFamily')->selected('N');

					$row = $form->addRow()->addClass('paymentCompany');
						$row->addLabel('companyPhone', __('Company Phone'));
						$row->addTextField('companyPhone')->maxLength(20);

					// COMPANY FEE CATEGORIES
					$sqlFees = "SELECT pupilsightFinanceFeeCategoryID as value, name FROM pupilsightFinanceFeeCategory WHERE active='Y' AND NOT pupilsightFinanceFeeCategoryID=1 ORDER BY name";
					$resultFees = $pdo->executeQuery(array(), $sqlFees);

					if (!$resultFees || $resultFees->rowCount() == 0) {
						$form->addHiddenValue('companyAll', 'Y');
					} else {
						$row = $form->addRow()->addClass('paymentCompany');
						$row->addLabel('companyAll', __('Company All?'))->description(__('Should all items be billed to the specified company, or just some?'));
						$row->addRadio('companyAll')->fromArray(array('Y' => __('All'), 'N' => __('Selected')))->checked('Y')->inline();

						$form->toggleVisibilityByClass('paymentCompanyCategories')->onRadio('companyAll')->when('N');

						$row = $form->addRow()->addClass('paymentCompanyCategories');
						$row->addLabel('pupilsightFinanceFeeCategoryIDList[]', __('Company Fee Categories'))
							->description(__('If the specified company is not paying all fees, which categories are they paying?'));
						$row->addCheckbox('pupilsightFinanceFeeCategoryIDList[]')
							->fromResults($resultFees)
							->fromArray(array('0001' => __('Other')))
							->loadFromCSV($values);
					}

					$row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit()->setClass('submit_align submt');

                    $form->loadAllValuesFrom($values);

					echo $form->getOutput();
                }
            }
        }
    }
}
