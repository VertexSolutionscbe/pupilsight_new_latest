<?php
@session_start();
?>
<!doctype html>
<html lang="en-US">

<head>
   <meta charset="utf-8" />
   <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
   <title>PupilSight | Admin</title>
   <!-- mobile settings -->
   <meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0" />
   <!-- WEB FONTS -->
   <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700,800&amp;subset=latin,latin-ext,cyrillic,cyrillic-ext" rel="stylesheet" type="text/css" />
   <!-- CORE CSS -->
   <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
   <!-- THEME CSS -->
   <link href="assets/css/essentials.css" rel="stylesheet" type="text/css" />
   <link href="assets/css/layout.css" rel="stylesheet" type="text/css" />
   <link href="assets/css/color_scheme/green.css" rel="stylesheet" type="text/css" id="color_scheme" />
   <link href="assets/css/layout-datatables.css" rel="stylesheet" type="text/css" />
   <link href="assets/css/style.css" rel="stylesheet" type="text/css" />
   <style>
      .menuBtn {
         color: #fff;
         background-color: #007bff;
         border-color: #007bff;
         padding: 0 20px;
         font-size: 16px;
         line-height: 36px;
         margin-right: 10px;
         height: 40px;
         display: inline-block;
         font-weight: 400;
         text-align: center;
         white-space: nowrap;
         vertical-align: middle;
         -ms-touch-action: manipulation;
         touch-action: manipulation;
         cursor: pointer;
         -webkit-user-select: none;
         -moz-user-select: none;
         -ms-user-select: none;
         user-select: none;
         background-image: none;
         border: 1px solid transparent;
         border-radius: 4px;
      }

      .menuBtn:hover {
         color: #fff;
      }
   </style>
</head>

<body>
   <!---loader-->
   <div class="loading" id="loading" style="display:none">Loading&#8230;</div>
   <!--ends ---->
   <!-- WRAPPER -->

   <div style="width:100%;height:70px;line-height:70px;">

      <a class="menuBtn" href="createform.php">
         <i class="main-icon fa fa-info"></i> <span>Section</span>
      </a>

      <a class="menuBtn" href="category.php">
         <i class="main-icon fa fa-cogs"></i> <span>Category</span>
      </a>

      <a class="menuBtn" href="message.php">
         <i class="main-icon fa fa-envelope"></i> <span>Messages</span>
      </a>

   </div>
   <!-- HEADER -->
