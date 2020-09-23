<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

//Setup variables
$output = '';
$id = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

$category = isset($_POST['category'.$id])? $_POST['category'.$id] : (isset($_GET['category'])? $_GET['category'] : '');
$purpose = isset($_POST['purpose'.$id])? $_POST['purpose'.$id] : (isset($_GET['purpose'])? $_GET['purpose'] : '');
$pupilsightYearGroupID = isset($_POST['pupilsightYearGroupID'.$id])? $_POST['pupilsightYearGroupID'.$id] : (isset($_GET['pupilsightYearGroupID'])? $_GET['pupilsightYearGroupID'] : '');
$tags = isset($_POST['tags'.$id])? $_POST['tags'.$id] : (isset($_GET['tags'])? $_GET['tags'] : null);

if (isActionAccessible($guid, $connection2, '/modules/Planner/resources_view.php') == false) {
    //Acess denied
    $output .= "<div class='alert alert-danger'>";
    $output .= __('Your request failed because you do not have access to this action.');
    $output .= '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, '/modules/Planner/resources_manage.php', $connection2);

    $output .= "<script type='text/javascript'>";
    $output .= '$(document).ready(function() {';
    $output .= 'var optionsSearch={';
    $output .= 'target: $(".'.$id.'resourceSlider"),';
    $output .= "url: '".$_SESSION[$guid]['absoluteURL']."/modules/Planner/resources_insert_ajax.php?id=$id',";
    $output .= "type: 'POST'";
    $output .= '};';

    $output .= "$('#".$id."ajaxFormSearch').submit(function() {";
    $output .= '$(this).ajaxSubmit(optionsSearch);';
    $output .= 'return false;';
    $output .= '});';
    $output .= '});';

    $output .= 'var formResetSearch=function() {';
    $output .= "$('#".$id."resourceInsert').css('display','none');";
    $output .= '};';
    $output .= '</script>';

    $output .= "<table cellspacing='0' style='width: 100%'>";
    $output .= "<tr id='".$id."resourceInsert'>";
    $output .= "<td colspan=2 style='padding-top: 0px'>";
    $output .= "<div style='margin: 0px' class='linkTop'><a href='javascript:void(0)' onclick='formResetSearch(); \$(\".".$id."resourceSlider\").slideUp();'><img title='".__('Close')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/></a></div>";
    $output .= "<h3 style='margin-top: 0px; font-size: 140%'>Insert A Resource</h3>";
    $output .= '<p>'.sprintf(__('The table below shows shared resources drawn from the %1$sPlanner%2$s section of Pupilsight. You will see the 50 most recent resources that match the filters you have used.'), "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/resources_view.php'>", '</a>').'</p>';
    
    $form = Form::create($id.'ajaxFormSearch', '');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');
            
    $row = $form->addRow();
    
    $categories = getSettingByScope($connection2, 'Resources', 'categories');
    $col = $row->addColumn();
        $col->addLabel('category'.$id, __('Category'));
        $col->addSelect('category'.$id)->fromString($categories)->placeholder()->setClass('mediumWidth')->selected($category);

    $purposesGeneral = getSettingByScope($connection2, 'Resources', 'purposesGeneral');
    $purposesRestricted = ($highestAction == 'Manage Resources_all')? getSettingByScope($connection2, 'Resources', 'purposesRestricted') : '';
    $col = $row->addColumn();
        $col->addLabel('purpose'.$id, __('Purpose'));
        $col->addSelect('purpose'.$id)->fromString($purposesGeneral)->fromString($purposesRestricted)->placeholder()->setClass('mediumWidth')->selected($purpose);

    $col = $row->addColumn();
        $col->addLabel('pupilsightYearGroupID'.$id, __('Year Groups'));
        $col->addSelectYearGroup('pupilsightYearGroupID'.$id)->placeholder()->setClass('mediumWidth')->selected($pupilsightYearGroupID);

    $row = $form->addRow();

    $sql = "SELECT tag as value, CONCAT(tag, ' <i>(', count, ')</i>') as name FROM pupilsightResourceTag WHERE count>0 ORDER BY tag";
    $col = $row->addColumn()->addClass('inline');
        $col->addLabel('tags'.$id, __('Tags'));
        $col->addFinder('tags'.$id)
            ->fromQuery($pdo, $sql)
            ->setParameter('hintText', __('Type a tag...'))
            ->addClass('floatNone')
            ->selected($tags);
    
    $col->addSubmit(__('Go'));
    
    $output .= $form->getOutput();
    $output .= '<br/>';

	//Search with filters applied
	try {
		$data = array();
		$sqlWhere = 'WHERE ';
		if ($tags != '') {
            $tagArray = explode(',', $tags);
			foreach ($tagArray as $tagCount => $atag) {
				$data['tag'.$tagCount] = '%'.$atag.'%';
				$sqlWhere .= 'tags LIKE :tag'.$tagCount.' AND ';
			}
		}
		if ($category != '') {
			$data['category'] = $category;
			$sqlWhere .= 'category=:category AND ';
		}
		if ($purpose != '') {
			$data['purpose'] = $purpose;
			$sqlWhere .= 'purpose=:purpose AND ';
		}
		if ($pupilsightYearGroupID != '') {
			$data['pupilsightYearGroupID'] = "%$pupilsightYearGroupID%";
			$sqlWhere .= 'pupilsightYearGroupIDList LIKE :pupilsightYearGroupID AND ';
		}
		if ($sqlWhere == 'WHERE ') {
			$sqlWhere = '';
		} else {
			$sqlWhere = substr($sqlWhere, 0, -5);
		}

		$sql = "SELECT pupilsightResource.*, surname, preferredName, title FROM pupilsightResource JOIN pupilsightPerson ON (pupilsightResource.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) $sqlWhere ORDER BY timestamp DESC LIMIT 50";

		$result = $connection2->prepare($sql);
		$result->execute($data);
	} catch (PDOException $e) {
		echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
	}

    if ($result->rowCount() < 1) {
        $output .= "<div class='alert alert-danger'>";
        $output .= __('There are no records to display.');
        $output .= '</div>';
    } else {
        $output .= "<table cellspacing='0' style='width: 100%'>";
        $output .= "<tr class='head'>";
        $output .= '<th>';
        $output .= __('Name').'<br/>';
        $output .= "<span style='font-size: 85%; font-style: italic'>".__('Contributor').'</span>';
        $output .= '</th>';
        $output .= '<th>';
        $output .= __('Type');
        $output .= '</th>';
        $output .= '<th>';
        $output .= __('Category').'<br/>';
        $output .= "<span style='font-size: 85%; font-style: italic'>".__('Purpose').'</span>';
        $output .= '</th>';
        $output .= '<th>';
        $output .= __('Tags');
        $output .= '</th>';
        $output .= '<th>';
        $output .= __('Year Groups');
        $output .= '</th>';
        $output .= '<th>';
        $output .= __('Insert');
        $output .= '</th>';
        $output .= '</tr>';

        $count = 0;
        $rowNum = 'odd';
        while ($row = $result->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            ++$count;

			//COLOR ROW BY STATUS!
			$output .= "<tr class=$rowNum>";
            $output .= '<td>';
            if ($row['type'] == 'Link') {
                $output .= "<a target='_blank' style='font-weight: bold' href='".$row['content']."'>".$row['name'].'</a><br/>';
            } elseif ($row['type'] == 'File') {
                $output .= "<a target='_blank' style='font-weight: bold' href='".$_SESSION[$guid]['absoluteURL'].'/'.$row['content']."'>".$row['name'].'</a><br/>';
            } elseif ($row['type'] == 'HTML') {
                $output .= "<a target='_blank' style='font-weight: bold' href='".$_SESSION[$guid]['absoluteURL'].'/modules/Planner/resources_view_standalone.php?pupilsightResourceID='.$row['pupilsightResourceID']."'>".$row['name'].'</a><br/>';
            }
            $output .= "<span style='font-size: 85%; font-style: italic'>".formatName($row['title'], $row['preferredName'], $row['surname'], 'Staff').'</span>';
            $output .= '</td>';
            $output .= '<td>';
            $output .= $row['type'];
            $output .= '</td>';
            $output .= '<td>';
            $output .= '<b>'.$row['category'].'</b><br/>';
            $output .= "<span style='font-size: 85%; font-style: italic'>".$row['purpose'].'</span>';
            $output .= '</td>';
            $output .= '<td>';
            $tagoutput = '';
            $tags = explode(',', $row['tags']);
            natcasesort($tags);
            foreach ($tags as $tag) {
                $tagoutput .= trim($tag).'<br/>';
            }
            $output .= substr($tagoutput, 0, -2);
            $output .= '</td>';
            $output .= '<td>';
            try {
                $dataYears = array();
                $sqlYears = 'SELECT pupilsightYearGroupID, nameShort, sequenceNumber FROM pupilsightYearGroup ORDER BY sequenceNumber';
                $resultYears = $connection2->prepare($sqlYears);
                $resultYears->execute($dataYears);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            $years = explode(',', $row['pupilsightYearGroupIDList']);
            $sqlWhere = '';
            if (count($years) > 0 and $years[0] != '') {
                if (count($years) == $resultYears->rowCount()) {
                    $output .= '<i>All Years</i>';
                } else {
                    $count3 = 0;
                    $count4 = 0;
                    while ($rowYears = $resultYears->fetch()) {
                        for ($i = 0; $i < count($years); ++$i) {
                            if ($rowYears['pupilsightYearGroupID'] == $years[$i]) {
                                if ($count3 > 0 and $count4 > 0) {
                                    $output .= ', ';
                                }
                                $output .= $rowYears['nameShort'];
                                ++$count4;
                            }
                        }
                        ++$count3;
                    }
                }
            } else {
                $output .= '<i>'.__('None').'</i>';
            }
            $output .= '</td>';
            $output .= '<td>';
            $html = '';
            $extension = '';
            if ($row['type'] == 'Link') {
                $extension = strrchr($row['content'], '.');
                if (strcasecmp($extension, '.gif') == 0 or strcasecmp($extension, '.jpg') == 0 or strcasecmp($extension, '.jpeg') == 0 or strcasecmp($extension, '.png') == 0) {
                    $html = "<a target='_blank' style='font-weight: bold' href='".$row['content']."'><img class='resource' style='max-width: 500px' src='".$row['content']."'></a>";
                } else {
                    $html = "<a target='_blank' style='font-weight: bold' href='".$row['content']."'>".$row['name'].'</a>';
                }
            } elseif ($row['type'] == 'File') {
                $extension = strrchr($row['content'], '.');
                if (strcasecmp($extension, '.gif') == 0 or strcasecmp($extension, '.jpg') == 0 or strcasecmp($extension, '.jpeg') == 0 or strcasecmp($extension, '.png') == 0) {
                    $html = "<a target='_blank' style='font-weight: bold' href='".$_SESSION[$guid]['absoluteURL'].'/'.$row['content']."'><img class='resource' style='max-width: 500px' src='".$_SESSION[$guid]['absoluteURL'].'/'.$row['content']."'></a>";
                } else {
                    $html = "<a target='_blank' style='font-weight: bold' href='".$_SESSION[$guid]['absoluteURL'].'/'.$row['content']."'>".$row['name'].'</a>';
                }
            } elseif ($row['type'] == 'HTML') {
                $html = $row['content'];
            }
            $output .= "<a href='javascript:void(0)' onclick='tinymce.execCommand(\"mceFocus\",false,\"$id\"); tinyMCE.execCommand(\"mceInsertContent\", 0, \"".htmlPrep(addslashes($html)).'"); formResetSearch(); $(".'.$id."resourceSlider\").slideUp();'><img title='".__('Insert')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a>";
            $output .= '</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';
    }
    $output .= '</td>';
    $output .= '</tr>';
    $output .= '</table>';
}

echo $output;
