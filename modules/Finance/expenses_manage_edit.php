<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenses_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if (isActionAccessible($guid, $connection2, '/modules/Finance/expenses_manage_add.php', 'Manage Expenses_all') == false) {
        echo "<div class='error'>";
        echo __('You do not have access to this action.');
        echo '</div>';
    } else {
        //Proceed!
        $pupilsightFinanceBudgetCycleID = $_GET['pupilsightFinanceBudgetCycleID'];
    
        $urlParams = compact('pupilsightFinanceBudgetCycleID');        
        
        $page->breadcrumbs
            ->add(__('Manage Expenses'), 'expenses_manage.php',  $urlParams)
            ->add(__('Edit Expense'));        

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if params are specified
        $pupilsightFinanceExpenseID = isset($_GET['pupilsightFinanceExpenseID'])? $_GET['pupilsightFinanceExpenseID'] : '';
        $status2 = isset($_GET['status2'])? $_GET['status2'] : '';
        $pupilsightFinanceBudgetID2 = isset($_GET['pupilsightFinanceBudgetID2'])? $_GET['pupilsightFinanceBudgetID2'] : '';
        $pupilsightFinanceBudgetID = '';
        if ($pupilsightFinanceExpenseID == '' or $pupilsightFinanceBudgetCycleID == '') {
            echo "<div class='error'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            $budgetsAccess = false;
            if ($highestAction == 'Manage Expenses_all') { //Access to everything {
                $budgetsAccess = true;
            } else {
                //Check if have Full or Write in any budgets
                $budgets = getBudgetsByPerson($connection2, $_SESSION[$guid]['pupilsightPersonID']);
                if (is_array($budgets) && count($budgets)>0) {
                    foreach ($budgets as $budget) {
                        if ($budget[2] == 'Full' or $budget[2] == 'Write') {
                            $budgetsAccess = true;
                        }
                    }
                }
            }

            if ($budgetsAccess == false) {
                echo "<div class='error'>";
                echo __('You do not have Full or Write access to any budgets.');
                echo '</div>';
            } else {
                //Get and check settings
                $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
                $budgetLevelExpenseApproval = getSettingByScope($connection2, 'Finance', 'budgetLevelExpenseApproval');
                $expenseRequestTemplate = getSettingByScope($connection2, 'Finance', 'expenseRequestTemplate');
                if ($expenseApprovalType == '' or $budgetLevelExpenseApproval == '') {
                    echo "<div class='error'>";
                    echo __('An error has occurred with your expense and budget settings.');
                    echo '</div>';
                } else {
                    //Check if there are approvers
                    try {
                        $data = array();
                        $sql = "SELECT * FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full'";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo $e->getMessage();
                    }

                    if ($result->rowCount() < 1) {
                        echo "<div class='error'>";
                        echo __('An error has occurred with your expense and budget settings.');
                        echo '</div>';
                    } else {
                        //Ready to go! Just check record exists and we have access, and load it ready to use...
                        try {
                            //Set Up filter wheres
                            $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                            $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget, surname, preferredName, 'Full' AS access
									FROM pupilsightFinanceExpense
									JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
									JOIN pupilsightPerson ON (pupilsightFinanceExpense.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID)
									WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo "<div class='error'>".$e->getMessage().'</div>';
                        }

                        if ($result->rowCount() != 1) {
                            echo "<div class='error'>";
                            echo __('The specified record cannot be found.');
                            echo '</div>';
                        } else {
                            //Let's go!
                            $values = $result->fetch();

                            if ($status2 != '' or $pupilsightFinanceBudgetID2 != '') {
                                echo "<div class='linkTop'>";
                                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/expenses_manage.php&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=$status2&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2'>".__('Back to Search Results').'</a>';
                                echo '</div>';
                            }
                            
                            // Get budget allocation & allocated amounts
                            $budgetAllocation = getBudgetAllocation($pdo, $pupilsightFinanceBudgetCycleID, $values['pupilsightFinanceBudgetID']);
                            $budgetAllocated = getBudgetAllocated($pdo, $pupilsightFinanceBudgetCycleID, $values['pupilsightFinanceBudgetID']);
                            $budgetRemaining = (is_numeric($budgetAllocation) && is_numeric($budgetAllocated))? ($budgetAllocation - $budgetAllocated) : __('N/A');
                            
							$form = Form::create('expenseManage', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/expenses_manage_editProcess.php');
							$form->setFactory(DatabaseFormFactory::create($pdo));

							$form->addHiddenValue('address', $_SESSION[$guid]['address']);
							$form->addHiddenValue('status2', $status2);
							$form->addHiddenValue('pupilsightFinanceExpenseID', $pupilsightFinanceExpenseID);
							$form->addHiddenValue('pupilsightFinanceBudgetID', $pupilsightFinanceBudgetID);
							$form->addHiddenValue('pupilsightFinanceBudgetID2', $pupilsightFinanceBudgetID2);
							$form->addHiddenValue('pupilsightFinanceBudgetCycleID', $pupilsightFinanceBudgetCycleID);

							$form->addRow()->addHeading(__('Basic Information'));
							
							$cycleName = getBudgetCycleName($pupilsightFinanceBudgetCycleID, $connection2);
							$row = $form->addRow();
								$row->addLabel('name', __('Budget Cycle'));
								$row->addTextField('name')->setValue($cycleName)->maxLength(20)->required()->readonly();

							$row = $form->addRow();
								$row->addLabel('budgetName', __('Budget'));
								$row->addTextField('budgetName')->setValue($values['budget'])->required()->readonly();

							$row = $form->addRow();
								$row->addLabel('title', __('Title'));
								$row->addTextField('title')->required()->readonly();

							$row = $form->addRow();
								$row->addLabel('status', __('Status'));
							if ($values['status'] == 'Requested' or $values['status'] == 'Approved' or $values['status'] == 'Ordered') {
								$statuses = array(
									'Ordered' => __('Ordered'),
									'Paid' => __('Paid'),
									'Cancelled' => __('Cancelled'),
								);
								if ($values['status'] == 'Requested') {
									$statuses = array(
										'Requested' => __('Requested'),
										'Approved' => __('Approved'),
										'Rejected' => __('Rejected'),
									) + $statuses;
								}
								if ($values['status'] == 'Approved') {
									$statuses = array('Approved' => __('Approved')) + $statuses;
								}

								$row->addSelect('status')->fromArray($statuses)->required()->placeholder();
							} else {
								$row->addTextField('status')->required()->readonly();
							}

							$row = $form->addRow();
								$col = $row->addColumn();
								$col->addLabel('body', __('Description'));
								$col->addContent($values['body']);

							$row = $form->addRow();
								$row->addLabel('purchaseBy', __('Purchase By'));
								$row->addTextField('purchaseBy')->required()->readonly();

							$row = $form->addRow();
								$col = $row->addColumn();
								$col->addLabel('purchaseDetails', __('Purchase Details'));
								$col->addContent($values['purchaseDetails']);

                            $form->addRow()->addHeading(__('Budget Tracking'));
                            
                            $row = $form->addRow();
                                $row->addLabel('cost', __('Total Cost'));
                                $row->addCurrency('cost')->required()->readonly()->setValue(number_format($values['cost'], 2, '.', ','));

							$row = $form->addRow();
								$row->addLabel('countAgainstBudget', __('Count Against Budget'))->description(__('For tracking purposes, should the item be counted against the budget? If immediately offset by some revenue, perhaps not.'));
                                $row->addYesNo('countAgainstBudget')->required();
                                
                            $form->toggleVisibilityByClass('budgetInfo')->onSelect('countAgainstBudget')->when('Y');

                            $budgetAllocationLabel = (is_numeric($budgetAllocation))? number_format($budgetAllocation, 2, '.', ',') : $budgetAllocation;
                            $row = $form->addRow()->addClass('budgetInfo');
								$row->addLabel('budgetAllocation', __('Budget For Cycle'))->description(__('Numeric value of the fee.'));
                                $row->addCurrency('budgetAllocation')->required()->readonly()->setValue($budgetAllocationLabel);
                              
                            $budgetAllocatedLabel = (is_numeric($budgetAllocated))? number_format($budgetAllocated, 2, '.', ',') : $budgetAllocated;
							$row = $form->addRow()->addClass('budgetInfo');
								$row->addLabel('budgetForCycle', __('Amount already approved or spent'))->description(__('Numeric value of the fee.'));
                                $row->addCurrency('budgetForCycle')->required()->readonly()->setValue($budgetAllocatedLabel);
                                
                            $budgetRemainingLabel = (is_numeric($budgetRemaining))? number_format($budgetRemaining, 2, '.', ',') : $budgetRemaining;
							$row = $form->addRow()->addClass('budgetInfo');
								$row->addLabel('budgetRemaining', __('Budget Remaining For Cycle'))->description(__('Numeric value of the fee.'));
                                $row->addCurrency('budgetRemaining')
                                    ->required()
                                    ->readonly()
                                    ->setValue($budgetRemainingLabel)
                                    ->addClass( (is_numeric($budgetRemaining) && $budgetRemaining - $values['cost'] > 0)? 'textUnderBudget' : 'textOverBudget' );

                            $form->addRow()->addHeading(__('Log'));
                            
                            $form->addRow()->addContent(getExpenseLog($guid, $pupilsightFinanceExpenseID, $connection2));

							$isPaid = $values['status'] == 'Paid';
							if (!$isPaid) {
								$form->toggleVisibilityByClass('paymentInfo')->onSelect('status')->when('Paid');
							}

							$form->addRow()->addHeading(__('Payment Information'))->addClass('paymentInfo');

							$row = $form->addRow()->addClass('paymentInfo');
								$row->addLabel('paymentDate', __('Date Paid'))->description(__('Date of payment, not entry to system.'));
								$row->addDate('paymentDate')->required()->setValue(dateConvertBack($guid, $values['paymentDate']))->readonly($isPaid);

							$row = $form->addRow()->addClass('paymentInfo');
								$row->addLabel('paymentAmount', __('Amount Paid'))->description(__('Final amount paid.'));
								$row->addCurrency('paymentAmount')->required()->maxLength(15)->readonly($isPaid);

							$row = $form->addRow()->addClass('paymentInfo');
                                $row->addLabel('pupilsightPersonIDPayment', __('Payee'))->description(__('Staff who made, or arranged, the payment.'));
                                if ($isPaid) {
                                    $data = array('pupilsightPersonID' => $values['pupilsightPersonIDPayment']);
                                    $sql = "SELECT title, surname, preferredName FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID) WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID";
                                    $result = $pdo->executeQuery($data, $sql);
                                    $payee = $result->rowCount() == 1? $result->fetch() : null;
                                    $payeeName = !empty($payee)? formatName($payee['title'], $payee['preferredName'], $payee['surname'], 'Staff', true, true) : '';
                                    $row->addTextField('payee')->required()->readonly()->setValue($payeeName);
                                    $form->addHiddenValue('pupilsightPersonIDPayment', $values['pupilsightPersonIDPayment']);
                                } else {
                                    $row->addSelectStaff('pupilsightPersonIDPayment')->required()->placeholder();
                                }
								

							$methods = array(
								'Bank Transfer' => __('Bank Transfer'),
								'Cash' => __('Cash'),
								'Cheque' => __('Cheque'),
								'Credit Card' => __('Credit Card'),
								'Other' => __('Other')
							);
							$row = $form->addRow()->addClass('paymentInfo');
                                $row->addLabel('paymentMethod', __('Payment Method'));
                                if ($isPaid) {
                                    $row->addTextField('paymentMethod')->required()->readonly()->setValue($values['paymentMethod']);
                                } else {
                                    $row->addSelect('paymentMethod')->fromArray($methods)->placeholder()->required()->readonly($isPaid);
                                }

							$row = $form->addRow()->addClass('paymentInfo');
								$row->addLabel('paymentID', __('Payment ID'))->description(__('Transaction ID to identify this payment.'));
                                $paymentID = $row->addTextField('paymentID')->maxLength(100)->readonly($isPaid && $values['paymentReimbursementStatus'] != 'Requested');

                            if ($values['paymentReimbursementReceipt'] != '') {
                                $paymentID->prepend("<a target='_blank' class='floatRight' href=\"./".$values['paymentReimbursementReceipt'].'">'.__('Payment Receipt').'</a><br/>');
                            }
                                
                            if ($values['status'] == 'Paid' and $values['purchaseBy'] == 'Self' and $values['paymentReimbursementStatus'] != '') {

                                $row = $form->addRow()->addClass('paymentInfo');
                                $row->addLabel('paymentReimbursementStatus', __('Reimbursement Status'));

                                if ($values['paymentReimbursementStatus'] == 'Complete') {
                                    $row->addTextField('paymentReimbursementStatus')->readonly()->setValue($values['paymentReimbursementStatus']);
                                } else {
                                    $statuses = array('Requested' => __('Requested'),'Complete' => __('Complete'));
                                    $row->addSelect('paymentReimbursementStatus')->fromArray($statuses)->selected($values['paymentReimbursementStatus']);

                                    $col = $form->addRow()->addColumn();
                                        $col->addLabel('reimbursementComment', __('Reimbursement Comment'));
                                        $col->addTextArea('reimbursementComment')->setRows(4)->setClass('fullWidth');
                                }
                            }

							$row = $form->addRow();
								$row->addFooter();
								$row->addSubmit();

							$form->loadAllValuesFrom($values);

							echo $form->getOutput();
                        }
                    }
                }
            }
        }
    }
}
