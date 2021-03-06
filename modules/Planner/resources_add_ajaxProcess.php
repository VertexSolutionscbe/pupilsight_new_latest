<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$time = time();

if (isActionAccessible($guid, $connection2, '/modules/Planner/resources_manage_add.php') == false) {
    echo "<span style='font-weight: bold; color: #ff0000'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</span>';
    exit();
} else {
    if (empty($_POST)) {
        echo "<span style='font-weight: bold; color: #ff0000'>";
        echo 'Your request failed due to an attachment error.';
        echo '</span>';
        exit();
    } else {
        //Proceed!
        $id = $_POST['id'];
        $type = $_POST[$id.'type'];
        if ($type == 'File') {
            $content = $_FILES[$id.'file'];
        } elseif ($type == 'Link') {
            $content = $_POST[$id.'link'];
        }
        $name = $_POST[$id.'name'];
        $category = $_POST[$id.'category'];
        $purpose = $_POST[$id.'purpose'];
        $tags = strtolower($_POST[$id.'tags']);
        $pupilsightYearGroupIDList = (!empty($_POST[$id.'pupilsightYearGroupID']))? implode(',', $_POST[$id.'pupilsightYearGroupID']) : '';
        $description = $_POST[$id.'description'];

        if (($type != 'File' and $type != 'Link') or is_null($content) or $name == '' or $category == '' /*or $tags == ''*/ or $id == '') {
            echo "<span style='font-weight: bold; color: #ff0000'>";
            echo __('Your request failed because your inputs were invalid.');
            echo '</span>';
            exit();
        } else {
            if ($type == 'File') {
                $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

                if (!empty($_FILES[$id.'file']['tmp_name'])) {
                    $file = (isset($_FILES[$id.'file']))? $_FILES[$id.'file'] : null;

                    // Upload the file, return the /uploads relative path
                    $attachment = $fileUploader->uploadFromPost($file, $name);

                    if (empty($attachment)) {
                        echo "<span style='font-weight: bold; color: #ff0000'>";
                            echo __('Your request failed due to an attachment error.');
                            echo ' '.$fileUploader->getLastError();
                        echo '</span>';
                        exit();
                    } else {
                        $content = $attachment;
                    }
                }
            }

            //Deal with tags
            try {
                $sql = 'LOCK TABLES pupilsightResourceTag WRITE';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                echo "<span style='font-weight: bold; color: #ff0000'>";
                echo __('Your request failed due to a database error.');
                echo '</span>';
                exit();
            }

            //Update tag counts
            $partialFail = false;
            $tags = explode(',', $_POST[$id.'tags']);
            $tagList = '';
            foreach ($tags as $tag) {
                if (trim($tag) != '') {
                    $tagList .= trim($tag).",";
                    try {
                        $dataTags = array('tag' => trim($tag));
                        $sqlTags = 'SELECT * FROM pupilsightResourceTag WHERE tag=:tag';
                        $resultTags = $connection2->prepare($sqlTags);
                        $resultTags->execute($dataTags);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                    if ($resultTags->rowCount() == 1) {
                        $rowTags = $resultTags->fetch();
                        try {
                            $dataTag = array('count' => ($rowTags['count'] + 1), 'tag' => trim($tag));
                            $sqlTag = 'UPDATE pupilsightResourceTag SET count=:count WHERE tag=:tag';
                            $resultTag = $connection2->prepare($sqlTag);
                            $resultTag->execute($dataTag);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    } elseif ($resultTags->rowCount() == 0) {
                        try {
                            $dataTag = array('tag' => trim($tag));
                            $sqlTag = 'INSERT INTO pupilsightResourceTag SET tag=:tag, count=1';
                            $resultTag = $connection2->prepare($sqlTag);
                            $resultTag->execute($dataTag);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    } else {
                        $partialFail = true;
                    }
                }
            }
            //Unlock table
            try {
                $sql = 'UNLOCK TABLES';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                echo "<span style='font-weight: bold; color: #ff0000'>";
                echo __('Your request failed due to a database error.');
                echo '</span>';
                exit();
            }

            //Write to database
            try {
                $data = array('type' => $type, 'content' => $content, 'name' => $name, 'category' => $category, 'purpose' => $purpose, 'tags' => substr($tagList, 0, -1), 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'description' => $description, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'timestamp' => date('Y-m-d H:i:s', $time));
                $sql = 'INSERT INTO pupilsightResource SET type=:type, content=:content, name=:name, category=:category, purpose=:purpose, tags=:tags, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, description=:description, pupilsightPersonID=:pupilsightPersonID, timestamp=:timestamp';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<span style='font-weight: bold; color: #ff0000'>";
                echo $e->getMessage();
                echo '</span>';
                exit();
            }

            if ($partialFail == true) {
                echo "<span style='font-weight: bold; color: #ff0000'>";
                echo __('Your request was successful, but some data was not properly saved.');
                echo '</span>';
            } else {
                $html = '';
                $extension = '';
                if ($type == 'Link') {
                    $extension = strrchr($content, '.');
                    if (strcasecmp($extension, '.gif') == 0 or strcasecmp($extension, '.jpg') == 0 or strcasecmp($extension, '.jpeg') == 0 or strcasecmp($extension, '.png') == 0) {
                        $html = "<a target='_blank' style='font-weight: bold' href='".$content."'><img class='resource' style='max-width: 500px' src='".$content."'></a>";
                    } else {
                        $html = "<a target='_blank' style='font-weight: bold' href='".$content."'>".$name.'</a>';
                    }
                } elseif ($type == 'File') {
                    $extension = strrchr($content, '.');
                    if (strcasecmp($extension, '.gif') == 0 or strcasecmp($extension, '.jpg') == 0 or strcasecmp($extension, '.jpeg') == 0 or strcasecmp($extension, '.png') == 0) {
                        $html = "<a target='_blank' style='font-weight: bold' href='".$_SESSION[$guid]['absoluteURL'].'/'.$content."'><img class='resource' style='max-width: 500px' src='".$_SESSION[$guid]['absoluteURL'].'/'.$content."'></a>";
                    } else {
                        $html = "<a target='_blank' style='font-weight: bold' href='".$_SESSION[$guid]['absoluteURL'].'/'.$content."'>".$name.'</a>';
                    }
                }
                echo $html;
            }
        }
    }
}
