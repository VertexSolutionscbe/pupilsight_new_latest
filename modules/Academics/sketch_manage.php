<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\SketchGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Sketch'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $SketchGateway = $container->get(SketchGateway::class);

    // QUERY
    $criteria = $SketchGateway->newQueryCriteria()
        ->pageSize(1000)
        ->sortBy(['id'])
        ->fromPOST();

    $sketch = $SketchGateway->getAllSketch($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('schoolYearManage', $criteria);

    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Academics/sketch_manage_add.php' class='thickbox btn btn-primary'>Add</a><div class='float-none'></div></div></div>";  
    
    
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('sketch_name', __('Name'));
    $table->addColumn('sketch_code', __('Code'));
    $table->addColumn('acedemic_year', __('Academic Year'));
    $table->addColumn('progname', __('Program'));
    $table->addColumn('class_name', __('Class'));
    $table->addColumn('template_filename', __('File'))
    ->format(function ($dataSet) {
        if($dataSet['template_filename'] != '') {
            return '<a title="Template File" href="public/sketch_template/'.$dataSet['template_filename'].'" download><i class="fas fa-download"></i></a>';
        } 
        return $dataSet['template_filename'];
    });  
    // $table->addColumn('test_name', __('Test Names'));
    
    // $table->addColumn('status', __('Status'))
    // ->format(function ($dataSet) {
    //     if ($dataSet['status'] == '1') {
    //         return 'Draft';
    //     } else if ($dataSet['status'] == '2' ) {
    //         return 'Published';
    //     } else {
    //        return 'Stoped';
    //     }
    //     return $dataSet['status'];
    // });  
   
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($skills, $actions) {
            $actions->addAction('editnew', __('Edit'))
                    ->setURL('/modules/Academics/sketch_manage_edit.php');
                    
                    

            // if ($schoolYear['status'] != 'Current') {
                $actions->addAction('delete', __('Delete'))
                       ->setURL('/modules/Academics/sketch_manage_delete.php');
            // }

            $actions->addAction('Sketch Configure', __('Sketch Configure'))
                    ->setURL('/modules/Academics/sketch_manage_attribute.php')
                    ->setIcon('cog'); 

            $actions->addAction('uploadtemplatenew', __('Upload Template'))
                    ->setURL('/modules/Academics/sketch_report_template_manage.php')
                    ->setIcon('cog');        

            $actions->addAction('Generate Sketch', __('Generate Sketch'))
                ->setURL('/modules/Academics/sketch_generate_result.php')
                ->setIcon('cog');         
        });

    echo $table->render($sketch);
}
