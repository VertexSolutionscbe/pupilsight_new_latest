<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenses_manage_print.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, '/modules/Finance/expenses_manage_print.php', $connection2);
    if ($highestAction == false) {
        echo "<div class='error'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Check if params are specified
        $pupilsightFinanceExpenseID = isset($_GET['pupilsightFinanceExpenseID'])? $_GET['pupilsightFinanceExpenseID'] : '';
        $pupilsightFinanceBudgetCycleID = isset($_GET['pupilsightFinanceBudgetCycleID'])? $_GET['pupilsightFinanceBudgetCycleID'] : '';
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

                            echo "<div class='linkTop'>";
                            echo "<a href='javascript:window.print()'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
                            echo '</div>';
                            ?>
							<table class='smallIntBorder fullWidth' cellspacing='0'>
								<tr class='break'>
									<td colspan=2>
										<h3><?php echo __('Basic Information') ?></h3>
									</td>
								</tr>
								<tr>
									<td style='width: 275px'>
										<b><?php echo __('Budget Cycle') ?> *</b><br/>
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
										<b><?php echo __('Budget') ?> *</b><br/>
									</td>
									<td class="right">
										<input readonly name="name" id="name" maxlength=20 value="<?php echo $row['budget']; ?>" type="text" class="standardWidth">
									</td>
								</tr>
								<tr>
									<td>
										<b><?php echo __('Title') ?> *</b><br/>
									</td>
									<td class="right">
										<input readonly name="name" id="name" maxlength=60 value="<?php echo $row['title']; ?>" type="text" class="standardWidth">
									</td>
								</tr>
								<tr>
									<td>
										<b><?php echo __('Status') ?> *</b><br/>
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
										<b><?php echo __('Purchase By') ?> *</b><br/>
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


								<?php
                                if ($row['status'] == 'Requested' or $row['status'] == 'Approved' or $row['status'] == 'Ordered' or $row['status'] == 'Paid') {
                                    ?>
									<tr class='break'>
										<td colspan=2>
											<h3><?php echo __('Budget Tracking') ?></h3>
										</td>
									</tr>
									<tr>
										<td>
											<b><?php echo __('Total Cost') ?> *</b><br/>
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
											<input readonly name="name" id="name" maxlength=60 value="<?php echo number_format($row['cost'], 2, '.', ','); ?>" type="text" class="standardWidth">
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
									<?php
                                    if ($row['countAgainstBudget'] == 'Y') {
                                        ?>
										<tr>
											<td>
												<b><?php echo __('Budget For Cycle') ?> *</b><br/>
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
												<?php
                                                $budgetAllocation = null;
												$budgetAllocationFail = false;
												try {
													$dataCheck = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceBudgetID' => $row['pupilsightFinanceBudgetID']);
													$sqlCheck = 'SELECT * FROM pupilsightFinanceBudgetCycleAllocation WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
													$resultCheck = $connection2->prepare($sqlCheck);
													$resultCheck->execute($dataCheck);
												} catch (PDOException $e) {
													echo "<div class='error'>".$e->getMessage().'</div>';
													$budgetAllocationFail = true;
												}
												if ($resultCheck->rowCount() != 1) {
													echo '<input readonly name="name" id="name" maxlength=60 value="'.__('NA').'" type="text" style="width: 300px">';
													$budgetAllocationFail = true;
												} else {
													$rowCheck = $resultCheck->fetch();
													$budgetAllocation = $rowCheck['value'];
													?>
															<input readonly name="name" id="name" maxlength=60 value="<?php echo number_format($budgetAllocation, 2, '.', ',');
													?>" type="text" class="standardWidth">
															<?php

												}
												?>
											</td>
										</tr>
										<tr>
											<td>
												<b><?php echo __('Amount already approved or spent') ?> *</b><br/>
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
												<?php
                                                $budgetAllocated = 0;
												$budgetAllocatedFail = false;
												try {
													$dataCheck = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceBudgetID' => $row['pupilsightFinanceBudgetID']);
													$sqlCheck = "(SELECT cost FROM pupilsightFinanceExpense WHERE countAgainstBudget='Y' AND pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID AND FIELD(status, 'Approved', 'Order'))
															UNION
															(SELECT paymentAmount AS cost FROM pupilsightFinanceExpense WHERE countAgainstBudget='Y' AND pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID AND FIELD(status, 'Paid'))
															";
													$resultCheck = $connection2->prepare($sqlCheck);
													$resultCheck->execute($dataCheck);
												} catch (PDOException $e) {
													echo "<div class='error'>".$e->getMessage().'</div>';
													$budgetAllocatedFail = true;
												}
												if ($budgetAllocatedFail == false) {
													while ($rowCheck = $resultCheck->fetch()) {
														$budgetAllocated = $budgetAllocated + $rowCheck['cost'];
													}
													?>
															<input readonly name="name" id="name" maxlength=60 value="<?php echo number_format($budgetAllocated, 2, '.', ',');
													?>" type="text" class="standardWidth">
															<?php

												}

												?>
											</td>
										</tr>
										<?php
                                        if ($budgetAllocationFail == false and $budgetAllocatedFail == false) {
                                            ?>
											<tr>
											<td>
												<b><?php echo __('Budget Remaining For Cycle') ?> *</b><br/>
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
												<?php
                                                $color = 'red';
                                            if (($budgetAllocation - $budgetAllocated) - $row['cost'] > 0) {
                                                $color = 'green';
                                            }
                                            ?>
												<input readonly name="name" id="name" maxlength=60 value="<?php echo number_format(($budgetAllocation - $budgetAllocated), 2, '.', ',');
                                            ?>" type="text" style="width: 300px; font-weight: bold; color: <?php echo $color ?>">
											</td>
										</tr>
										<?php

                                        }
                                    }
                                }
                            ?>



							<tr class='break'>
								<td colspan=2>
									<h3><?php echo __('Log') ?></h3>
								</td>
							</tr>
							<tr>
								<td colspan=2>
									<?php
									echo getExpenseLog($guid, $pupilsightFinanceExpenseID, $connection2, true);
                            		?>
									</td>
								</tr>
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
