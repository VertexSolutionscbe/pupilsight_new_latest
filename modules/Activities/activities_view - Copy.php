<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Activities\ActivityGateway;
use Pupilsight\Forms\Prefab\BulkActionForm;
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Set returnTo point for upcoming pages
    $page->breadcrumbs->add(__('View Activities'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $search = isset($_GET['search'])? $_GET['search'] : '';
    $pupilsightSchoolYearTermID = isset($_GET['pupilsightSchoolYearTermID'])? $_GET['pupilsightSchoolYearTermID'] : '';
    $dateType = getSettingByScope($connection2, 'Activities', 'dateType');
    $enrolmentType = getSettingByScope($connection2, 'Activities', 'enrolmentType');
    $schoolTerms = getTerms($connection2, $_SESSION[$guid]['pupilsightSchoolYearID']);
    $yearGroups = getYearGroups($connection2);

    $activityGateway = $container->get(ActivityGateway::class);
    
    // CRITERIA
    $criteria = $activityGateway->newQueryCriteria()
        ->searchBy($activityGateway->getSearchableColumns(), $search)
        ->filterBy('term', $pupilsightSchoolYearTermID)
        ->sortBy($dateType != 'Date' ? 'pupilsightSchoolYearTermIDList' : 'programStart', $dateType != 'Date' ? 'ASC' : 'DESC')
        ->sortBy('name');

    $criteria->fromPOST();

    echo '<h2>';
    echo __(' Filter & Search');
    echo '</h2>';

    $paymentOn = getSettingByScope($connection2, 'Activities', 'payment') != 'None' and getSettingByScope($connection2, 'Activities', 'payment') != 'Single';

    $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/activities_view.php");

    $row = $form->addRow();
        $row->addLabel('search', __('Search'))->description('Activity name.');
        $row->addTextField('search')->setValue($criteria->getSearchText());

    

    $row = $form->addRow()
			->addClass('right_align');
        $row->addSearchSubmit($pupilsight->session, __('Clear Search'));
		

    echo $form->getOutput();

    echo '<h2>';
    echo __('Activities');
    echo '</h2>';

    $activities = $activityGateway->queryActivitiesBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID']);

    // FORM
    $form = BulkActionForm::create('bulkAction', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/activities_manageProcessBulk.php');
    $form->addHiddenValue('search', $search);

    $bulkActions = array(
        'Duplicate' => __('Duplicate'),
        'DuplicateParticipants' => __('Duplicate With Participants'),
        'Delete' => __('Delete'),
    );
    $sql = "SELECT pupilsightSchoolYearID as value, pupilsightSchoolYear.name FROM pupilsightSchoolYear WHERE (status='Upcoming' OR status='Current') ORDER BY sequenceNumber LIMIT 0, 2";

    $col = $form->createBulkActionColumn($bulkActions);
        $col->addSelect('pupilsightSchoolYearIDCopyTo')
            ->fromQuery($pdo, $sql)
            ->setClass('shortWidth schoolYear');
        $col->addSubmit(__('Go'));

    $form->toggleVisibilityByClass('schoolYear')->onSelect('action')->when(array('Duplicate', 'DuplicateParticipants'));

    // DATA TABLE
	 $table = DataTable::createPaginated('inView', $criteria);
    $table = $form->addRow()->addDataTable('activities', $criteria)->withData($activities);

   

    $table->modifyRows(function ($activity, $row) {
        if ($activity['active'] == 'N') $row->addClass('error');
        return $row;
    });
	

    $table->addMetaData('filterOptions', [
        'active:Y'          => __('Active').': '.__('Yes'),
        'active:N'          => __('Active').': '.__('No'),
        'registration:Y'    => __('Registration').': '.__('Yes'),
        'registration:N'    => __('Registration').': '.__('No'),
        'enrolment:less'    => __('Enrolment').': &lt; '.__('Full'),
        'enrolment:full'    => __('Enrolment').': '.__('Full'),
        'enrolment:greater' => __('Enrolment').': &gt; '.__('Full'),
    ]);

    if ($enrolmentType == 'Competitive') {
        $table->addMetaData('filterOptions', ['status:waiting' => __('Waiting List')]);
    } else {
        $table->addMetaData('filterOptions', ['status:pending' => __('Pending')]);
    }

    $table->addMetaData('bulkActions', $col);

    // COLUMNS
    $table->addColumn('name', __('Activity'))
        ->format(function($activity) {
            return $activity['name'].'<br/><span class="small emphasis">'.$activity['type'].'</span>';
        });
	
	 $table->addColumn('provider', __('Provider'))
        ->format(function($activity) use ($guid){
            return ($activity['provider'] == 'School')? $_SESSION[$guid]['organisationNameShort'] : __('External');
        });

	
    $table->addColumn('days', __('Term Days'))
        ->notSortable()
        ->format(function($activity) use ($activityGateway) {
            return implode(', ', array_map('__', $activityGateway->selectWeekdayNamesByActivity($activity['pupilsightActivityID'])->fetchAll(\PDO::FETCH_COLUMN)));
        });

    $table->addColumn('yearGroups', __('Years'))
        ->format(function($activity) use ($yearGroups) {
            return ($activity['yearGroupCount'] >= count($yearGroups)/2)? '<i>'.__('All').'</i>' : $activity['yearGroups'];
        });

   
    if ($paymentOn) {
        $table->addColumn('payment', __('Cost'))
            ->description($_SESSION[$guid]['currency'])
            ->format(function($activity) {
                $payment = ($activity['payment'] > 0) 
                    ? Format::currency($activity['payment']) . '<br/>' . __($activity['paymentType'])
                    : '<i>'.__('None').'</i>';
                if ($activity['paymentFirmness'] != 'Finalised') $payment .= '<br/><i>'.__($activity['paymentFirmness']).'</i>';

                return $payment;
            });
    }

    $actions = $table->addActionColumn()
            ->addParam('pupilsightActivityID')
            ->format(function ($activity, $actions) {
                $actions->addAction('view', __('View Details'))
                    ->setURL('/modules/Activities/activities_view_full.php')
					
					//->modalWindow(600, 550)
					;
            });
    

    // ACTIONS
			/* $table->addActionColumn()
        ->addParam('pupilsightActivityID')
        ->addParam('search', $criteria->getSearchText(true))
        ->format(function ($activity, $actions) use ($guid) {
            $actions->addAction('view', __('view'))
                    ->setURL('/')
					->modalWindow(1100, 550);

           
            }); */
		/* $actions->addAction('view', __('View'))
                        ->setURL('/modules/Rubrics/rubrics_view_full.php')
                        ->modalWindow(1100, 550); */
						
						
                    
    
   // $table->addCheckboxColumn('pupilsightActivityID');

    echo $form->getOutput();
}
?>

