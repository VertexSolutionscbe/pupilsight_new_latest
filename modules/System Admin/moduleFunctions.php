<?php
/*
Pupilsight, Flexible & Open School System

*/

use Pupilsight\Domain\System\I18nGateway;
use Psr\Container\ContainerInterface;

//Sets the sequence numbers appropriately for a given first day of the week (either Sunday or Monday)
function setFirstDayOfTheWeek($connection2, $fdotw, $databaseName)
{
    $return = true;

    if ($fdotw != 'Monday' and $fdotw != 'Sunday') {
        $return = false;
    } else {
        //Remove index on sequenceNumber
        try {
            $dataIndex = array('databaseName' => $databaseName);
            $sqlIndex = "SELECT * FROM information_schema.statistics WHERE table_schema=:databaseName AND table_name='pupilsightDaysOfWeek' AND column_name='sequenceNumber'";
            $resultIndex = $connection2->prepare($sqlIndex);
            $resultIndex->execute($dataIndex);
            if ($resultIndex->rowCount() == 1) {
                $dataIndex = array();
                $sqlIndex = 'ALTER TABLE pupilsightDaysOfWeek DROP INDEX sequenceNumber';
                $resultIndex = $connection2->prepare($sqlIndex);
                $resultIndex->execute($dataIndex);
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit();
            $return = false;
        }

        $nameShort = '';
        for ($i = 1; $i <= 7; ++$i) {
            if ($fdotw == 'Monday') {
                switch ($i) {
                    case 1: { $nameShort = 'Mon'; break; }
                    case 2: { $nameShort = 'Tue'; break; }
                    case 3: { $nameShort = 'Wed'; break; }
                    case 4: { $nameShort = 'Thu'; break; }
                    case 5: { $nameShort = 'Fri'; break; }
                    case 6: { $nameShort = 'Sat'; break; }
                    case 7: { $nameShort = 'Sun'; break; }
                }
            } else {
                switch ($i) {
                    case 1: { $nameShort = 'Sun'; break; }
                    case 2: { $nameShort = 'Mon'; break; }
                    case 3: { $nameShort = 'Tue'; break; }
                    case 4: { $nameShort = 'Wed'; break; }
                    case 5: { $nameShort = 'Thu'; break; }
                    case 6: { $nameShort = 'Fri'; break; }
                    case 7: { $nameShort = 'Sat'; break; }
                }
            }

            try {
                $dataDOTW = array('sequenceNumber' => $i, 'nameShort' => $nameShort);
                $sqlDOTW = 'UPDATE pupilsightDaysOfWeek SET sequenceNumber=:sequenceNumber WHERE nameShort=:nameShort';
                $resultDOTW = $connection2->prepare($sqlDOTW);
                $resultDOTW->execute($dataDOTW);
            } catch (PDOException $e) {
                echo $e->getMessage();
                exit();
                $return = false;
            }
        }

        //Reinstate index on sequenceNumber
        try {
            $dataIndex = array();
            $sqlIndex = 'ALTER TABLE pupilsightDaysOfWeek ADD UNIQUE `sequenceNumber` (`sequenceNumber`);';
            $resultIndex = $connection2->prepare($sqlIndex);
            $resultIndex->execute($dataIndex);
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit();
            $return = false;
        }
    }

    return $return;
}

/**
 * Load the module manifest into an array. Handling the include in a function keeps the variable scope contained.
 * @param string $moduleName
 * @param string $guid
 * @return array
 */
function getModuleManifest($moduleName, $guid)
{
    $name = $description = $entryURL = $type = $category = $version = $author = $url = '';
    $manifestOK = false;

    $manifestFile = $_SESSION[$guid]['absolutePath'].'/modules/'.$moduleName.'/manifest.php';
    if (is_file($manifestFile)) {
        include $manifestFile;
        $manifestOK = ($name == $moduleName);
    }
    
    return compact('name', 'description', 'entryURL', 'type', 'category', 'version', 'author', 'url', 'manifestOK');
}

/**
 * Get the version number for a module from it's version.php file.
 *
 * @param string $moduleName
 * @param string $guid
 * @return string
 */
function getModuleVersion($moduleName, $guid)
{
    $versionFile = $_SESSION[$guid]['absolutePath'].'/modules/'.$moduleName.'/version.php';
    if (is_file($versionFile)) {
        include $versionFile;
       return $moduleVersion;
    } else {
        return false;
    }
}

/**
 * Load the theme manifest into an array. Handling the include in a function keeps the variable scope contained.
 * @param string $themeName
 * @param string $guid
 * @return array
 */
function getThemeManifest($themeName, $guid)
{
    $name = $description = $version = $author = $url = '';
    $responsive = 'N';
    $manifestOK = false;

    $manifestFile = $_SESSION[$guid]['absolutePath'].'/themes/'.$themeName.'/manifest.php';
    if (is_file($manifestFile)) {
        include $manifestFile;
        $manifestOK = ($name == $themeName);
    }
    
    return compact('themeName', 'name', 'description', 'version', 'author', 'url', 'responsive', 'manifestOK');
}

function getThemeVersion($themeName, $guid)
{
    $return = false;

    $file = file($_SESSION[$guid]['absolutePath']."/themes/$themeName/manifest.php");
    foreach ($file as $fileEntry) {
        if (substr($fileEntry, 1, 7) == 'version') {
            $temp = '';
            $temp = substr($fileEntry, 10, -1);
            $temp = substr($temp, 0, strpos($temp, '"'));
            $return = $temp;
        }
    }

    return $return;
}

function getCurrentVersion($guid, $connection2, $version)
{
    $output = '';

    $output .= '<script type="text/javascript">';
    $output .= '$(document).ready(function(){';
    /*
    $output .= '$.ajax({';
    $output .= 'crossDomain: true, type:"GET", contentType: "application/json; charset=utf-8",async:false,';
    $output .= 'url: "http://pupilsight.in/services/version/version.php?callback=?",';
    $output .= "data: \"\",dataType: \"jsonp\", jsonpCallback: 'fnsuccesscallback',jsonpResult: 'jsonpResult',";
    $output .= 'success: function(data) {';
    $output .= "if (data['version']==='false') {";
    $output .= '$("#status").attr("class","error");';
    $output .= "$(\"#status\").html('".__('Version check failed').".') ;";
    $output .= '}';
    $output .= 'else {';
    $output .= "if (versionCompare(data['version'], '".$version."') <= 0) {";
    $output .= '$("#status").attr("class","success");';
    $output .= "$(\"#status\").html('".sprintf(__('Version check successful. Your Pupilsight installation is up to date at %1$s.'), $version).' '.sprintf(__('If you have recently updated your system files, please check that your database is up to date in %1$sUpdates%2$s.'), "<a href=\'".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/System Admin/update.php\'>", '</a>')."') ;";
    $output .= '}';
    $output .= 'else {';
    */    
    $output .= '$("#status").attr("class","warning");';
    $output .= "$(\"#status\").html('".sprintf(__('Version check successful. Your Pupilsight installation is out of date. Please visit %1$s to download the latest version.'), "<a target=\"blank\" href=\'http://pupilsight.in/download\'>the Pupilsight download page</a>")."') ;";
    /*
    $output .= '}';
    $output .= '}';
    $output .= '},';
    $output .= 'error: function (data, textStatus, errorThrown) {';
    $output .= '$("#status").attr("class","error");';
    $output .= "$(\"#status\").html('".__('Version check failed').".') ;";
    $output .= '}';
    $output .= '});';
    */
    $output .= '});';
    $output .= '</script>';
    /*    
    $cuttingEdgeCode = getSettingByScope($connection2, 'System', 'cuttingEdgeCode');
    if ($cuttingEdgeCode != 'Y') {
        $output .= "<div id='status' class='warning'>";
        $output .= "<div style='width: 100%; text-align: center'>";
        $output .= "<img style='margin: 10px 0 5px 0' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/loading.gif' alt='Loading'/><br/>";
        $output .= __('Checking for Pupilsight updates.');
        $output .= '</div>';
        $output .= '</div>';
    }*/

    return $output;
}

/**
 * Checks to see if a pupilsight.mo language file exists for the given i18n code.
 *
 * @param string $absolutePath
 * @param string $code
 * @return bool
 */
function i18nFileExists($absolutePath, $code)
{
    return file_exists($absolutePath.'/i18n/'.$code.'/LC_MESSAGES/pupilsight.mo');
}

/**
 * Downloads and installs the pupilsight.mo file for a given i18n code.
 *
 * @param string $absolutePath
 * @param string $code
 * @return bool
 */
function i18nFileInstall($absolutePath, $code)
{
    // Grab the file contents from the PupilsightEdu i18n repository
    $gitHubURL = 'https://github.com/PupilsightEdu/i18n/blob/master/'.$code.'/LC_MESSAGES/pupilsight.mo?raw=true';
    $gitHubContents = file_get_contents($gitHubURL);

    if (empty($gitHubContents)) return false;

    // Locate where the i18n files will be copied to on the server
    $localPath = $absolutePath.'/i18n/'.$code.'/LC_MESSAGES/pupilsight.mo';
    $localDir = dirname($localPath);
    if (!is_dir($localDir)) {
        mkdir($localDir, 0755, true);
    }

    // Copy files
    return file_put_contents($localPath, $gitHubContents) !== false;
}

/**
 * Finds and sets any languages to installed='Y' if the file already exists.
 * Sets langueges to  installed='N' if the file no longer exits.
 *
 * @param ContainerInterface $container
 */
function i18nCheckAndUpdateVersion($container, $version = null)
{
    $absolutePath = $container->get('session')->get('absolutePath');

    $i18nGateway = $container->get(I18nGateway::class);
    $i18nList = $i18nGateway->selectActiveI18n()->fetchAll();

    foreach ($i18nList as $i18n) {
        $fileExists = i18nFileExists($absolutePath, $i18n['code']);

        if ($i18n['installed'] == 'N' && $fileExists) {
            $versionUpdate = version_compare($version, $i18n['version'], '>') ? $version : $i18n['version'];
            $i18nGateway->updateI18nVersion($i18n['pupilsighti18nID'], 'Y', $versionUpdate);
        } else if ($i18n['installed'] == 'Y' && !$fileExists) {
            $i18nGateway->updateI18nVersion($i18n['pupilsighti18nID'], 'N', null);
        }
    }
}

/**
 * Recursively remove the contents of a folder, including sub-directories. Optionally remove the folder itself.
 *
 * @param string $dir
 * @param bool   $removeSelf
 */
function removeDirectoryContents($dir, $removeSelf = false)
{
    if (!is_dir($dir)) return;

    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $filename => $fileInfo) {
        if ($fileInfo->isDir()) {
            rmdir($filename);
        } else {
            unlink($filename);
        }
    }

    if ($removeSelf) {
        rmdir($dir);
    }
}

function num2alpha($n)
{
    for ($r = ""; $n >= 0; $n = intval($n / 26) - 1) {
        $r = chr($n%26 + 0x41) . $r;
    }
    return $r;
}

function readableFileSize($bytes)
{
    $unit=array('bytes','KB','MB','GB','TB','PB');
    return @round($bytes/pow(1024, ($i=floor(log($bytes, 1024)))), 2).' '.$unit[$i];
}
