<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/index.php';
$session = $container->get('session');

if (isActionAccessible($guid, $connection2, '/modules/Campaign/addCampaignAjaxForm.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // $servername = "localhost";
    // $username = "root";
    // $password = "";
    // $dbname = "wordpress";

    // $conn = new mysqli($servername, $username, $password, $dbname);
    // if ($conn->connect_error) {
    //     die("Connection failed: " . $conn->connect_error);
    // }
   
    $formshortcode = $_POST['formshortcode'];
    $formid = (int) filter_var($formshortcode, FILTER_SANITIZE_NUMBER_INT);
    $campaignid = $_SESSION['campaignid']; 
    
    $post_author = '1';
    $post_content = $formshortcode;
    $comment_status = 'closed';
    $ping_status = 'closed';
    $post_name = 'form-'.$campaignid;
    $post_type = 'page';
    
    if ($campaignid == '' or $formshortcode == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        
            //Check for other currents
            
                //Write to database
                try {
                    $data = array('post_author' => $post_author, 'post_content' => $post_content, 'comment_status' => $comment_status, 'ping_status' => $ping_status, 'post_name' => $post_name,  'post_type' => $post_type);
                    $sql = "INSERT INTO wp_posts SET post_author=:post_author, post_content=:post_content, comment_status=:comment_status, ping_status=:ping_status, post_name=:post_name, post_type=:post_type";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    // $mysqli = "INSERT INTO wp_posts (post_author, post_content, comment_status, ping_status, post_name, post_type) VALUES ('".$post_author."', '".$post_content."', '".$comment_status."', '".$ping_status."', '".$post_name."', '".$post_type."')";
                    // if ($conn->query($mysqli) === TRUE) {
                    //     $AI = $conn->insert_id;
                    // }
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                }

                //Last insert ID
                $AI = $connection2->lastInsertID();
                
                if(!empty($AI)){
                    $id = $AI;
                     $guid = $_SESSION[$guid]['absoluteURL'].'/wp/?page_id='.$id;
                    //$guid = 'http://localhost/wordpress/?page_id='.$id;
                    $data2 = array('guid' => $guid, 'id' => $id);
                            
                    $sql2 = "UPDATE wp_posts SET guid=:guid WHERE id=:id";
                    $result2 = $connection2->prepare($sql2);
                    $result2->execute($data2);

                    // $sql2 = "UPDATE wp_posts SET guid= ".$guid." WHERE id= ".$id." ";
                    // $conn->query($sql2);

                    $campaign_id = $campaignid;
                    $page_link = $guid;
                    $data1 = array('offline_form_id'=>$formid, 'offline_page_link' => $page_link, 'id' => $campaign_id);
                    $sql1 = "UPDATE campaign SET offline_form_id=:offline_form_id, offline_page_link=:offline_page_link WHERE id=:id";
                    $result1 = $connection2->prepare($sql1);
                    $result1->execute($data1);
                }


                // $data5 = array('id' => $campaignid);
                // $sql5 = 'SELECT * FROM campaign WHERE id=:id';
                // $result5 = $connection2->prepare($sql5);
                // $result5->execute($data5);
                // $values5 = $result5->fetch();

                // $name = $values5['name'];
                // $ayear = $values5['academic_year'];
                // $id = $campaignid;

                // $URL = 'http://localhost/pupilsight/index.php?q=/modules/Campaign/wf_add.php&id='.$id.'&name='.$name.'&academic_year='.$ayear.'&search=';
                //header("Location: {$URL}");
                //echo $URL;
    
                // $URL .= "&return=success0&editID=$AI";
                //$session->forget(['campaignid']);
                // $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/index.php';
                // header("Location: {$URL}");
        
    }
}
