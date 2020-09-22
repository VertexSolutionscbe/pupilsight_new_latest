<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/resources_manage_add.php&search='.$_GET['search'];
$time = time();

if (isActionAccessible($guid, $connection2, '/modules/Planner/resources_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    if (empty($_POST)) {
        $URL .= '&return=warning1';
        header("Location: {$URL}");
        exit;
    } else {
        $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
        if ($highestAction == false) {
            $URL .= '&return=error0';
            header("Location: {$URL}");
            exit;
        } else {
            //Proceed!
            $type = $_POST['type'];
            if ($type == 'File') {
                $content = $_FILES['file'];
            } elseif ($type == 'HTML') {
                $content = $_POST['html'];
            } elseif ($type == 'Link') {
                $content = $_POST['link'];
            }
            $name = $_POST['name'];
            $category = $_POST['category'];
            $purpose = $_POST['purpose'];
            $tags = strtolower($_POST['tags']);
            $pupilsightYearGroupIDList = (!empty($_POST['pupilsightYearGroupID']))? implode(',', $_POST['pupilsightYearGroupID']) : '';
            $description = $_POST['description'];

            if (($type != 'File' and $type != 'HTML' and $type != 'Link') or is_null($content) or $name == '' or $category == '' or $tags == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
                exit;
            } else {
                if ($type == 'File') {
                    $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

                    $file = (isset($_FILES['file']))? $_FILES['file'] : null;

                    // Upload the file, return the /uploads relative path
                    $attachment = $fileUploader->uploadFromPost($file, $name);

                    if (empty($attachment)) {
                        $URL .= '&return=error1';
                        header("Location: {$URL}");
                        exit;
                    }

                    $content = $attachment;
                }

                //Deal with tags
                try {
                    $sql = 'LOCK TABLES pupilsightResourceTag WRITE';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Update tag counts
                $partialFail = false;
                $tags = explode(',', $_POST['tags']);
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
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Write to database
                try {
                    $data = array('type' => $type, 'content' => $content, 'name' => $name, 'category' => $category, 'purpose' => $purpose, 'tags' => substr($tagList, 0, -1), 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'description' => $description, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'timestamp' => date('Y-m-d H:i:s', $time));
                    $sql = 'INSERT INTO pupilsightResource SET type=:type, content=:content, name=:name, category=:category, purpose=:purpose, tags=:tags, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, description=:description, pupilsightPersonID=:pupilsightPersonID, timestamp=:timestamp';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 14, '0', STR_PAD_LEFT);

                if ($partialFail == true) {
                    $URL .= "&return=warning1&editID=$AI";
                    header("Location: {$URL}");
                } else {
                    $URL .= "&return=success0&editID=$AI";
                    header("Location: {$URL}");
                }
            }
        }
    }
}
