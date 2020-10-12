<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/view_selected_campaign_form.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $id = $_GET['id'];
   
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM campaign WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            echo '<h2>';
            echo __('Application form');
            echo '</h2>';
            $values = $result->fetch();

            $viewurl = 'wp/?fluentform_pages=1&preview_id='.$values['form_id'];


            ?>
              <div class="wpb_text_column wpb_content_element   mobile-center">
                                    <div class="wpb_wrapper">
									
						 <iframe data-campid="<?php echo $id;?>" id="application_view" height="500" width="1000"
    src="<?php echo $viewurl;?>">
</iframe>

                                    </div>
                                </div>

            <?php
        }
    }
}
?>
<script>
    
    var iframe = document.getElementById("application_view");
    
    // Adjusting the iframe height onload event
    // iframe.onload = function(){
    //     iframe.style.height = (Number(iframe.contentWindow.document.body.scrollHeight) + 100) + 'px';
    // }

    $('#application_view').load(function(){
        var iframe = $('#application_view').contents();
        //iframe.find("#wpadminbar").hide();
        //iframe.find(".section-inner").hide();
        iframe.find(".ff-btn-submit").prop('disabled', true);
        iframe.find("#ff_preview_only").prop('checked', true);
        iframe.find(".ff_preview_body").addClass('ff_preview_only');
        
    });

</script>