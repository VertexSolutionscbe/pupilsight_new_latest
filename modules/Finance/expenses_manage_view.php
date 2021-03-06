<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenses_manage_view.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='error'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        $pupilsightFinanceBudgetCycleID = $_GET['pupilsightFinanceBudgetCycleID'];
    
        $urlParams = compact('pupilsightFinanceBudgetCycleID');        
        
        $page->breadcrumbs
            ->add(__('Manage Expenses'), 'expenses_manage.php',  $urlParams)
            ->add(__('View Expense'));        

        //Check if params are specified
        $pupilsightFinanceExpenseID = isset($_GET['pupilsightFinanceExpenseID'])? $_GET['pupilsightFinanceExpenseID'] : '';
        $status2 = isset($_GET['status2'])? $_GET['status2'] : '';
        $pupilsightFinanceBudgetID2 = isset($_GET['pupilsightFinanceBudgetID2'])? $_GET['pupilsightFinanceBudgetID2'] : '';
        if ($pupilsightFinanceExpenseID == '' or $pupilsightFinanceBudgetCycleID == '') {
            echo "<div class='error'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            //Check if have Full or Write in any budgets
            $budgets = getBudgetsByPerson($connection2, $_SESSION[$guid]['pupilsightPersonID']);
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
                            //GET THE DATA ACCORDING TO FILTERS
                            if ($highestAction == 'Manage Expenses_all') { //Access to everything
                                $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget, surname, preferredName, 'Full' AS access
									FROM pupilsightFinanceExpense
									JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
									JOIN pupilsightPerson ON (pupilsightFinanceExpense.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID)
									WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID
									ORDER BY FIND_IN_SET(pupilsightFinanceExpense.status, 'Pending,Issued,Paid,Refunded,Cancelled'), timestampCreator DESC";
                            } else { //Access only to own budgets
                                $data['pupilsightPersonID'] = $_SESSION[$guid]['pupilsightPersonID'];
                                $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget, surname, preferredName, access
									FROM pupilsightFinanceExpense
									JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
									JOIN pupilsightFinanceBudgetPerson ON (pupilsightFinanceBudgetPerson.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
									JOIN pupilsightPerson ON (pupilsightFinanceExpense.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID)
									WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID AND pupilsightFinanceBudgetPerson.pupilsightPersonID=:pupilsightPersonID
									ORDER BY FIND_IN_SET(pupilsightFinanceExpense.status, 'Pending,Issued,Paid,Refunded,Cancelled'), timestampCreator DESC";
                            }
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
                            $row = $result->fetch();

                            if ($status2 != '' or $pupilsightFinanceBudgetID2 != '') {
                                echo "<div class='linkTop'>";
                                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/expenses_manage.php&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=$status2&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2'>".__('Back to Search Results').'</a>';
                                echo '</div>';
                            }
                            ?>
								<table class='smallIntBorder fullWidth' cellspacing='0'>
									<tr class='break'>
										<td colspan=2>
											<h3><?php echo __('Basic Information') ?></h3>
										</td>
									</tr>
									<tr>
										<td style='width: 275px'>
											<b><?php echo __('Budget Cycle') ?></b><br/>
										</td>
										<td class="right">
											<?php
                                            $yearName = '';
											try {
												$dataYear = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID);
												$sqlYear = 'SELECT * FROM pupilsightFinanceBudgetCycle WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID';
												$resultYear = $connection2->prepare($sqlYear);
												$resultYear->execute($dataYear);
											} catch (PDOException $e) {
												echo "<div class='error'>".$e->getMessage().'</div>';
											}
											if ($resultYear->rowCount() == 1) {
												$rowYear = $resultYear->fetch();
												$yearName = $rowYear['name'];
											}
											?>
											<input readonly name="name" id="name" maxlength=20 value="<?php echo $yearName ?>" type="text" class="standardWidth">
											<input name="pupilsightFinanceBudgetCycleID" id="pupilsightFinanceBudgetCycleID" maxlength=20 value="<?php echo $pupilsightFinanceBudgetCycleID ?>" type="hidden" class="standardWidth">
											<script type="text/javascript">
												var pupilsightFinanceBudgetCycleID=new LiveValidation('pupilsightFinanceBudgetCycleID');
												pupilsightFinanceBudgetCycleID.add(Validate.Presence);
											</script>
										</td>
									</tr>
									<tr>
										<td style='width: 275px'>
											<b><?php echo __('Budget') ?></b><br/>
										</td>
										<td class="right">
											<input readonly name="name" id="name" maxlength=20 value="<?php echo $row['budget']; ?>" type="text" class="standardWidth">
										</td>
									</tr>
									<tr>
										<td>
											<b><?php echo __('Title') ?></b><br/>
										</td>
										<td class="right">
											<input readonly name="name" id="name" maxlength=60 value="<?php echo $row['title']; ?>" type="text" class="standardWidth">
										</td>
									</tr>
									<tr>
										<td>
											<b><?php echo __('Status') ?></b><br/>
										</td>
										<td class="right">
											<input readonly name="name" id="name" maxlength=60 value="<?php echo $row['status']; ?>" type="text" class="standardWidth">
										</td>
									</tr>
									<tr>
										<td colspan=2>
											<b><?php echo __('Description') ?></b>
											<?php
                                                echo '<p>';
												echo $row['body'];
												echo '</p>'
                                            ?>
										</td>
									</tr>
									<tr>
										<td>
											<b><?php echo __('Total Cost') ?></b><br/>
											<span style="font-size: 90%">
												<i>
												<?php
                                                if ($_SESSION[$guid]['currency'] != '') {
                                                    echo sprintf(__('Numeric value of the fee in %1$s.'), $_SESSION[$guid]['currency']);
                                                } else {
                                                    echo __('Numeric value of the fee.');
                                                }
                            					?>
												</i>
											</span>
										</td>
										<td class="right">
											<input readonly name="name" id="name" maxlength=60 value="<?php echo $row['cost']; ?>" type="text" class="standardWidth">
										</td>
									</tr>
									<tr>
										<td>
											<b><?php echo __('Count Against Budget') ?> *</b><br/>
										</td>
										<td class="right">
											<input readonly name="countAgainstBudget" id="countAgainstBudget" maxlength=60 value="<?php echo ynExpander($guid, $row['countAgainstBudget']); ?>" type="text" class="standardWidth">
										</td>
									</tr>
									<tr>
										<td>
											<b><?php echo __('Purchase By') ?></b><br/>
										</td>
										<td class="right">
											<input readonly name="purchaseBy" id="purchaseBy" maxlength=60 value="<?php echo $row['purchaseBy']; ?>" type="text" class="standardWidth">
										</td>
									</tr>
									<tr>
										<td colspan=2>
											<b><?php echo __('Purchase Details') ?></b>
											<?php
                                                echo '<p>';
												echo $row['purchaseDetails'];
												echo '</p>'
                                            ?>
										</td>
									</tr>

									<tr class='break'>
										<td colspan=2>
											<h3><?php echo __('Log') ?></h3>
										</td>
									</tr>
									<tr>
										<td colspan=2>
											<?php
                                            echo getExpenseLog($guid, $pupilsightFinanceExpenseID, $connection2);
                            				?>
										</td>
									</tr>

									<?php
                                    if ($row['status'] == 'Paid') {
                                        ?>
										<tr class='break' id="paidTitle">
											<td colspan=2>
												<h3><?php echo __('Payment Information') ?></h3>
											</td>
										</tr>
										<tr id="paymentDateRow">
											<td>
												<b><?php echo __('Date Paid') ?></b><br/>
												<span class="emphasis small"><?php echo __('Date of payment, not entry to system.') ?></span>
											</td>
											<td class="right">
												<input readonly name="paymentDate" id="paymentDate" maxlength=10 value="<?php echo dateConvertBack($guid, $row['paymentDate']) ?>" type="text" class="standardWidth">
											</td>
										</tr>
										<tr id="paymentAmountRow">
											<td>
												<b><?php echo __('Amount Paid') ?></b><br/>
												<span class="emphasis small"><?php echo __('Final amount paid.') ?>
												<?php
                                                if ($_SESSION[$guid]['currency'] != '') {
                                                    echo "<span style='font-style: italic; font-size: 85%'>".$_SESSION[$guid]['currency'].'</span>';
                                                }
                                        		?>
												</span>
											</td>
											<td class="right">
												<input readonly name="paymentAmount" id="paymentAmount" maxlength=10 value="<?php echo number_format($row['paymentAmount'], 2, '.', ',') ?>" type="text" class="standardWidth">
											</td>
										</tr>
										<tr id="payeeRow">
											<td>
												<b><?php echo __('Payee') ?></b><br/>
												<span class="emphasis small"><?php echo __('Staff who made, or arranged, the payment.') ?></span>
											</td>
											<td class="right">
												<?php
                                                try {
                                                    $dataSelect = array('pupilsightPersonID' => $row['pupilsightPersonIDPayment']);
                                                    $sqlSelect = 'SELECT * FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID) WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName';
                                                    $resultSelect = $connection2->prepare($sqlSelect);
                                                    $resultSelect->execute($dataSelect);
                                                } catch (PDOException $e) {
                                                }
												if ($resultSelect->rowCount() == 1) {
													$rowSelect = $resultSelect->fetch();
													?>
															<input readonly name="payee" id="payee" maxlength=10 value="<?php echo formatName(htmlPrep($rowSelect['title']), ($rowSelect['preferredName']), htmlPrep($rowSelect['surname']), 'Staff', true, true) ?>" type="text" class="standardWidth">
															<?php

												}
												?>
											</td>
										</tr>
										<tr id="paymentMethodRow">
											<td>
												<b><?php echo __('Payment Method') ?></b><br/>
											</td>
											<td class="right">
												<input readonly name="paymentMethod" id="paymentMethod" maxlength=10 value="<?php echo $row['paymentMethod'] ?>" type="text" class="standardWidth">
											</td>
										</tr>
										<tr id="paymentIDRow">
											<td>
												<b><?php echo __('Payment ID') ?></b><br/>
												<span class="emphasis small"><?php echo __('Transaction ID to identify this payment.') ?></span>
											</td>
											<td class="right">
												<input readonly name="paymentID" id="paymentID" maxlength=100 value="<?php echo $row['paymentID'] ?>" type="text" class="standardWidth">
											</td>
										</tr>
										<?php

                                    }
                            		?>
								</table>
							<?php

                        }
                    }
                }
            }
        }
    }
}
?>
