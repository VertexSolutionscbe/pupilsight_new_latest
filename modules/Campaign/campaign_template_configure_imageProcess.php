<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
ini_set('max_execution_time', 7200);


if (isActionAccessible($guid, $connection2, '/modules/Campaign/index.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // print_r($_FILES);
    // die();
    //Proceed!
    $campaign_id = $_POST['campaign_id'];
    $field_name = $_POST['field_name'];
    $template_type = $_POST['template_type'];
    $x = $_POST['x'];
    $y = $_POST['y'];
    $page_no = $_POST['page_no'];
    $width = $_POST['width'];
    $height = $_POST['height'];
    
    
    if ($campaign_id == '' && $field_name == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {

            $data = array('campaign_id' => $campaign_id, 'field_name' => $field_name, 'template_type' => $template_type);
            $sql = 'DELETE FROM campaign_configure_image_template WHERE campaign_id=:campaign_id AND field_name=:field_name AND template_type=:template_type';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $datains = array('campaign_id' => $campaign_id, 'field_name' => $field_name, 'template_type' => $template_type, 'x' => $x, 'y' => $y, 'page_no' => $page_no, 'width' => $width, 'height' => $height);
            $sqlins = "INSERT INTO campaign_configure_image_template SET campaign_id=:campaign_id, field_name=:field_name, template_type=:template_type, x=:x, y=:y, page_no=:page_no, width=:width, height=:height ";
            $resultins = $connection2->prepare($sqlins);
            $resultins->execute($datains);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        }

    }
}