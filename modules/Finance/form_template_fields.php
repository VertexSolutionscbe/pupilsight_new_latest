<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_receipts_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $id = $_GET['id'];
    $page->breadcrumbs
        ->add(__('Manage Fees Template'), 'fee_receipts_manage.php')
        ->add(__('Fees Template Fields'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

}
?>

<table class="table">
    <thead>
        <tr>
            <th>Sl No</th>
            <th>Field Name</th>
            <th>Template Field type</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Student Id</td>
            <td>${student_id}</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Admission No</td>
            <td>${admission_no}</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Roll No</td>
            <td>${roll_no}</td>
        </tr>
        <tr>
            <td>4</td>
            <td>Student Name</td>
            <td>${student_name}</td>
        </tr>
        
        <tr>
            <td>5</td>
            <td>Father Name</td>
            <td>${father_name}</td>
        </tr>
        <tr>
            <td>6</td>
            <td>Mother Name</td>
            <td>${mother_name}</td>
        </tr>
        <tr>
            <td>7</td>
            <td>Program Name</td>
            <td>${program_name}</td>
        </tr>
        <tr>
            <td>8</td>
            <td>Class & Section</td>
            <td>${class_section}</td>
        </tr>
        <tr>
            <td>9</td>
            <td>Invoice No</td>
            <td>${invoice_no}</td>
        </tr>
        <tr>
            <td>10</td>
            <td>Receipt No</td>
            <td>${receipt_no}</td>
        </tr>
        <tr>
            <td>11</td>
            <td>Date</td>
            <td>${date}</td>
        </tr>
        <tr>
            <td>12</td>
            <td>Instrument Date</td>
            <td>${instrument_date}</td>
        </tr>
        <tr>
            <td>13</td>
            <td>Instrument No</td>
            <td>${instrument_no}</td>
        </tr>
        <tr>
            <td>14</td>
            <td>Total Amount</td>
            <td>${total}</td>
        </tr>
        <tr>
            <td>15</td>
            <td>Fine</td>
            <td>${fine_amount}</td>
        </tr>
        <tr>
            <td>16</td>
            <td>Payment Mode</td>
            <td>${pay_mode}</td>
        </tr>
        <tr>
            <td>17</td>
            <td>Transaction Id</td>
            <td>${transactionId}</td>
        </tr>
        <tr>
            <td>18</td>
            <td>Bank Name</td>
            <td>${bank_name}</td>
        </tr>
        <tr>
            <td>19</td>
            <td>Fee Head Acount No</td>
            <td>${fee_head_acc_no}</td>
        </tr>
        <tr>
            <td>20</td>
            <td>Total Paid in Words</td>
            <td>${total_paid_in_words}</td>
        </tr>
        <tr>
            <td>21</td>
            <td>Fee Item Sl No</td>
            <td>${serial.all}</td>
        </tr>
        <tr>
            <td>22</td>
            <td>Fee Item Name</td>
            <td>${particulars.all}</td>
        </tr>
        <tr>
            <td>23</td>
            <td>Fee Item Amount Without Tax and Discount</td>
            <td>${inv_amt.all}</td>
        </tr>
        <tr>
            <td>24</td>
            <td>Fee Item Tax</td>
            <td>${tax.all}</td>
        </tr>
        <tr>
            <td>25</td>
            <td>Fee Item Amount With Tax and Discount</td>
            <td>${amount.all}</td>
        </tr>
        <tr>
            <td>26</td>
            <td>Total Fee Item Amount Without Tax and Discount</td>
            <td>${inv_total}</td>
        </tr>
        <tr>
            <td>27</td>
            <td>Total Tax</td>
            <td>${total_tax}</td>
        </tr>

    </tbody>

</table>