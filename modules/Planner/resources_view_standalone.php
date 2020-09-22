<?php
/*
Pupilsight, Flexible & Open School System

*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

echo "<link rel='stylesheet' type='text/css' href='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/css/main.css' />";
?>

<div id="wrap">
	<div id="header">
		<div id="header-left">
			<a href='<?php echo $_SESSION[$guid]['absoluteURL'] ?>'><img height='100px' width='400px' class="logo" alt="Logo" title="Logo" src="<?php echo $_SESSION[$guid]['absoluteURL'].'/'.$_SESSION[$guid]['organisationLogo']; ?>"/></a>
		</div>
		<div id="header-right">

		</div>
	</div>
	<div id="content-wrap">
		<div id="content">
			<?php
            if (isActionAccessible($guid, $connection2, '/modules/Planner/resources_view_full.php') == false) {
                //Acess denied
                echo "<div class='alert alert-danger'>";
                echo __('Your request failed because you do not have access to this action.');
                echo '</div>';
            } else {
                //Proceed!
                //Get class variable
                $pupilsightResourceID = $_GET['pupilsightResourceID'];
                if ($pupilsightResourceID == '') {
                    echo "<div class='alert alert-warning'>";
                    echo 'Resource has not been specified .';
                    echo '</div>';
                }
                //Check existence of and access to this resource.
                else {
                    try {
                        $data = array('pupilsightResourceID' => $pupilsightResourceID);
                        $sql = 'SELECT * FROM pupilsightResource WHERE pupilsightResourceID=:pupilsightResourceID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($result->rowCount() != 1) {
                        echo "<div class='alert alert-warning'>";
                        echo __('The specified record does not exist.');
                        echo '</div>';
                    } else {
                        $row = $result->fetch();

                        echo '<h1>';
                        echo $row['name'];
                        echo '</h1>';

                        echo $row['content'];
                    }
                }
            }
            ?>
		</div>
		<div id="sidebar">
		</div>
	</div>
	<div id="footer">
		<a href="http://pupilsight.in">Pupilsight</a> v<?php echo $version ?> | &#169; 2011, <a href="http://www.pupilsight.in">Pupilsight</a> at <a href="http://www.ichk.edu.hk">International College Hong Kong</a> | Created under the <a href="https://www.gnu.org/licenses/gpl.html">GNU General Public License</a>
	</div>
</div>
