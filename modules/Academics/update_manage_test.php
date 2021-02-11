<?php
   /*
   Pupilsight, Flexible & Open School System
   */
   
   use Pupilsight\Forms\Form;
   use Pupilsight\Forms\DatabaseFormFactory;
   
   if (isActionAccessible($guid, $connection2, '/modules/Academics/update_manage_test.php') == false) {
       //Acess denied
       echo "<div class='error'>";
       echo __('You do not have access to this action.');
       echo '</div>';
   } else {
       //Proceed!  

       $page->breadcrumbs
       ->add(__('Manage Test'), 'manage_test.php')
       ->add(__('Update Test'));
   
       if (isset($_GET['return'])) {
           returnProcess($guid, $_GET['return'], null, null);
       }
       $test_master_id = $_GET['tid'];
       $sql = 'SELECT a.*, c.name as academic_year,e.name as classname, d.name as progname, f.name as secname FROM examinationTest AS a LEFT JOIN examinationTestAssignClass AS b ON a.id = b.test_id LEFT JOIN pupilsightSchoolYear AS c ON a.pupilsightSchoolYearID = c.pupilsightSchoolYearID LEFT JOIN pupilsightProgram AS d ON b.pupilsightProgramID=d.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS e ON b.pupilsightYearGroupID=e.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS f ON b.pupilsightRollGroupID=f.pupilsightRollGroupID WHERE a.test_master_id = '.$test_master_id.' GROUP BY a.id';
       $result = $connection2->query($sql);
       $tests = $result->fetchAll();
       if(!empty($tests)){
         
           $sqlterm = 'SELECT * FROM pupilsightSchoolYearTerm ORDER BY pupilsightSchoolYearTermID ASC';
           $resultterm = $connection2->query($sqlterm);
           $termdata = $resultterm->fetchAll();
   
           $sqlgrade = 'SELECT * FROM examinationGradeSystem ORDER BY id ASC';
           $resultgrade = $connection2->query($sqlgrade);
           $gradedata = $resultgrade->fetchAll();

           $sqltemplate = 'SELECT * FROM examinationReportTemplateMaster ORDER BY id ASC';
           $resulttemplate = $connection2->query($sqltemplate);
           $templatedata = $resulttemplate->fetchAll();

           $sqlsketch = 'SELECT * FROM examinationReportTemplateSketch ORDER BY id ASC';
           $resultsketch = $connection2->query($sqlsketch);
           $sketchdata = $resultsketch->fetchAll();
   ?>
<h2> Update To Test</h2>
<div class="input-group stylish-input-group">
   <div class="flex-1 relative">
      <a style='display:none' id='showSmsEmailPopup' href='fullscreen.php?q=/modules/Academics/send_email_sms.php&width=800'  class='thickbox '></a>
      <select class='btn-fill-md bg-dodger-blue left_align' id="updateByColumnType" name="" class="w-full" style="margin-top: 7px;">
         <option value="">Select</option>
         <option value="name">Test Name</option>
         <option value="pupilsightSchoolYearTermID">Test Type</option>
         <option value="assesment_method">Assesment Method</option>
         <option value="assesment_option">Assesment Option</option>
         <option value="gradeSystemId">Grading System</option>
         <option value="max_marks">Max Marks</option>
         <option value="min_marks">Min Marks</option>
         <option value="report_template_id">Report Template</option>
         <option value="sketch_id">Sketch Template</option>
      </select>
      &nbsp;&nbsp;
      <input id="name" type="text" name="name" class="name hideUpdateOption" style="display:none;" placeholder="Test Name">
      <select id="pupilsightSchoolYearTermID" name="pupilsightSchoolYearTermID" class=" pupilsightSchoolYearTermID hideUpdateOption" style="display:none; float: none;">
         <option value="">Select Type</option>
         <?php if(!empty($termdata)){
            foreach($termdata as $term){ ?>
         <option value="<?php echo $term['pupilsightSchoolYearTermID'];?>"><?php echo $term['name'];?></option>
         <?php } } ?>
      </select>
      <select name="assesment_method" class=" assesment_method hideUpdateOption" id="assesment_method" style="display:none; float: none;">
         <option value="">Select Method</option>
         <option value="Marks">Marks</option>
         <option value="Grade">Grade</option>
      </select>
      <select id="assesment_option" style="display:none; float: none;" name="assesment_option" class="assesment_option hideUpdateOption">
         <option value="">Select Option</option>
         <option value="Radio Button">Radio Button</option>
         <option value="Dropdown">Dropdown</option>
      </select>
      <select id="gradeSystemId" style="display:none; float: none;" name="gradeSystemId" class="gradeSystemId  hideUpdateOption">
         <option value="">Select Grading System</option>
         <?php if(!empty($gradedata)) { 
            foreach($gradedata as $gd){ ?>
         <option value="<?php echo $gd['id']; ?>"><?php echo $gd['name']; ?></option>
         <?php  } } ?>
      </select>
      <select id="report_template_id" style="display:none; float: none;" name="report_template_id" class="report_template_id  hideUpdateOption">
         <option value="">Select Report Template</option>
         <?php if(!empty($templatedata)) { 
            foreach($templatedata as $templ){ ?>
         <option value="<?php echo $templ['id']; ?>"><?php echo $templ['name']; ?></option>
         <?php  } } ?>
      </select>
      <select id="sketch_id" style="display:none; float: none;" name="sketch_id" class="sketch_id  hideUpdateOption">
         <option value="">Select Sketch</option>
         <?php if(!empty($sketchdata)) { 
            foreach($sketchdata as $skt){ ?>
         <option value="<?php echo $skt['id']; ?>"><?php echo $skt['sketch_name']; ?></option>
         <?php  } } ?>
      </select>
      <input id="max_marks" style="display:none;" type="text" name="max_marks" class="max_marks hideUpdateOption" placeholder="Max Marks">  
      <input id="min_marks" style="display:none;" type="text" name="min_marks" class="min_marks hideUpdateOption" placeholder="Min Marks">
      &nbsp;&nbsp;
      <a id="updateTestBulkWise" class=" btn btn-primary">Save</a>
      
      &nbsp;&nbsp;
      <i id="sent_message_icon" data-hrf='fullscreen.php?q=/modules/Academics/send_email_sms.php'  title="Send Marks SMS/EMAIL" class="mdi mdi-circle-edit-outline mdi-24px iconsize"></i>
      <span style="font-size: 25px;padding: 5px;">|</span>
      <i id="updateTestSettings" data-type="lock_marks_entry" data-val="1" title="Lock Marks Entry" class="mdi mdi-lock mdi-24px iconsize"></i>
      
      <span style="font-size: 25px;padding: 5px;">|</span>
      
      <i id="updateTestSettings" data-type="lock_marks_entry" data-val="0" title="UnLock Marks Entry" class="mdi mdi-lock-open mdi-24px iconsize"></i>

      <span style="font-size: 25px;padding: 5px;">|</span>

      <i id="updateTestSettings" data-type="enable_publish" data-val="1" title="Publish"  class="mdi mdi-earth mdi-24px iconsize"></i>

      <span style="font-size: 25px;padding: 5px;">|</span>

      <i id="updateTestSettings" data-type="enable_publish" data-val="0" title="UnPublish"  class="mdi mdi-stop-circle mdi-24px iconsize"></i>

      <span style="font-size: 25px;padding: 5px;">|</span>

      <i id="updateTestSettings" data-type="enable_html" data-val="1" title="Show HTML"  class="mdi mdi-file-code mdi-24px iconsize"></i>

      <span style="font-size: 25px;padding: 5px;">|</span>

      <i id="updateTestSettings" data-type="enable_html" data-val="0" title="Hide HTML"  class="mdi mdi-file-code mdi-24px iconsize" style="color: darkgrey;"></i>

      <span style="font-size: 25px;padding: 5px;">|</span>

      <i id="updateTestSettings" data-type="enable_pdf" data-val="1" title="Show PDF"  class="mdi mdi-file-pdf mdi-24px iconsize"></i>

      <span style="font-size: 25px;padding: 5px;">|</span>

      <i id="updateTestSettings" data-type="enable_pdf" data-val="0" title="Hide PDF"  class="mdi mdi-file-pdf mdi-24px iconsize" style="color: darkgrey;"></i>

      <span style="font-size: 25px;padding: 5px;">|</span>
      
      <i id="updateTestSettings" data-type="enable_test_report" data-val="1" title="Lock Test Report" class="mdi mdi-lock mdi-24px iconsize"></i>
      
      <span style="font-size: 25px;padding: 5px;">|</span>
      
      <i id="updateTestSettings" data-type="enable_test_report" data-val="0" title="UnLock Test Report" class="mdi mdi-lock-open mdi-24px iconsize"></i>

      <span style="font-size: 25px;padding: 5px;">|</span>

      <i id="downloadReport" data-hrf="thirdparty/phpword/reportcardmultiple.php?tid=" class="mdi mdi-download mdi-24px iconsize" aria-hidden="true" title="Report Download" style="cursor:pointer;"></i>

      <a id="reportDownload" href="thirdparty/phpword/reportcardmultiple.php?tid=" style="display:none;">click</a>
     
   </div>
</div>
<form name="">
   <div class='table-responsive dataTables_wrapper '>
      <table class="table" >
         <thead>
            <tr>
               <th >
                  <input type='checkbox' class="chkAll">
               </th>
               <th >
                  <lable>Test name</lable>
               </th>
               <th>
                  <lable>Academic Year</lable>
               </th>
               <th>
                  <lable>Program</lable>
               </th>
               <th>
                  <lable>Class</lable>
               </th>
               <th>
                  <lable>Marks Entered</lable>
               </th>
               <th>
                  <lable>D.I Entered</lable>
               </th>
               <th>
                  <lable>Lock</lable>
               </th>
               <th>
                  <lable>Publish</lable>
               </th>
               <th>
                  <lable>Show Html</lable>
               </th>
               <th>
                  <lable>Show PDF</lable>
               </th>
               <th>
                  <lable>Lock Test Report</lable>
               </th>
               <th>
                  <lable>SMS</lable>
               </th>
               <th>
                  <lable>Email</lable>
               </th>
            </tr>
         </thead>
         <tbody>
            <?php foreach($tests as $tst){ ?>
            <tr>
               <td>
                  <input type='checkbox' name="id[]" class=" testid chkChild" data-name="<?php echo $tst['name'];?>" value="<?php echo $tst['id'];?>">
               </td>
               <td>
                  <p> <strong><?php echo $tst['name'];?></strong></p>
               </td>
               <td>
                  <p> <strong><?php echo $tst['academic_year'];?></strong></p>
               </td>
               <td>
                  <p> <strong><?php echo $tst['progname'];?></strong></p>
               </td>
               <td>
                  <p> <strong><?php echo $tst['classname'].' - '.$tst['secname'];?></strong></p>
               </td>
               <td>
                  <?php if(!empty($tst['enable_marks_entry']) && $tst['enable_marks_entry'] == '1'){ ?>
                     <i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon tick_icon"></i>
                  <?php } else { ?>   
                     <i class="mdi mdi-close-circle mdi-24px text-red w-full"></i>
                  <?php } ?>
               </td>
               <td>
                  <?php if(!empty($tst['enable_descriptive_entry']) && $tst['enable_descriptive_entry'] == '1'){ ?>
                     <i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon tick_icon"></i>
                  <?php } else { ?>   
                     <i class="mdi mdi-close-circle mdi-24px text-red w-full"></i>
                  <?php } ?>
               </td>
               <td>
                  <?php if(!empty($tst['lock_marks_entry']) && $tst['lock_marks_entry'] == '1'){ ?>
                     <i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon tick_icon"></i>
                  <?php } else { ?>   
                     <i class="mdi mdi-close-circle mdi-24px text-red w-full"></i>
                  <?php } ?>
               </td>
               <td>
                  <?php if(!empty($tst['enable_publish']) && $tst['enable_publish'] == '1'){ ?>
                     <i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon tick_icon"></i>
                  <?php } else { ?>   
                     <i class="mdi mdi-close-circle mdi-24px text-red w-full"></i>
                  <?php } ?>
               </td>
               <td>
                  <?php if(!empty($tst['enable_html']) && $tst['enable_html'] == '1'){ ?>
                     <i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon tick_icon"></i>
                  <?php } else { ?>   
                     <i class="mdi mdi-close-circle mdi-24px text-red w-full"></i>
                  <?php } ?>
               </td>
               <td>
                  <?php if(!empty($tst['enable_pdf']) && $tst['enable_pdf'] == '1'){ ?>
                     <i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon tick_icon"></i>
                  <?php } else { ?>   
                     <i class="mdi mdi-close-circle mdi-24px text-red w-full"></i>
                  <?php } ?>
               </td>
               <td>
                  <?php if(!empty($tst['enable_test_report']) && $tst['enable_test_report'] == '1'){ ?>
                     <i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon tick_icon"></i>
                  <?php } else { ?>   
                     <i class="mdi mdi-close-circle mdi-24px text-red w-full"></i>
                  <?php } ?>
               </td>
               <td>
                  <?php if(!empty($tst['enable_sms']) && $tst['enable_sms'] == '1'){ ?>
                     <i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon tick_icon"></i>
                  <?php } else { ?>   
                     <i class="mdi mdi-close-circle mdi-24px text-red w-full"></i>
                  <?php } ?>
               </td>
               <td>
                  <?php if(!empty($tst['enable_email']) && $tst['enable_email'] == '1'){ ?>
                     <i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon tick_icon"></i>
                  <?php } else { ?>   
                     <i class="mdi mdi-close-circle mdi-24px text-red w-full"></i>
                  <?php } ?>
               </td>
            </tr>
            <?php } ?>        
         </tbody>
      </table>
   </div>
</form>
<?php
   } }
   ?>
