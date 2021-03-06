<?php
/*
Pupilsight, Flexible & Open School System
*/

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>
			Pupilsight Database Updater
		</title>
		<meta charset="utf-8"/>
		<meta name="author" content="Pupilsight, International College Hong Kong"/>
		<meta name="robots" content="none"/>

		<link rel="shortcut icon" type="image/x-icon" href="./favicon.ico"/>
		<link rel='stylesheet' type='text/css' href='./themes/Default/css/main.css' />
	</head>
	<body>
		<?php
        include './pupilsight.php';
        include './config.php';
        include './version.php';

        $partialFail = false;

        $cuttingEdgeCode = getSettingByScope($connection2, 'System', 'cuttingEdgeCode');
        if ($cuttingEdgeCode != 'Y') {
            $type = 'regularRelease';
        } else {
            $type = 'cuttingEdge';
        }

        if ($type != 'regularRelease' and $type != 'cuttingEdge') {
            echo "<div class='alert alert-danger'>";
            echo __('Your request failed because your inputs were invalid.');
            echo '</div>';
        } elseif ($type == 'regularRelease') { //Do regular release update
            $versionDB = getSettingByScope($connection2, 'System', 'version');
            $versionCode = $version;

            //Validate Inputs
            if ($versionDB == '' or $versionCode == '' or version_compare($versionDB, $versionCode) != -1) {
                echo "<div class='alert alert-danger'>";
                echo __('Your request failed because your inputs were invalid, or no update was required.');
                echo '</div>';
            } else {
                include './CHANGEDB.php';

                foreach ($sql as $version) {
                    if (version_compare($version[0], $versionDB, '>') and version_compare($version[0], $versionCode, '<=')) {
                        $sqlTokens = explode(';end', $version[1]);
                        foreach ($sqlTokens as $sqlToken) {
                            if (trim($sqlToken) != '') {
                                try {
                                    $result = $connection2->query($sqlToken);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                        }
                    }
                }

                if ($partialFail == true) {
                    echo "<div class='alert alert-danger'>";
                    echo __('Some aspects of your update failed.');
                    echo '</div>';
                } else {
                    //Update DB version
                    try {
                        $data = array('value' => $versionCode);
                        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='version'";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>";
                        echo __('Some aspects of your update failed.');
                        echo '</div>';
                        exit;
                    }

					// Update DB version for existing languages
	                i18nCheckAndUpdateVersion($container, $versionDB);

					// Clear the templates cache folder
	                removeDirectoryContents($_SESSION[$guid]['absolutePath'].'/uploads/cache');

					// Clear the var folder and remove it
	                removeDirectoryContents($_SESSION[$guid]['absolutePath'].'/var', true);

                    echo "<div class='alert alert-sucess'>";
                    echo __('Your request was completed successfully.');
                    echo '</div>';
                }
            }
        } elseif ($type == 'cuttingEdge') { //Do cutting edge update
            $versionDB = getSettingByScope($connection2, 'System', 'version');
            $versionCode = $version;
            $cuttingEdgeCodeLine = getSettingByScope($connection2, 'System', 'cuttingEdgeCodeLine');

            include './CHANGEDB.php';
            $versionMax = $sql[(count($sql))][0];
            $sqlTokens = explode(';end', $sql[(count($sql))][1]);
            $versionMaxLinesMax = (count($sqlTokens) - 1);
            $update = false;
            if (version_compare($versionMax, $versionDB, '>')) {
                $update = true;
            } else {
                if ($versionMaxLinesMax > $cuttingEdgeCodeLine) {
                    $update = true;
                }
            }

            if ($update == false) { //Something went wrong...abandon!
                echo "<div class='alert alert-danger'>";
                echo __('Some aspects of your update failed.');
                echo '</div>';
                exit;
            } else { //Let's do it
                if (version_compare($versionMax, $versionDB, '>')) { //At least one whole verison needs to be done
                    foreach ($sql as $version) {
                        $tokenCount = 0;
                        if (version_compare($version[0], $versionDB, '>=') and version_compare($version[0], $versionCode, '<=')) {
                            $sqlTokens = explode(';end', $version[1]);
                            if ($version[0] == $versionDB) { //Finish current version
                                foreach ($sqlTokens as $sqlToken) {
                                    if ($tokenCount >= $cuttingEdgeCodeLine) {
                                        if (trim($sqlToken) != '') { //Decide whether this has been run or not
                                            try {
                                                $result = $connection2->query($sqlToken);
                                            } catch (PDOException $e) {
                                                $partialFail = true;
                                            }
                                        }
                                    }
                                    ++$tokenCount;
                                }
                            } else { //Update intermediate versions and max version
                                foreach ($sqlTokens as $sqlToken) {
                                    if (trim($sqlToken) != '') { //Decide whether this has been run or not
                                        try {
                                            $result = $connection2->query($sqlToken);
                                        } catch (PDOException $e) {
                                            $partialFail = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else { //Less than one whole version
                    //Get up to speed in max version
                    foreach ($sql as $version) {
                        $tokenCount = 0;
                        if (version_compare($version[0], $versionDB, '>=') and version_compare($version[0], $versionCode, '<=')) {
                            $sqlTokens = explode(';end', $version[1]);
                            foreach ($sqlTokens as $sqlToken) {
                                if ($tokenCount >= $cuttingEdgeCodeLine) {
                                    if (trim($sqlToken) != '') { //Decide whether this has been run or not
                                        try {
                                            $result = $connection2->query($sqlToken);
                                        } catch (PDOException $e) {
                                            $partialFail = true;
                                        }
                                    }
                                }
                                ++$tokenCount;
                            }
                        }
                    }
                }

                if ($partialFail == true) {
                    echo "<div class='alert alert-danger'>";
                    echo __('Some aspects of your update failed.');
                    echo '</div>';
                } else {
                    //Update DB version
                    try {
                        $data = array('value' => $versionMax);
                        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='version'";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>";
                        echo __('Some aspects of your update failed.');
                        echo '</div>';
                        exit;
                    }

                    //Update DB line count
                    try {
                        $data = array('value' => $versionMaxLinesMax);
                        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System' AND name='cuttingEdgeCodeLine'";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>";
                        echo __('Some aspects of your update failed.');
                        echo '</div>';
                        exit;
                    }

					// Update DB version for existing languages
	                i18nCheckAndUpdateVersion($container, $versionDB);

					// Clear the templates cache folder
	                removeDirectoryContents($_SESSION[$guid]['absolutePath'].'/uploads/cache');

					// Clear the var folder and remove it
                    removeDirectoryContents($_SESSION[$guid]['absolutePath'].'/var', true);
                    
                    echo "<div class='alert alert-sucess'>";
                    echo __('Your request was completed successfully.');
                    echo '</div>';
                }
            }
        }
        ?>
	</body>
</html>
