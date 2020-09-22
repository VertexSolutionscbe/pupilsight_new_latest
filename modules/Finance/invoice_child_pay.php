<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_child_pay.php') == true) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Invoice'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $FeesGateway = $container->get(FeesGateway::class);
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $childs = 'SELECT b.pupilsightPersonID, b.officialName FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID2 = b.pupilsightPersonID WHERE a.pupilsightPersonID1 = '.$cuid.' GROUP BY a.pupilsightPersonID1 LIMIT 0,1';
    $resulta = $connection2->query($childs);
    $students = $resulta->fetch();

    $stuId = $students['pupilsightPersonID'];

    // QUERY
 

}
