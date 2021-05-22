<?php
include_once "cms/w2f/adminLib.php";
$adminlib = new adminlib();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["loginuser"])){

$data = $adminlib->getPupilSightData();

$section = $adminlib->getPupilSightSectionFrontendData();

$campaign = $adminlib->getcampaign();
//session_start();


function getDomain()
{
    if (isset($_SERVER["HTTPS"])) {
        $protocol =
            $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off" ? "https" : "http";
    } else {
        $protocol = "http";
    }
    //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $protocol . "://" . $_SERVER["HTTP_HOST"];
}
//$baseurl = getDomain().'/pupilsight';
$baseurl = getDomain();




if(isset($_GET['sid'])){
    $subid = $_GET['sid'];
    $dataApplicant = $adminlib->getCampaignData($_GET['sid']);
    if(!empty($dataApplicant)){
        $len = count($dataApplicant);
        $i = 0;
        $dt = array();
        while($i<$len){
            $dt[$dataApplicant[$i]["field_name"]] = $dataApplicant[$i]["field_value"];
            $i++;
        }

        $priority_contact = "father";
        if(isset($dt["priority_contact"])){
            $priority_contact = strtolower($dt["priority_contact"]);
            if($priority_contact=="father"||$priority_contact=="mother"){
                //
            }else{
                $priority_contact = "father";
            }
        }


        $dt["parent_name"] = $dt["father_name"];
        $dt["parent_immigration_status"] = $dt["father_immigration_status"];
        $dt["parent_nationality"] = $dt["father_nationality"];
        $dt["parent_email"] = $dt["father_email"];
        $dt["parent_passport_no"] = $dt["father_passport_no"];
        $dt["parent_passport_expiry"] =  $dt["father_passport_expiry"];
        $dt["parent_nric_no"] =  $dt["father_nric_no"];
        $dt["parent_nric_expiry"] = $dt["father_nric_expiry"];
        $dt["parent_company_name"] = '';
        if(isset($dt["father_company_name"])){
            $dt["parent_company_name"] =  $dt["father_company_name"];
        }
        $dt["parent_occupation"] =  '';
        if(isset($dt["father_occupation"])){
            $dt["parent_occupation"] =  $dt["father_occupation"];
        }
        

        if($dt["priority_contact"]=="Mother"){
            $dt["parent_name"] = $dt["mother_name"];
            $dt["parent_immigration_status"] = $dt["mother_immigration_status"];
            $dt["parent_nationality"] = $dt["mother_nationality"];
            $dt["parent_email"] = $dt["mother_email"];
            $dt["parent_passport_no"] = $dt["mother_passport_no"];
            $dt["parent_passport_expiry"] =  $dt["mother_passport_expiry"];
            $dt["parent_nric_no"] =  $dt["mother_nric_no"];
            $dt["parent_nric_expiry"] = $dt["mother_nric_expiry"];
            $dt["parent_company_name"]=  $dt["mother_company_name"];
            $dt["parent_occupation"] =  $dt["mother_occupation"];
        }

        
        $sql = 'SELECT a.pupilsightProgramID, a.pupilsightYearGroupID, a.is_contract_generated, b.academic_id, c.name, c.sequenceNumber, p.name as progname FROM wp_fluentform_submissions AS a LEFT JOIN campaign AS b ON a.form_id = b.form_id LEFT JOIN pupilsightProgram AS p ON a.pupilsightProgramID = p.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS c ON a.pupilsightYearGroupID = c.pupilsightYearGroupID AND b.academic_id = c.pupilsightSchoolYearID WHERE a.id = "'.$subid.'" ';
        $subData = database::doSelectOne($sql);
        $pupilsightSchoolYearID = $subData['academic_id'];
        $progName = $subData['progname'];
        $className = $subData['name'];
        $sequenceNumber = $subData['sequenceNumber'];
        $nextsequenceNumber = $sequenceNumber + 1;

        $is_contract_generated = $subData['is_contract_generated'];

        $nextClassName = '';
        $sql1 = 'SELECT name FROM pupilsightYearGroup WHERE pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND sequenceNumber = "'.$nextsequenceNumber.'" ';
        $subData1 = database::doSelectOne($sql1);
        if(!empty($subData1)){
            $nextClassName = $subData1['name'];
        }
    } else{
        $redirectUrl = $baseurl.'/index.php?q=/modules/Campaign/check_status.php';
        header("Location:".$redirectUrl);
        die();
    }
   
}else{
    $redirectUrl = $baseurl.'/index.php?q=/modules/Campaign/check_status.php';
    header("Location:".$redirectUrl);
    die();
}

$logo = $baseurl . "/cms/images/pupilpod_logo.png";
$hero_image = $baseurl . "/cms/images/welcome.png";
$about_us = $baseurl . "/cms/images/about_us.png";
$announcements = $baseurl . "/cms/images/announcements.png";
$chairmans_message = $baseurl . "/cms/images/chairmans_message.png";
$events = $baseurl . "/cms/images/events.png";
$courses = $baseurl . "/cms/images/courses.png";

$title = isset($data["title"]) ? ucwords($data["title"]) : "Pupilpod";
$cms_banner_title = isset($data["cms_banner_title"])
    ? $data["cms_banner_title"]
    : "Over a decade’s legacy";
$cms_banner_short_description = isset($data["cms_banner_short_description"])
    ? $data["cms_banner_short_description"]
    : "of bringing cutting edge technology to education.";
if (
    isset($data["cms_banner_image_path"]) &&
    file_exists($data["cms_banner_image_path"])
) {
    $hero_image = $data["cms_banner_image_path"];
}

$logo = $baseurl . "/cms/images/pupilpod_logo.png";
if (isset($data["logo_image"])) {
    $logo = $baseurl . "/cms/images/logo/" . $data["logo_image"];
}

$invalid = "";
if (isset($_GET["invalid"])) {
    $invalid = $_GET["invalid"];
}
?>

<input type="hidden" name="inavlid" id="invalid" value="<?php echo $invalid; ?>" />


<!doctype html>
<html class="no-js" lang="">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Contract Form</title>
    <meta name="description"
        content="Pupilpod is India’s first cloud based School ERP Software. It is 100% customizable and evolves to meet each School or University’s needs.
    Discover how with Pupilpod you can automate your entire Academic, Operational, and Management information systems" />
    <meta name="keywords" content="Pupilpod,School ERP,erp,School ERP Software, School Management Solution">

    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <meta name="msapplication-TileColor" content="#206bc4" />
    <meta name="theme-color" content="#206bc4" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="HandheldFriendly" content="True" />
    <meta name="MobileOptimized" content="320" />
    <meta name="robots" content="noindex,nofollow,noarchive" />
    <link rel="icon" href="./favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon" />

    <!-- CSS files -->

    <link rel="stylesheet" href="//cdn.materialdesignicons.com/5.0.45/css/materialdesignicons.min.css">


    <link rel="stylesheet" href="<?= $baseurl ?>/assets/css/normalize.css?v=1.0" type="text/css" media="all" />

    <link href="<?= $baseurl ?>/assets/css/tabler.css" rel="stylesheet" />
    <link href="<?= $baseurl ?>/assets/css/dev.css" rel="stylesheet" />

    <!-- Libs JS -->
    <script src="<?= $baseurl ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseurl ?>/assets/libs/jquery/dist/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="<?= $baseurl ?>/assets/libs/jquery/jquery-migrate.min.js?v=1.0"></script>


    <script src="<?= $baseurl ?>/assets/js/core.js"></script>

    <script src="<?= $baseurl ?>/assets/js/tabler.min.js"></script>
    <script type="text/javascript" src="<?= $baseurl ?>/assets/js/jquery.form.js?v=1.0"></script>


    <style>
    body {
        font-size: 16px;
    }
    </style>

</head>

<body id='chkCounterSession' class='antialiased'>
<!-- Preloader Start Here -->
<div id="preloader" style="display:none;"></div>
    <!-- Preloader End Here -->

    <div class="container" id="contentPanel">
        <div class="row my-5">
            <div class="col">
                <h2 class="page-title">
                    Student Contract
                </h2>
            </div>
            <div class="col-12 mt-2">
                <div class="card">
                    <div class="card-header my-4">
                        <h2>
                            <div class="row">
                                <div class="col-12 text-center">
                                    PART A – PRIVATE EDUCATION INSTITUTION – STUDENT CONTRACT
                                </div>
                                <div class="col-12 text-right">
                                    Regulation 25(5)(b)
                                </div>
                                <div class="col-12 text-center">
                                    <br>FORM 12
                                    <br>PRIVATE EDUCATION ACT
                                    <br>(No. 21 of 2009)
                                    <br>PRIVATE EDUCATION REGULATIONS
                                    <br>ADVISORY NOTE TO STUDENTS
                                </div>
                            </div>
                        </h2>
                    </div>
                    <div class="card-body px-5">
                        <div class="row">
                            <div class="col-12 my-3">
                                This note is for a prospective student.
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 my-3">
                                <p>
                                    You are strongly encouraged to thoroughly research on the Private Education
                                    Institution (PEI) conducting the course before signing up for any course. You should
                                    consider, for example, the reputation of the PEI, the teacher-student ratio of its
                                    classes, the qualifications of the teaching staff, and the course materials provided
                                    by the PEI.
                                </p>
                                <p>
                                    By signing and returning the Student Contract (the “Contract”), you agree to the
                                    Terms and Conditions which will bind you and the PEI, if you accept the PEI’s offer
                                    of a place in a course of study offered or provided by the PEI.
                                </p>
                                <p>
                                    You should ask the PEI to allow you to read a copy of the Contract (with all blanks
                                    filled in and options selected) in both English and the official language of your
                                    home country, if necessary. For your own protection, you should review all the PEI’s
                                    policies, and check carefully that you agree to all the terms of the Contract,
                                    including the details relating to each of the following sections, before signing the
                                    Contract:
                                </p>
                                <ol type="a">
                                    <li>
                                        The duration of the course, including holidays and examination schedules, and
                                        contact hours by days and week;
                                    </li>
                                    <li>
                                        The total fees payable, including course fees and other related costs;
                                    </li>
                                    <li>
                                        Dates when respective payments are due;
                                    </li>
                                    <li>
                                        The refund policy in the event of voluntary withdrawal (by you) or enforced
                                        dismissal from the course or programme (by PEI);
                                    </li>
                                    <li>
                                        The Fee Protection Scheme you are subscribed to and its coverage;
                                    </li>
                                    <li>
                                        The dispute resolution methods available; and
                                    </li>
                                    <li>
                                        Information about the PEI’s policies on academic and disciplinary matters
                                    </li>
                                    <li>
                                        The degree or diploma or qualification which will be awarded to you upon
                                        successful completion of the course.
                                    </li>
                                </ol>
                                <p class='mt-2'>
                                    If you have any doubt about the contents of the Contract, or if the terms are
                                    different
                                    from what the agent or the PEI have informed you previously, or advertised, you
                                    should always seek advice and/or clarifications before signing the Contract.
                                </p>
                                <p>
                                    This portion below is to be completed by the signatory of the Student Contract, i.e.
                                    either the student, or if the student is below the age of 18, his parent or
                                    guardian.
                                </p>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-auto">
                                I,
                            </div>
                            <div class="col">
                                <input readonly type="text" class="form-control" name="parent_name" id="parent_name" value="<?=$dt["parent_name"];?>">
                            </div>
                            <div class="col-12 text-center font-italic">
                                (Name of Parent/ Guardian)
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="nric" id="nric" value="<?=$dt["parent_nric_no"];?>">
                                <div class='text-center font-italic'>(NRIC)</div>
                            </div>
                            <div class="col-md-6 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="passport_number" id="passport_number" value="<?=$dt["parent_passport_no"];?>">
                                <div class='text-center font-italic'>(Passport Number)</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mt-3">
                                Have read and understood this advisory note before signing the Student Contract
                            </div>
                            <div class="col-auto">
                                For myself/ my ward**
                            </div>
                            <div class="col">
                                <input readonly type="text" class="form-control" name="parent_name" id="parent_name" value="<?=$dt["parent_name"];?>">
                            </div>
                            <div class="col-12 mb-3">
                                <div class='text-center font-italic'>(Name of Child)</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="st_nric" id="st_nric" value="<?=$dt["student_nric_no"];?>">
                                <div class='text-center font-italic'>(NRIC)</div>
                            </div>
                            <div class="col-md-6 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="st_passport_number"
                                    id="st_passport_number" value="<?=$dt["student_passport_no"];?>">
                                <div class='text-center font-italic'>(Passport Number)</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-auto">
                                with <b><u>G I G International School Pte Ltd.</u></b>
                                <div class="text-center font-italic">(Name of PEI)</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-auto my-3">
                                <!-- <input readonly type="file" class="form-control" name="parent_signature" id="parent_signature"> -->
                                <!-- <div class="text-center font-italic">(Signature Of Parent)</div> -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-12 my-3">
                                <input readonly type='text' class="form-control" name="date" id="date" value='<?= date("m/d/Y");?>'>
                                <div class="text-center font-italic">(Date)</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-center my-5">
                                <h2>PRIVATE EDUCATION INSTITUTION-STUDENT CONTRACT</h2>
                            </div>
                            <div class="col-12">
                                <p>This Contract binds both the Private Education Institution (PEI) and the Student once
                                    both parties sign this Contract. If the Student is under eighteen (18) years of age,
                                    the Student will be represented by the Parent/Legal Guardian.</p>
                                <p>
                                    This Contract is made between:
                                </p>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-12">
                                <ol>
                                    <li>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 my-3">Registered Name of
                                                PEI<br />Registration Number
                                            </div>
                                            <div class="col-md-6 col-sm-12 my-3"><u>G I G International School Pte
                                                    Ltd</u><br /><u>201000716D</u></div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 my-3">Full Name of Student
                                            </div>
                                            <div class="col-md-6 col-sm-12 my-3"><input readonly type="text" class="form-control"
                                                    name="student_name" id="student_name" value="<?=$dt["student_name"];?>"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 my-3">(as in NRIC for Singapore Citizen (SC) and
                                                Permanent Resident (PR) / as in passport for international student)*
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 my-3">NRIC Number (for SC/PR)*</div>
                                            <div class="col-md-6 col-sm-12 my-3"><input readonly type="text" class="form-control"
                                                    name="nric_number" id="nric_number" required value="<?=$dt["student_nric_no"];?>"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 my-3">Student’s Pass Number (if
                                                available)/<br />Passport Number (for international student)*</div>
                                            <div class="col-md-6 col-sm-12 my-3"><input readonly type="text" class="form-control"
                                                    name="st_pass_number" id="st_pass_number" required value="<?=$dt["student_passport_no"];?>"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 my-3">Full Name of Parent/Legal
                                                Guardian*<br />(if Student is under eighteen (18) years of age)</div>
                                            <div class="col-md-6 col-sm-12 my-3"><input readonly type="text" class="form-control"
                                                    name="parent_legal_name" id="parent_legal_name" required value="<?=$dt["parent_name"];?>"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 my-1">NRIC/Passport Number*</div>
                                            <div class="col-md-6 col-sm-12 my-1"><input readonly type="text" class="form-control"
                                                    name="parent_legal_passport_number"
                                                    id="parent_legal_passport_number" required value="<?=$dt["parent_passport_no"];?>"></div>
                                        </div>
                                    </li>
                                </ol>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 my-3 font-italic">
                                Where non-applicable, put “N.A.”. Leave no fields blank.<br />
                                State all dates in the format of DD/MM/YYYY.
                            </div>
                        </div>
                        <!----Course Information and Fees--->
                        <div class="row">
                            <div class="col-12">
                                <ol>
                                    <li>
                                        <h3>COURSE INFORMATION AND FEES</h3>
                                        <ol type="I">
                                            <li>The PEI will deliver the Course as set out in Schedule A to the Student,
                                                towards conferment of the stated qualification upon successful Course
                                                completion.</li>
                                            <li>The PEI confirms that the Course has been permitted by the Committee for
                                                Private Education (CPE) and no amendments have been made to the Course
                                                as set out in Schedule A, unless otherwise permitted by CPE.</li>
                                            <li>The Course Fees payable are set out in Schedule B and the optional
                                                Miscellaneous Fees in Schedule C.</li>
                                            <li>The PEI considers payment made 3 days/month* after the scheduled due
                                                date(s) in Schedule B as late. The PEI will explain to the Student its
                                                policy for late payment of Course Fees, including any late payment fee
                                                charged in Schedule C (if applicable) and any impact on Course/module
                                                completion (if applicable).</li>
                                        </ol>

                                    </li>
                                    <li>
                                        <h3 class="my-3">REFUND POLICY</h3>
                                        <h3>2.1 <u>Refund for Withdrawal Due to Non-Delivery of Course:</u></h3>
                                        <ol type='I'>The PEI will notify the Student within three (3) working days upon
                                            knowledge of any of the following:<li>It does not commence the Course on the
                                                Course Commencement Date;</li>
                                            <li>It terminates the Course before the Course Commencement Date;</li>
                                            <li>It does not complete the Course by the Course Completion Date;</li>
                                            <li> It terminates the Course before the Course Completion Date;</li>
                                            <li>It has not ensured that the Student meets the course entry or
                                                matriculation requirement as set by the organisation stated in Schedule
                                                A within any stipulated timeline set by CPE; or
                                            </li>
                                            <li>The Student’s Pass application is rejected by Immigration and
                                                Checkpoints Authority (ICA).</li>
                                            <li>The Student should be informed in writing of alternative study
                                                arrangements (if any), and also be entitled to a refund of the entire
                                                Course Fees and Miscellaneous Fees already paid should the Student
                                                decide to withdraw, within seven (7) working days of the above notice.
                                            </li>
                                        </ol>
                                        <h3 class="my-3">2.2 <u>Refund for Withdrawal Due to Other Reasons:</u></h3>
                                        If the Student withdraws from the Course for any reason other than those stated
                                        in Clause 2.1, the PEI will, within seven (7) working days of receiving the
                                        Student’s written notice of withdrawal, refund to the Student an amount based on
                                        the table in Schedule D.

                                        <h3 class="my-3">2.3 <u>Refund During Cooling-Off Period:</u></h3>
                                        The PEI will provide the Student with a cooling-off period of seven (7) working
                                        days after the date that the Contract has been signed by both parties.
                                        The Student will be refunded the highest percentage (stated in Schedule D) of
                                        the fees already paid if the Student submits a written notice of withdrawal to
                                        the PEI within the cooling-off period, regardless of whether the Student has
                                        started the course or not.</h3>
                                    </li>
                                    <li>
                                        <h3 class="my-3">ADDITIONAL INFORMATION</h3>
                                        <br><b>3.1 </b>laws of Singapore will apply to how this Contract will be read
                                        and to the rights the parties have under this Contract.
                                        <br><b>3.2 </b>If any part of this Contract is not valid for any reason under
                                        the law of Singapore, this will not affect any other part of this Contract.
                                        <br><b>3.3 </b>the Student and the PEI cannot settle a dispute using the way
                                        arranged by the PEI, the Student and the PEI may refer the dispute to the CPE
                                        Mediation-Arbitration Scheme (www.cpe.gov.sg).
                                        <br><b>3.4 </b>All information given by the Student to the PEI will not be given
                                        by the PEI to anyone else, unless the Student signs in writing that he agrees or
                                        unless the PEI is allowed to give the information by law.
                                        <br><b>3.5 </b>there is any other agreement between the PEI and the Student that
                                        is different from the terms in this Contract, then the terms in this Contract
                                        will apply.
                                        <br><b>3.6 </b>the Student or the PEI does not exercise or delay exercising any
                                        right granted by this Contract, the Student and the PEI will still be able to
                                        exercise the same type of right under this Contract during the rest of the time
                                        the Contract continues.
                                        <br><b>3.7 </b>this Contract is also signed or translated in any language other
                                        than English and there is a difference from the English language copy of this
                                        Contract, the English language copy will apply.
                                    </li>
                                </ol>
                            </div>
                        </div>

                    <?php if($progName == 'IGCSE') { ?>
                        <div class="row">
                            <div class="col-12 text-center mt-5">
                                <h3>SCHEDULE A</h3>
                            </div>
                            <div class="col-12 text-center">
                                <h3>COURSE DETAILS</h3>
                            </div>
                            <div class="col-12 font-italic">Note: The information provided below should be the same as
                                that submitted to the CPE. </div>
                            <div class="col-12">
                                <table border="1" class="table">
                                    <tbody>
                                        <tr>
                                            <td>1) Course Title</td>
                                            <td><?php echo $className;?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2) Course Duration (in months)</td>
                                            <td>12
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3) Full-time or Part-time Course</td>
                                            <td>Full Time
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4) Course Commencement Date</td>
                                            <td>5 April 2021
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>5) Course Completion Date</td>
                                            <td>31 March 2022
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>6) Date of Commencement of Studies if later than Course Commencement
                                                Date<p><em><span style="font-weight: 400;">Note: &ldquo;N.A.&rdquo; if
                                                            both dates are the same&nbsp;</span></em></p>
                                            </td>
                                            <td>N.A.</td>
                                        </tr>
                                        <tr>
                                            <td>7) Qualification<p><em><span style="font-weight: 400;">(Name of award to
                                                            be conferred on the Student upon successful Course
                                                            completion)</span></em></p>
                                            </td>
                                            <td>Performance Profile with Promotion to </span><span
                                                    style="font-weight: 400;"><?php echo $nextClassName;?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>8) Organisation which develops the Course</td>
                                            <td>G I G International School
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>9) Organisation which awards/ confers the qualification</td>
                                            <td>G I G International School
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>10) Course entry requirement(s)</td>
                                            <td>As per GIGIS age criteria as on </span><span
                                                    style="font-weight: 400;">5</span><span
                                                    style="font-weight: 400;">th</span><span style="font-weight: 400;">
                                                    April 2021</span></li>
                                                <li style="font-weight: 400;"><span style="font-weight: 400;">Pass the
                                                        preceding year&rsquo;s assessment</span></li>
                                                <li style="font-weight: 400;"><span style="font-weight: 400;">Submitted
                                                        the required documents</span></li>
                                                <li style="font-weight: 400;"><span style="font-weight: 400;">Paid due
                                                        fees
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>11) Course schedule with modules and/or subjects</td>
                                            <td>As per Class/module details stated in the GIGIS Student Handbook.&nbsp;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>12) Scheduled holidays (public and school) and/or semester/term break
                                                for course</td>
                                            <td>As per Class/module details stated in the GIGIS Student Handbook.</td>
                                        </tr>
                                        <tr>
                                            <td>13) Examination and/or other assessment period</td>
                                            <td>Continuous Comprehensive Evaluation with last assessment in </span><span
                                                    style="font-weight: 400;">March 2022
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>14) Expected examination results release date</td>
                                            <td>March 2022
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>15) Expected award conferment date</td>
                                            <td>March 2022
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-12 text-center mt-5">
                                <h3>SCHEDULE B</h3>
                            </div>
                            <div class="col-12 text-center">
                                <h3>COURSE FEES</h3>
                            </div>
                            <div class="col-12 my-3">
                                <table class="table" border="1">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <p><strong>Fees Breakdown</strong></p>
                                            </td>
                                            <td>
                                                Fees Payable
                                                (without GST)
                                                <p><strong>(S$)</strong></p>
                                            </td>
                                            <td>
                                                Sibling Subsidy*
                                                (without GST)
                                                <p><strong>(S$)</strong></p>
                                            </td>
                                            <td>
                                                Total Fees
                                                (with GST)
                                                <p><strong>(S$)</strong></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <span class="text-danger">Note: show full breakdown of total payable
                                                    course fees on a monthly basis</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><br />
                                                Tuition Fee&nbsp;
                                                <br />
                                                Activity Fee &amp; School Events
                                                <br />
                                                Student Welfare Fee
                                                <br />
                                                Resource Fee
                                                <br />
                                                Technology Fee
                                            </td>
                                            <td><br />
                                                $800.00
                                                <br />
                                                $54.00
                                                <br />
                                                $25.00
                                                <br />
                                                $135.00
                                                <br />
                                                $130.00
                                            </td>
                                            <td><br />
                                                $0.00
                                                <br />
                                                $0.00
                                                <br />
                                                $0.00
                                                <br />
                                                $0.00
                                                <br />
                                                $0.00
                                            </td>
                                            <td><br />
                                                $856.00
                                                <br />
                                                $57.78
                                                <br />
                                                $26.75
                                                <br />
                                                $144.45
                                                <br />
                                                $139.10
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p><strong>Total Course Fees Payable: Per month</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$1144.00</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$0.00</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$1224.08</strong></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                No. of Instalment/s
                                            </td>
                                            <td colspan="3">
                                                6
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="col-12 my-3 font-italic">*Sibling Subsidy 5% on Tuition Fees</div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 text-center my-3">
                                <h3><u>INSTALMENT SCHEDULE</u></h3>
                            </div>
                            <div class="col-12 my-3">
                                <table class="table" border="1">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <h3><strong>Instalment</strong><strong>1</strong><strong>
                                                        Schedule</strong></h3>
                                            </td>
                                            <td>
                                                <strong>Amount (with GST) (S$)</strong>
                                            </td>
                                            <td>
                                                <p><strong>Date Due</strong><strong>2</strong></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>1st Instalment (April &amp; May 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2448.16</p>
                                            </td>
                                            <td>
                                                <p>05/03/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>2nd Instalment (June &amp; July 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2448.16</p>
                                            </td>
                                            <td>
                                                <p>05/05/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>3rd Instalment (Aug &amp; Sep 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2448.16</p>
                                            </td>
                                            <td>
                                                <p>05/07/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>4th Instalment (Oct &amp; Nov 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2448.16</p>
                                            </td>
                                            <td>
                                                <p>05/09/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>5th Instalment (Dec&rsquo;21 &amp; Jan&rsquo;22)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2448.16</p>
                                            </td>
                                            <td>
                                                <p>05/11/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>6th Instalment (Feb &amp; Mar 2022)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2448.16</p>
                                            </td>
                                            <td>
                                                <p>05/01/2022</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p><strong>Total Course Fees Payable:</strong></p>
                                            </td>
                                            <td>
                                                <p><strong>$14,688.96</strong></p>
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-12 my-3">
                                <ol>
                                    <li>Each instalment amount <u>shall not exceed</u> the following:</li>
                                </ol>
                                <ul>
                                    <li><s>12 months&rsquo; worth of fees for EduTrust certified PEIs*; or</li>
                                    <li>6 months&rsquo; worth of fees for non-EduTrust-certified PEIs with Industry-Wide
                                        Course Fee Insurance Scheme (IWC)*; or</s></li>
                                    <li>2 months&rsquo; worth of fees for non-EduTrust-certified PEIs without IWC*.</li>
                                </ul>
                                <p>*<em>Delete as appropriate by striking through.</em></p>
                                <ol start="2">
                                    <li>Each instalment after the first shall be collected within one week before the
                                        next payment scheduled.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mt-5 text-center">
                                <h3>SCHEDULE B<br />
                                    COURSE FEES (SIBILING DISCOUNT)
                                </h3>
                            </div>
                            <div class="col-12">
                                <table class="table" border="1">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <strong>Fees Breakdown</strong>
                                            </td>
                                            <td>
                                                Fees Payable (without GST) <strong>(S$)</strong>
                                            </td>
                                            <td>
                                                Sibling Subsidy* (without GST) <strong>(S$)</strong>
                                            </td>
                                            <td>
                                                Total Fees (with GST) <strong>(S$)</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-danger font-italic">
                                                Note: show full breakdown of total payable course fees on a monthly
                                                basis
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><br />
                                                <p>Tuition Fee&nbsp;</p>
                                                <br />
                                                <p>Activity Fee &amp; School Events</p>
                                                <br />
                                                <p>Student Welfare Fee</p>
                                                <br />
                                                <p>Resource Fee</p>
                                                <br />
                                                <p>Technology Fee</p>
                                            </td>
                                            <td><br />
                                                <p>$800.00</p>
                                                <br />
                                                <p>$54.00</p>
                                                <br /><br />
                                                <p>$25.00</p>
                                                <br />
                                                <p>$135.00</p>
                                                <br />
                                                <p>$130.00</p>
                                            </td>
                                            <td><br />
                                                <p>$40.00</p>
                                                <br />
                                                <p>$0.00</p>
                                                <br /><br />
                                                <p>$0.00</p>
                                                <br />
                                                <p>$0.00</p>
                                                <br />
                                                <p>$0.00</p>
                                            </td>
                                            <td><br />
                                                <p>$813.20</p>
                                                <br />
                                                <p>$57.78</p>
                                                <br /><br />
                                                <p>$26.75</p>
                                                <br />
                                                <p>$144.45</p>
                                                <br />
                                                <p>$139.10</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p><strong>Total Course Fees Payable: Per month</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$1144.00</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$40.00</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$1181.28</strong></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p>No. of Instalment/s</p>
                                            </td>
                                            <td colspan="3">
                                                <p>6</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p class="font-italic">*Sibling Subsidy 5% on Tuition Fees</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mt-5 text-center">
                                <h3>
                                    <u>INSTALMENT SCHEDULE</u>
                                </h3>
                            </div>
                            <div class="col-12 my-3">
                                <table class="table" border="1">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <h3><strong>Instalment</strong><strong>1</strong><strong>
                                                        Schedule</strong></h3>
                                            </td>
                                            <td>
                                                <strong>Amount (with GST) (S$)</strong>
                                            </td>
                                            <td>
                                                <strong>Date Due 2</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>1st Instalment (April &amp; May 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2362.56</p>
                                            </td>
                                            <td>
                                                <p>05/03/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>2nd Instalment (June &amp; July 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2362.56</p>
                                            </td>
                                            <td>
                                                <p>05/05/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>3rd Instalment (Aug &amp; Sep 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2362.56</p>
                                            </td>
                                            <td>
                                                <p>05/07/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>4th Instalment (Oct &amp; Nov 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2362.56</p>
                                            </td>
                                            <td>
                                                <p>05/09/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>5th Instalment (Dec&rsquo;21 &amp; Jan&rsquo;22)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2362.56</p>
                                            </td>
                                            <td>
                                                <p>05/11/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>6th Instalment (Feb &amp; Mar 2022)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2362.56</p>
                                            </td>
                                            <td>
                                                <p>05/01/2022</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p><strong>Total Course Fees Payable:</strong></p>
                                            </td>
                                            <td>
                                                <p><strong>$14175.36</strong></p>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-12 my-3">
                                <ol>
                                    <li>Each instalment amount shall not exceed the following:</li>
                                </ol>
                                <ul>
                                    <li><s>12 months&rsquo; worth of fees for EduTrust certified PEIs*; or</li>
                                    <li>6 months&rsquo; worth of fees for non-EduTrust-certified PEIs with Industry-Wide
                                        Course Fee Insurance Scheme (IWC)*; or</li>
                                    <li></s>2 months&rsquo; worth of fees for non-EduTrust-certified PEIs without IWC*.
                                    </li>
                                </ul>
                                <p>*<em>Delete as appropriate by striking through.</em></p>
                                <ol start="2">
                                    <li>Each instalment after the first shall be collected within one week before the
                                        next payment scheduled.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mt-5 text-center">
                                <h3>SCHEDULE C<br>
                                    <u>MISCELLANEOUS FEES</u>
                                </h3>
                            </div>
                            <div class="col-12 my-3">
                                <table class="table" border="1">
                                    <tbody>
                                        <tr>
                                            <td style='width:20px;'>&nbsp;</td>
                                            <td>
                                                <strong>Purpose of Fee</strong>
                                            </td>
                                            <td>
                                                Amount (without GST) <strong>(S$)&nbsp;</strong>
                                            </td>
                                            <td>
                                                Amount (with GST) <strong>(S$)</strong>
                                            </td>
                                            <td>
                                                Frequency
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">Examples include late payment fees, replacement of student
                                                ID, re-taking examinations</td>
                                        </tr>
                                        <tr>
                                            <td>1</td>
                                            <td>Bus Zone I (up to 4km)</td>
                                            <td>$225.00</td>
                                            <td>$240.75</td>
                                            <td>Per month</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Bus Zone I (above 4km to 8km)</td>
                                            <td>$245.00</td>
                                            <td>$262.15</td>
                                            <td>Per month</td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Bus Zone III (above 8km to 10km)</td>
                                            <td>$265.00</td>
                                            <td>$283.55</td>
                                            <td>Per month</td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Bus Zone IV (above 10km)</td>
                                            <td>$296.00</td>
                                            <td>$316.72</td>
                                            <td>Per month</td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td>Club Fees</td>
                                            <td>$300.00</td>
                                            <td>$321.00</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>6</td>
                                            <td>Event Costumes</td>
                                            <td> $75.00</td>
                                            <td>$80.25</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>7</td>
                                            <td>Camps & Workshops Activities</td>
                                            <td>$150.00</td>
                                            <td>$160.50</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>8</td>
                                            <td>Class/Graduation Photo</td>
                                            <td>$10.00</td>
                                            <td>$10.70</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>9</td>
                                            <td>Annual Day CD</td>
                                            <td>$10.00</td>
                                            <td>$10.70</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>10</td>
                                            <td>Competition & Cultural Activities Fee</td>
                                            <td>$100.00</td>
                                            <td>$107.00</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>11</td>
                                            <td>UNSW/ASSET/Olympiad/NTSE(2018-19)</td>
                                            <td>$150.00</td>
                                            <td>$160.50</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>12</td>
                                            <td>Online programme</td>
                                            <td>$80.00</td>
                                            <td>$85.60</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>13</td>
                                            <td>Check points</td>
                                            <td>$250.00</td>
                                            <td>$267.50</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>14</td>
                                            <td>Duplicate ID card</td>
                                            <td>$20.00</td>
                                            <td>$21.40</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>15</td>
                                            <td>Books and Note books</td>
                                            <td>$600.00</td>
                                            <td>$642.00</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>16</td>
                                            <td>Late Payment Charges</td>
                                            <td>$100.00</td>
                                            <td>$107.00</td>
                                            <td>Per instance</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <ol start="3">
                                    <li>Miscellaneous Fees refer to any non-compulsory fees which the students pay only
                                        when applicable. Such fees are normally collected by the PEI when the need
                                        arises</li>
                                </ol>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="row">
                            <div class="col-12 text-center mt-5">
                                <h3>SCHEDULE A</h3>
                            </div>
                            <div class="col-12 text-center">
                                <h3>COURSE DETAILS</h3>
                            </div>
                            <div class="col-12 font-italic">Note: The information provided below should be the same as
                                that submitted to the CPE. </div>
                            <div class="col-12">
                                <table border="1" class="table">
                                    <tbody>
                                        <tr>
                                            <td>1) Course Title</td>
                                            <td><?php echo $className;?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2) Course Duration (in months)</td>
                                            <td>12
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3) Full-time or Part-time Course</td>
                                            <td>Full Time
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4) Course Commencement Date</td>
                                            <td>5 April 2021
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>5) Course Completion Date</td>
                                            <td>31 March 2022
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>6) Date of Commencement of Studies if later than Course Commencement
                                                Date<p><em><span style="font-weight: 400;">Note: &ldquo;N.A.&rdquo; if
                                                            both dates are the same&nbsp;</span></em></p>
                                            </td>
                                            <td>N.A.</td>
                                        </tr>
                                        <tr>
                                            <td>7) Qualification<p><em><span style="font-weight: 400;">(Name of award to
                                                            be conferred on the Student upon successful Course
                                                            completion)</span></em></p>
                                            </td>
                                            <td>Performance Profile with Promotion to </span><span
                                                    style="font-weight: 400;"><?php echo $nextClassName;?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>8) Organisation which develops the Course</td>
                                            <td>G I G International School
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>9) Organisation which awards/ confers the qualification</td>
                                            <td>G I G International School
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>10) Course entry requirement(s)</td>
                                            <td>As per GIGIS age criteria as on </span><span
                                                    style="font-weight: 400;">5</span><span
                                                    style="font-weight: 400;">th</span><span style="font-weight: 400;">
                                                    April 2021</span></li>
                                                <li style="font-weight: 400;"><span style="font-weight: 400;">Pass the
                                                        preceding year&rsquo;s assessment</span></li>
                                                <li style="font-weight: 400;"><span style="font-weight: 400;">Submitted
                                                        the required documents</span></li>
                                                <li style="font-weight: 400;"><span style="font-weight: 400;">Paid due
                                                        fees
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>11) Course schedule with modules and/or subjects</td>
                                            <td>As per Class/module details stated in the GIGIS Student Handbook.&nbsp;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>12) Scheduled holidays (public and school) and/or semester/term break
                                                for course</td>
                                            <td>As per Class/module details stated in the GIGIS Student Handbook.</td>
                                        </tr>
                                        <tr>
                                            <td>13) Examination and/or other assessment period</td>
                                            <td>Continuous Comprehensive Evaluation with last assessment in </span><span
                                                    style="font-weight: 400;">March 2022
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>14) Expected examination results release date</td>
                                            <td>March 2022
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>15) Expected award conferment date</td>
                                            <td>March 2022
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-12 text-center mt-5">
                                <h3>SCHEDULE B</h3>
                            </div>
                            <div class="col-12 text-center">
                                <h3>COURSE FEES</h3>
                            </div>
                            <div class="col-12 my-3">
                                <table class="table" border="1">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <p><strong>Fees Breakdown</strong></p>
                                            </td>
                                            <td>
                                                Fees Payable
                                                (without GST)
                                                <p><strong>(S$)</strong></p>
                                            </td>
                                            <td>
                                                Sibling Subsidy*
                                                (without GST)
                                                <p><strong>(S$)</strong></p>
                                            </td>
                                            <td>
                                                Total Fees
                                                (with GST)
                                                <p><strong>(S$)</strong></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <span class="text-danger">Note: show full breakdown of total payable
                                                    course fees on a monthly basis</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><br />
                                                Tuition Fee&nbsp;
                                                <br />
                                                Activity Fee &amp; School Events
                                                <br />
                                                Student Welfare Fee
                                                <br />
                                                Resource Fee
                                                <br />
                                                Technology Fee
                                            </td>
                                            <td><br />
                                                $700.00
                                                <br />
                                                $50.00
                                                <br />
                                                $25.00
                                                <br />
                                                $135.00
                                                <br />
                                                $130.00
                                            </td>
                                            <td><br />
                                                $0.00
                                                <br />
                                                $0.00
                                                <br />
                                                $0.00
                                                <br />
                                                $0.00
                                                <br />
                                                $0.00
                                            </td>
                                            <td><br />
                                                $749.00
                                                <br />
                                                $53.50
                                                <br />
                                                $26.75
                                                <br />
                                                $144.45
                                                <br />
                                                $139.10
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p><strong>Total Course Fees Payable: Per month</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$1040.00</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$0.00</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$1112.80</strong></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                No. of Instalment/s
                                            </td>
                                            <td colspan="3">
                                                6
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="col-12 my-3 font-italic">*Sibling Subsidy 5% on Tuition Fees</div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 text-center my-3">
                                <h3><u>INSTALMENT SCHEDULE</u></h3>
                            </div>
                            <div class="col-12 my-3">
                                <table class="table" border="1">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <h3><strong>Instalment</strong><strong>1</strong><strong>
                                                        Schedule</strong></h3>
                                            </td>
                                            <td>
                                                <strong>Amount (with GST) (S$)</strong>
                                            </td>
                                            <td>
                                                <p><strong>Date Due</strong><strong>2</strong></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>1st Instalment (April &amp; May 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2225.60</p>
                                            </td>
                                            <td>
                                                <p>05/03/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>2nd Instalment (June &amp; July 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2225.60</p>
                                            </td>
                                            <td>
                                                <p>05/05/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>3rd Instalment (Aug &amp; Sep 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2225.60</p>
                                            </td>
                                            <td>
                                                <p>05/07/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>4th Instalment (Oct &amp; Nov 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2225.60</p>
                                            </td>
                                            <td>
                                                <p>05/09/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>5th Instalment (Dec&rsquo;21 &amp; Jan&rsquo;22)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2225.60</p>
                                            </td>
                                            <td>
                                                <p>05/11/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>6th Instalment (Feb &amp; Mar 2022)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2225.60</p>
                                            </td>
                                            <td>
                                                <p>05/01/2022</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p><strong>Total Course Fees Payable:</strong></p>
                                            </td>
                                            <td>
                                                <p><strong>$13353.60</strong></p>
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-12 my-3">
                                <ol>
                                    <li>Each instalment amount <u>shall not exceed</u> the following:</li>
                                </ol>
                                <ul>
                                    <li><s>12 months&rsquo; worth of fees for EduTrust certified PEIs*; or</li>
                                    <li>6 months&rsquo; worth of fees for non-EduTrust-certified PEIs with Industry-Wide
                                        Course Fee Insurance Scheme (IWC)*; or</s></li>
                                    <li>2 months&rsquo; worth of fees for non-EduTrust-certified PEIs without IWC*.</li>
                                </ul>
                                <p>*<em>Delete as appropriate by striking through.</em></p>
                                <ol start="2">
                                    <li>Each instalment after the first shall be collected within one week before the
                                        next payment scheduled.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mt-5 text-center">
                                <h3>SCHEDULE B<br />
                                    COURSE FEES (SIBILING DISCOUNT)
                                </h3>
                            </div>
                            <div class="col-12">
                                <table class="table" border="1">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <strong>Fees Breakdown</strong>
                                            </td>
                                            <td>
                                                Fees Payable (without GST) <strong>(S$)</strong>
                                            </td>
                                            <td>
                                                Sibling Subsidy* (without GST) <strong>(S$)</strong>
                                            </td>
                                            <td>
                                                Total Fees (with GST) <strong>(S$)</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-danger font-italic">
                                                Note: show full breakdown of total payable course fees on a monthly
                                                basis
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><br />
                                                <p>Tuition Fee&nbsp;</p>
                                                <br />
                                                <p>Activity Fee &amp; School Events</p>
                                                <br />
                                                <p>Student Welfare Fee</p>
                                                <br />
                                                <p>Resource Fee</p>
                                                <br />
                                                <p>Technology Fee</p>
                                            </td>
                                            <td><br />
                                                <p>$700.00</p>
                                                <br />
                                                <p>$50.00</p>
                                                <br /><br />
                                                <p>$25.00</p>
                                                <br />
                                                <p>$135.00</p>
                                                <br />
                                                <p>$130.00</p>
                                            </td>
                                            <td><br />
                                                <p>$35.00</p>
                                                <br />
                                                <p>$0.00</p>
                                                <br /><br />
                                                <p>$0.00</p>
                                                <br />
                                                <p>$0.00</p>
                                                <br />
                                                <p>$0.00</p>
                                            </td>
                                            <td><br />
                                                <p>$711.55</p>
                                                <br />
                                                <p>$53.50</p>
                                                <br /><br />
                                                <p>$26.75</p>
                                                <br />
                                                <p>$144.45</p>
                                                <br />
                                                <p>$139.10</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p><strong>Total Course Fees Payable: Per month</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$1040.00</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$35.00</strong></p>
                                            </td>
                                            <td><br />
                                                <p><strong>$1075.35</strong></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p>No. of Instalment/s</p>
                                            </td>
                                            <td colspan="3">
                                                <p>6</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p class="font-italic">*Sibling Subsidy 5% on Tuition Fees</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mt-5 text-center">
                                <h3>
                                    <u>INSTALMENT SCHEDULE</u>
                                </h3>
                            </div>
                            <div class="col-12 my-3">
                                <table class="table" border="1">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <h3><strong>Instalment</strong><strong>1</strong><strong>
                                                        Schedule</strong></h3>
                                            </td>
                                            <td>
                                                <strong>Amount (with GST) (S$)</strong>
                                            </td>
                                            <td>
                                                <strong>Date Due 2</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>1st Instalment (April &amp; May 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2150.70</p>
                                            </td>
                                            <td>
                                                <p>05/03/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>2nd Instalment (June &amp; July 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2150.70</p>
                                            </td>
                                            <td>
                                                <p>05/05/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>3rd Instalment (Aug &amp; Sep 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2150.70</p>
                                            </td>
                                            <td>
                                                <p>05/07/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>4th Instalment (Oct &amp; Nov 2021)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2150.70</p>
                                            </td>
                                            <td>
                                                <p>05/09/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>5th Instalment (Dec&rsquo;21 &amp; Jan&rsquo;22)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2150.70</p>
                                            </td>
                                            <td>
                                                <p>05/11/2021</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <ul>
                                                    <li>6th Instalment (Feb &amp; Mar 2022)</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <p>$2150.70</p>
                                            </td>
                                            <td>
                                                <p>05/01/2022</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p><strong>Total Course Fees Payable:</strong></p>
                                            </td>
                                            <td>
                                                <p><strong>$12904.20</strong></p>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-12 my-3">
                                <ol>
                                    <li>Each instalment amount shall not exceed the following:</li>
                                </ol>
                                <ul>
                                    <li><s>12 months&rsquo; worth of fees for EduTrust certified PEIs*; or</li>
                                    <li>6 months&rsquo; worth of fees for non-EduTrust-certified PEIs with Industry-Wide
                                        Course Fee Insurance Scheme (IWC)*; or</li>
                                    <li></s>2 months&rsquo; worth of fees for non-EduTrust-certified PEIs without IWC*.
                                    </li>
                                </ul>
                                <p>*<em>Delete as appropriate by striking through.</em></p>
                                <ol start="2">
                                    <li>Each instalment after the first shall be collected within one week before the
                                        next payment scheduled.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mt-5 text-center">
                                <h3>SCHEDULE C<br>
                                    <u>MISCELLANEOUS FEES</u>
                                </h3>
                            </div>
                            <div class="col-12 my-3">
                                <table class="table" border="1">
                                    <tbody>
                                        <tr>
                                            <td style='width:20px;'>&nbsp;</td>
                                            <td>
                                                <strong>Purpose of Fee</strong>
                                            </td>
                                            <td>
                                                Amount (without GST) <strong>(S$)&nbsp;</strong>
                                            </td>
                                            <td>
                                                Amount (with GST) <strong>(S$)</strong>
                                            </td>
                                            <td>
                                                Frequency
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">Examples include late payment fees, replacement of student
                                                ID, re-taking examinations</td>
                                        </tr>
                                        <tr>
                                            <td>1</td>
                                            <td>Bus Zone I (up to 4km)</td>
                                            <td>$225.00</td>
                                            <td>$240.75</td>
                                            <td>Per month</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Bus Zone I (above 4km to 8km)</td>
                                            <td>$245.00</td>
                                            <td>$262.15</td>
                                            <td>Per month</td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Bus Zone III (above 8km to 10km)</td>
                                            <td>$265.00</td>
                                            <td>$283.55</td>
                                            <td>Per month</td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Bus Zone IV (above 10km)</td>
                                            <td>$296.00</td>
                                            <td>$316.72</td>
                                            <td>Per month</td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td>Club Fees</td>
                                            <td>$300.00</td>
                                            <td>$321.00</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>6</td>
                                            <td>Event Costumes</td>
                                            <td> $75.00</td>
                                            <td>$80.25</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>7</td>
                                            <td>Camps & Workshops Activities</td>
                                            <td>$150.00</td>
                                            <td>$160.50</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>8</td>
                                            <td>Class/Graduation Photo</td>
                                            <td>$10.00</td>
                                            <td>$10.70</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>9</td>
                                            <td>Annual Day CD</td>
                                            <td>$10.00</td>
                                            <td>$10.70</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>10</td>
                                            <td>Competition & Cultural Activities Fee</td>
                                            <td>$100.00</td>
                                            <td>$107.00</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>11</td>
                                            <td>UNSW/ASSET/Olympiad/NTSE(2018-19)</td>
                                            <td>$150.00</td>
                                            <td>$160.50</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>12</td>
                                            <td>Online programme</td>
                                            <td>$80.00</td>
                                            <td>$85.60</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>13</td>
                                            <td>Check points</td>
                                            <td>$250.00</td>
                                            <td>$267.50</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>14</td>
                                            <td>Duplicate ID card</td>
                                            <td>$20.00</td>
                                            <td>$21.40</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>15</td>
                                            <td>Books and Note books</td>
                                            <td>$400.00</td>
                                            <td>$428.00</td>
                                            <td>Per instance</td>
                                        </tr>
                                        <tr>
                                            <td>16</td>
                                            <td>Late Payment Charges</td>
                                            <td>$100.00</td>
                                            <td>$107.00</td>
                                            <td>Per instance</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <ol start="3">
                                    <li>Miscellaneous Fees refer to any non-compulsory fees which the students pay only
                                        when applicable. Such fees are normally collected by the PEI when the need
                                        arises</li>
                                </ol>
                            </div>
                        </div>
                    <?php } ?>
                        <div class="row">
                            <div class="col-12 mt-5 text-center">
                                <h3>SCHEDULE D<br>
                                    <u>REFUND TABLE</u>
                                </h3>
                            </div>
                            <div class="col-12 my-3">
                                <table class="table" border="1">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <p>% of [the amount of fees paid under Schedules B and C]</p>
                                            </td>
                                            <td>
                                                <h3><br />If Student&rsquo;s written notice of withdrawal is received:
                                                </h3>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p>[75%]</p>
                                            </td>
                                            <td>
                                                <p>more than [30] days before the Course Commencement Date</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p>[50%]</p>
                                            </td>
                                            <td>
                                                <p>before, but not more than [7] days before the Course Commencement
                                                    Date</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p>[0%]</p>
                                            </td>
                                            <td>
                                                <p>after, but not more than [7] days after the Course Commencement Date
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p>[0%]</p>
                                            </td>
                                            <td>
                                                <p>more than [7] days after the Course Commencement Date</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-12">
                                <label class="form-check">
                                    <input readonly class="form-check-input" type="checkbox" checked="">
                                    <span class="form-check-label">The parties hereby acknowledge and agree to the terms
                                        stated in this Contract.
                                    </span>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-12 mt-3">
                                <p>SIGNED by the PEI</p>
                                <p>For:</p>
                                <p>
                                    <img src='assets/img/gigis_signature.png'></img><br/>
                                </p>
                                <p>__________________________________________________________________</p>
                                <p>Authorised Signatory of the PEI</p>
                                <p>Name: Mr BK Arun</p>
                                <p>Date: 26/1/2021</p>
                            </div>
                            <div class="col-md-6 col-sm-12 mt-3">
                            <p>&nbsp;</p>
                                <p>&nbsp;</p>
                                <p>
                                    <img src='assets/img/gigis_seal.png' style='height:90px;'></img>
                                </p>
                                <p>__________________________________________________________________</p>
                                <p>Seal of PEI</p>
                            </div>
                        </div>
                        <!--
                        <div class="row">
                            <div class="col-md-6 col-sm-12 my-3">
                                <input readonly type="file" class="form-control" name="student_sign" id="student_sign">
                                <div class=''>SIGNED by the Student</div>
                            </div>
                            <div class="col-md-6 col-sm-12 my-3">
                                <input readonly type="file" class="form-control" name="parent_sign" id="parent_sign">
                                <div class=''>SIGNED by the Student’s parent or legal guardian (if the student is under eighteen (18) years of age</div>
                            </div>
                        </div>
                        -->
                        <div class="row">
                            <div class="col-md-4 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="dependant_pass" id="dependant_pass" value="<?=$dt["student_name"];?>">
                                <div class=''>Name of Child</div>

                            </div>
                            <div class="col-md-4 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="parent_legal_name" id="parent_legal_name" value="<?=$dt["student_immigration_status"];?>">
                                <div class=''>Immigration type</div>
                            </div>
                            <div class="col-md-4 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="parent_legal_name" id="parent_legal_name" value="<?=$dt["parent_name"];?>">
                                <div class=''>Name of Parent/ Legal Guardian:</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12 my-3">
                                <div class="row">
                                    <div class="col-auto">Date</div>
                                    <div class="col"><input readonly type='text' class="form-control" name="sign_date" id="sign_date" value="<?=date("m/d/Y");?>"></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 my-3">
                                <div class="row">
                                    <div class="col-auto">Date</div>
                                    <div class="col"><input readonly type='text' class="form-control" name="sign_date" id="sign_date" value="<?=date("m/d/Y");?>"></div>
                                </div>
                            </div>
                        </div>

                        <!-------PART B – Declaration for Re-enrolment, Citizenship and Immigration--------->
                        <div class="row">
                            <div class="col-12 mt-5 text-center">
                                <h2><u>PART B – Declaration for Re-enrolment, Citizenship and Immigration</u></h2>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-auto">
                                I / We
                            </div>
                            <div class="col">
                                <div>
                                    <input readonly type="text" class="form-control" name="bparent_name" id="bparent_name" value="<?=$dt["parent_name"];?>">
                                </div>
                                <div class="mt-1 font-italic text-center">(Name of Parent/ Guardian)</div>
                                <div class="mt-2">
                                    <input readonly type="text" class="form-control" name="bstudent_name" id="bstudent_name" value="<?=$dt["student_name"];?>">
                                </div>
                                <div class="font-italic text-center">(Name of Child)</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <p>enrolled in <strong>G I G International School Pte Ltd</strong> hereby state that:
                                </p>
                                <ol>
                                    <li>I / We have read and understood the guidelines for Singapore Citizens issued by
                                        the Ministry of Education (the &ldquo;MOE&rdquo;) which are as follows:

                                        <p>Admission of Singapore Citizen or Foreign Students and Singapore Permanent
                                            Residents who attain Singapore Citizenship</p>
                                        <p>Singapore Citizens are exempted from seeking MOE&rsquo;s approval to study at
                                            the foreign system school&rsquo;s Kindergarten / Pre-School.</p>
                                        <p>Singapore Citizens who are enrolled in Kindergarten / Pre-School are not
                                            guaranteed continue admission to Grade / Standard / Class 1 and are required
                                            to seek prior MOE approval for continuing education in GIGIS.&nbsp;</p>
                                        <p><strong>Singapore Citizens below 6 years:</strong></p>
                                        <p>All Singapore Citizens who will be age appropriate for the MOE School Primary
                                            1 registration exercise should participate in the P1 registration process.
                                            This is to ensure that these children continue their education in the
                                            national schools, if their application to study at GIGIS is not approved by
                                            MOE.&nbsp;</p>
                                        <p>All Singapore Citizen children, who are age appropriate for MOE School
                                            Primary 1 and have not obtained MOE approval to continue their study in
                                            GIGIS will be required to study in the MOE schools no later than the start
                                            of the respective MOE Primary Year 1 academic year.&nbsp;</p>
                                        <p><strong>Singapore Citizens above 6 years:</strong></p>
                                        <p>Parents of GIGIS students who become Singapore Citizen after they have
                                            achieved 6 years age, are exempted from MOE approval for continuing their
                                            education in GIGIS. However, parents of such students are required to comply
                                            with the requirements specified in Point 4 below and seek a written approval
                                            from the GIGIS Admissions Department confirming the promotion / re-admission
                                            in the next grade.&nbsp;</p>
                                    </li>
                                    <li>
                                        I / We understand that, <strong>G I G International School Pte Ltd</strong> has
                                        granted admission to _ <?php echo $className;?>_ subject to the following Terms and
                                        Conditions.
                                        <ol type="i">
                                            <li>That my child holds a non-Singapore Citizenship OR is a Singapore
                                                Citizen authorised by the Ministry of Education as per clause
                                                &ldquo;1&rdquo; above, to study at GIGIS and&nbsp;</li>

                                            <li>That my child is eligible for a promotion to next grade and / or
                                                satisfies the re-enrolment criteria as established by the school,
                                                and&nbsp;</li>

                                            <li>That I / We have read the School Terms and Conditions, School Policies
                                                and the Student Handbook and that I / We agree to comply, and shall
                                                ensure that my / our child also complies with the afore-mentioned terms,
                                                and&nbsp;</li>

                                            <li>That my child has not violated laws of any country or is facing any
                                                trial in any court or is convicted of any offence(s).</li>
                                        </ol>
                                        <br><br />
                                    </li>
                                    <li>
                                        As on the date of signing this document, I/ We declare that
                                        <div>
                                            <input readonly type="text" class="form-control" name="child_name" id="child_name" value="<?=$dt["student_name"];?>">
                                        </div>
                                        <div class="font-italic text-center">(Name of Child)</div>
                                        <div>has following nationality and immigration details:</div>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 my-3">Nationality:</div>
                                            <div class="col-md-6 col-sm-12 my-3">
                                                <input readonly type="text" class="form-control" name="bnationality"
                                                    id="bnationality" value="<?=$dt["student_nationality"];?>">
                                            </div>
                                            <div class="col-md-6 col-sm-12 my-3">Passport Number:</div>
                                            <div class="col-md-6 col-sm-12 my-3">
                                                <input readonly type="text" class="form-control" name="bpassport_number"
                                                    id="bpassport_number" value="<?=$dt["student_passport_no"];?>">
                                            </div>
                                            <div class="col-md-6 col-sm-12 my-3">Issue Date:</div>
                                            <div class="col-md-6 col-sm-12 my-3">
                                                <input readonly type='text' class="form-control" name="bissue_date"
                                                    id="bissue_date" value="">
                                            </div>
                                            <div class="col-md-6 col-sm-12 my-3">Immigration Status:</div>
                                            <div class="col-md-6 col-sm-12 my-3">
                                                <input readonly type='text' class="form-control" name="bimmigration_status"
                                                    id="bimmigration_status" value="<?=$dt["student_immigration_status"];?>">
                                            </div>
                                            <div class="col-md-6 col-sm-12 my-3">NRIC / FIN Number:</div>
                                            <div class="col-md-6 col-sm-12 my-3">
                                                <input readonly type='text' class="form-control" name="bfin_number"
                                                    id="bfin_number" value="<?=$dt["student_nric_no"];?>">
                                            </div>
                                        </div>
                                        <br /><br />
                                    </li>
                                    <li>
                                        I / We agree that at any time our child is granted and accepts a Singapore
                                        Citizenship, then within 7 (seven) working days from date of such acceptance, I
                                        / We agree to do the following:
                                        <ol type="i">
                                            <li>Apply for MOE approval through the GIGIS Admissions Department,
                                                applicability to be determined as per Clause 1, where applicable,&nbsp;
                                            </li>

                                            <li>Update the student particulars in profile and submit the new passport
                                                and immigration documents / Ministry letters if any to school office,
                                                and seek acknowledgment of changed profile by the class teacher,&nbsp;
                                            </li>

                                            <li>Get a written confirmation from the GIGIS Admissions Department (<a
                                                    href="mailto:admissions@gigis.edu.sg">admissions@gigis.edu.sg</a>)
                                                that the child is exempted from MOE approval and the child can continue
                                                his/her education at GIGIS,&nbsp;</li>

                                            <li>Generate a new student e-contract with revised nationality and
                                                immigration status and inform the class teacher by written email if the
                                                student contract could not be generated / completed for any reason(s).
                                            </li>
                                        </ol>
                                        <br /><br />
                                    </li>
                                    <li>
                                        I / We agree and fully understand that in the event we fail to perform and
                                        comply with steps mentioned in Clause “4” above, then it would be deemed as a
                                        breach of School’s Terms and Conditions.
                                        <br /><br />
                                    </li>
                                    <li>I / We represent that all the information and details mentioned in the student
                                        profile of my / our child is correct and valid as on the date of signing of this
                                        Declaration, I / We agree and undertake that I / We shall ensure that the
                                        student profile of my / our child is updated with latest, correct and valid
                                        particulars at all times during the time my / our child is enrolled with GIGIS.
                                        In case any of the particulars of the student profile of my child are changed,
                                        including but not limited to change in the immigration status of my child from
                                        Dependent Pass Holder to Permanent Resident, I shall update the latest details
                                        on the student profile within seven (7) days of such change becoming effective.
                                        <br /><br />
                                    </li>
                                    <li>I / We agree that the school may be asked to share student particulars with the
                                        Ministry of Education or any other relevant authority in case of an enquiry or
                                        as a matter of routine administrative procedure in accordance with the
                                        applicable laws.
                                        <br /><br />
                                    </li>
                                    <li>I / We understand that suppression of facts or furnishing misleading / false
                                        information or failure to provide updated information as required under this
                                        Declaration may result in cancellation / termination of admission from the
                                        School and the School may exercise other rights and seek remedies as may be
                                        available under law at my / our cost and liability.
                                        <br /><br />
                                    </li>
                                    <li>
                                        <div class="row">
                                            <div class="col-auto">
                                                I / We,
                                            </div>
                                            <div class="col">
                                                <input readonly type="text" class="form-control" name="cparent_name"
                                                    id="cparent_name" value="<?=$dt["parent_name"];?>">
                                            </div>
                                            <div class="col-12 text-center font-italic">
                                                (Name of Parent/ Guardian)
                                            </div>
                                            <div class="col-12 mt-2">
                                                certify that the information provided in this declaration is true and
                                                complete.
                                            </div>
                                        </div>
                                        <!--
                                        <div class="row">
                                            <div class="col-auto my-3">Signed by (Parent/ Guardian)</div>
                                            <div class="col my-3">
                                                <input readonly type="file" class="form-control" name="cparent_sign" id="cparent_sign">
                                            </div>
                                            <div class="col-auto my-3">
                                                on
                                            </div>
                                            <div class="col my-3">
                                                <input readonly type='text' class="form-control" name="cparent_sign_date" id="cparent_sign_date">
                                            </div>
                                        </div>
                                        -->
                            </div>
                            <br /><br />
                            </li>

                            </ol>
                        </div>

                        <?php
                            if($priority_contact=="father"){
                        ?>
                        <div class="row">
                            <div class="col-md-4 col-sm-12 my-3 text-right">
                                Father's Name:
                            </div>
                            <div class="col-md-8 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="father_name" id="father_name" value="<?=$dt["father_name"];?>">
                            </div>
                        </div>
                        <?php
                            }else{
                        ?>

                        <div class="row">
                            <div class="col-md-4 col-sm-12 my-3 text-right">
                                Mother's Name:
                            </div>
                            <div class="col-md-8 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="mother_name" id="mother_name" value="<?=$dt["mother_name"];?>">
                            </div>
                        </div>
                        <?php
                            }
                        ?>

                        <div class="row">
                            <div class="col-md-4 col-sm-12 my-3 text-right">
                                IC No:
                            </div>
                            <div class="col-md-8 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="ic_number" id="ic_number" value="<?=$dt["parent_nric_no"];?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-12 my-3 text-right">
                                Passport Number:
                            </div>
                            <div class="col-md-8 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="dpassport_number" id="dpassport_number" value="<?=$dt["parent_passport_no"];?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 text-center mt-5">
                                <h3><u>LATE COURSE COMMENCEMENT</u></h3>
                            </div>
                            <div class="col-12">
                                <p>I have been informed by the Admissions Team and I am aware that the course I am
                                    enrolling for in G I G International School has commenced prior to my becoming a
                                    student in the school.
                                </p>
                                <p>I acknowledge and agree to be enrolled for the course that has already commenced on
                                    the Course Commencement Date mentioned in the student contract.
                                </p>
                            </div>
                        </div>

                        <!--
                        <div class="row">
                            <div class="col-md-6 col-sm-12 my-3">
                                <input readonly type="file" class="form-control" name="bstudent_sign" id="bstudent_sign">
                                <div>SIGNED by the Student</div>
                            </div>
                            <div class="col-md-6 col-sm-12 my-3">
                                <input readonly type="file" class="form-control" name="bparent_sign" id="bparent_sign">
                                <div>Signed by the Student’s Parent/Legal Guardian</div>
                                <div class='text-center font-italic'>(If student is under eighteen (18) years of age)</div>
                            </div>
                        </div>
                        --->

                        <div class="row">
                            <div class="col-md-6 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="dstudent_name" id="dstudent_name" value="<?=$dt["student_name"];?>">
                                <div>Name Of Student</div>
                            </div>
                            <div class="col-md-6 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="dparent_name" id="dparent_name" value="<?=$dt["parent_name"];?>">
                                <div>Name of Parent / Legal Guardian</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-12 my-3">
                                <input readonly type='text' class="form-control" name="ddate" id="ddate" value="<?=date("m/d/Y");?>">
                                <div>Date</div>
                            </div>
                            <div class="col-md-4 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="dpassport" id="dpassport" value="<?=$dt["parent_passport_no"];?>">
                                <div>Passport Number</div>
                            </div>
                            <div class="col-md-4 col-sm-12 my-3">
                                <input readonly type="text" class="form-control" name="dnrc_number" id="dnrc_number" value="<?=$dt["parent_nric_no"];?>">
                                <div>NRIC No</div>
                            </div>
                        </div>

                    </div>
                    <?php if($is_contract_generated != '1'){ ?>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-auto mx-4">
                                <button type="button" class="btn btn-primary" onclick="openOtp();">Submit</button>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    </div>

<div id="otpPanel" class="container-tight py-6">

            <!-- <form action="../login.php?" class="card card-md needs-validation" novalidate="" method="post" autocomplete="off"> -->
        <div class="card">
            <div class="card-body">
                
                <div class="text-center my-3">
                    <img src="<?= $logo ?>" height="50" alt="">
                </div>
                <h2 class="mb-3 text-center">Verify OTP</h2>
                <div class="empty-warning" id='otpVerify'></div>

                <div class="mb-3">
                    <label class="form-label">Enter OTP</label>
                    <input type="text" id="otp" value="" name="otp" class="form-control" required="">
                    <div class="invalid-feedback">Invalid OTP</div>
                </div>

                <div class="mt-2">
                    <button class="btn btn-primary" type="button" onclick="validateOtp();">Validate</button>
                </div>

                <div class="mt-2 text-right">
                    <button class="btn btn-link" type="button" onclick="openOtp();">Resent Otp</button>   
                </div>

            </div>
            </div>

</div>


    <script>
    document.body.style.display = "block";
    $(document).ready(function() {
        $("#otpPanel").hide();
    });

    var otpLimit = 0;
    function openOtp(){
        if(otpLimit>10){
            alert("Your account is locked due to multiple otp failed.");
            return;
        }
        otpLimit++;
        $("#contentPanel").hide(400);
        $("#otpPanel").show(400);
        alert("A Otp email is sent on ur mail id [<?=$dt["parent_email"];?>].Please verify the same.");
        try{
            $.ajax({
                url: 'contact_form_mail_send.php',
                type: 'post',
                data: { to: "<?=$dt["parent_email"];?>" },
                async: true,
                success: function (response) {
                    
                }
            });
        }catch(ex){
            console.log(ex);
        }
    }

    function validateOtp(){
        var sid = '<?=$subid;?>';
        var stu_name = '<?=$dt["student_name"];?>';
        var val = $("#otp").val();
        if(val==""){
            alert("Invalid OTP");
            return;
        }
        //ajax session
        try{
            $("#preloader").show();
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { otp: val, val: val, type:"checkOtpForContractForm"},
                async: true,
                success: function (response) {
                    if(response=="success"){
                        
                        $.ajax({
                            url: 'student_contract_mail_send.php',
                            type: 'post',
                            data: { sid: sid, stu_name:stu_name },
                            async: true,
                            success: function (response) {
                                $("#preloader").hide();
                                alert("Your contract form submitted successfully");
                                window.location.href = 'index.php?q=/modules/Campaign/check_status.php';
                            }
                        });
                        
                    }else{
                        $("#preloader").hide();
                        alert("Invalid OTP");
                    }
                }
            });
        }catch(ex){
            console.log(ex);
        }
    }
    </script>

</body>

</html>

<?php 
} else {
    header("Location: home.php");
    die();
}
?>