<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\FacilityGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/space_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Facilities'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $search = isset($_GET['search'])? $_GET['search'] : '';

    $facilityGateway = $container->get(FacilityGateway::class);

    // QUERY
    $criteria = $facilityGateway->newQueryCriteria()
        ->searchBy($facilityGateway->getSearchableColumns(), $search)
        ->sortBy(['name'])
        ->fromPOST();

    echo '<h3>';
    echo __('Search');
    echo '</h3>';

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/space_manage.php');

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'));
        $row->addTextField('search')->setValue($criteria->getSearchText());

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

    echo $form->getOutput();

    echo '<h3>';
    echo __('View');
    echo '</h3>';

    $facilities = $facilityGateway->queryFacilities($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('facilityManage', $criteria);


    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module']."/spaceSettings.php' class='thickbox btn btn-primary'>Settings</a>";  
    
    echo "&nbsp;&nbsp;<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/space_manage_add.php' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>"; 

    

    $table->addColumn('name', __('Name'));
    $table->addColumn('type', __('Type'));
    $table->addColumn('capacity', __('Capacity'));
    $table->addColumn('facilities', __('Facilities'))
        ->notSortable()
        ->format(function($values) { 
            $return = null;
            $return .= ($values['computer'] == 'Y') ? __('Teaching computer').'<br/>':'';
            $return .= ($values['computerStudent'] > 0) ? $values['computerStudent'].' '.__('student computers').'<br/>':'';
            $return .= ($values['projector'] == 'Y') ? __('Projector').'<br/>':'';
            $return .= ($values['tv'] == 'Y') ? __('TV').'<br/>':'';
            $return .= ($values['dvd'] == 'Y') ? __('DVD Player').'<br/>':'';
            $return .= ($values['hifi'] == 'Y') ? __('Hifi').'<br/>':'';
            $return .= ($values['speakers'] == 'Y') ? __('Speakers').'<br/>':'';
            $return .= ($values['iwb'] == 'Y') ? __('Interactive White Board').'<br/>':'';
            $return .= ($values['phoneInternal'] != '') ? __('Extension Number').': '.$values['phoneInternal'].'<br/>':'';
            $return .= ($values['phoneExternal'] != '') ? __('Phone Number').': '.$values['phoneExternal'].'<br/>':'';
            return $return;
        });

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightSpaceID')
        ->addParam('search', $criteria->getSearchText(true))
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/space_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/School Admin/space_manage_delete.php');
        });

    echo $table->render($facilities);
}