<script type="text/javascript">
   $(document).on('click', '#sent_message_icon', function() {
        if ($("input[name='id[]']").is(':checked')) {
            var checked = $("input[name='id[]']:checked").length;
            if (checked > 1) {
                alert("Please Select One Test!");
                return false;
            } else {
                var hrf = $(this).attr('data-hrf');
                var id = $("input[name='id[]']:checked").val();
                var name = $("input[name='id[]']:checked").attr('data-name');
                if (id != '') {
                    var newhrf = hrf + '&tid=' + id+ '&name='+name;
                    $("#showSmsEmailPopup").attr('href', newhrf);
                    $("#showSmsEmailPopup").click();
                } else {
                    alert("Please Select Test!");
                }
            }
        } else {
            alert("Please Select Test!");
        }
    });
    
   $(document).on('click','#send_sms_or_email',function(){
     var formData = $("#send_email_sms_form").serialize();
      var err=0;
       $("#preloader").show();
      setTimeout(function(){
      $.ajax({
                url: 'sendSwitch.php',
                type: 'post',
                data: formData,
                async: true,
                success: function(response) {
                  $("#preloader").hide();
                        var obj = JSON.parse(response);
                       if(obj.status=="ok"){
                         $("#TB_closeWindowButton").click();
                             alert('Marks sent successfully.');
                             window.location.reload();
                       } else {
                        alert(obj.msg);
                       }
                }
            });
      },2000);
   });
   
   $(document).on('change','.check_status',function(){
     var table = $(this).attr('data-type');
      $("."+table).slideToggle();
   });

   
   $(document).on('click', '#downloadReport', function() {
         var hrf = $(this).attr('data-hrf');
         var sub = [];
         $.each($("input[name='id[]']:checked"), function() {
            sub.push($(this).val());
         });
         var subid = sub.join(",");
         if (subid != '') {
            var newhrf = hrf+subid;
            $("#reportDownload").attr('href', newhrf);
            window.setTimeout(function() {
               $("#reportDownload")[0].click();
            }, 10);
         } else {
            alert('You Have to Select Test!');
         }
    });
</script>