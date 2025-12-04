<?php
  session_start();
  if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
      header("Location: /login");
      exit();
  }
  require_once "./process/db_my.php";
  require_once "./process/functions.php";
  $user_info = fetchUser();
  if ($user_info['adminLevel'] >= 10) $adminRoleName = "Super Admin";
  else $adminRoleName = "CTV Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard | Nhật ký tình nguyện</title>
  <!-- favicon -->
  <link rel="icon" href="/assets/image/favicon.ico" type="image/x-icon">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="plugins/toastr/toastr.min.css">
  
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <!-- <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="/assets/image/logo.png" alt="NKTNLogo" height="100" width="100">
  </div> -->

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-user"></i>
        </a>
        <div class="dropdown-menu dropdown-menu dropdown-menu-right">
          <span class="dropdown-item dropdown-header text-lg p-0">Tài khoản</span>
          <div class="dropdown-divider"></div>
          <a href="/logout.php" class="dropdown-item text-danger">
            <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
          </a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
      <img src="/assets/image/logo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-bold">Dashboard</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
        <div class="image">
          <img src="/dist/img/default-avatar.svg" alt="User Image">
        </div>
        <div class="info">
          <div class="d-block text-white"><b><?=$user_info["fullName"]?></b><br>(<?=$adminRoleName?>)</div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="/" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
              </p>
            </a>
          </li>
          <li class="nav-header">CTV</li>
          <li class="nav-item">
            <a href="/mission_approval" class="nav-link">
              <i class="nav-icon fas fa-list"></i>
              <p>
                Duyệt nhiệm vụ
              </p>
            </a>
          </li>
          
          <li class="nav-item" id="ranking_menu">
            <a class="nav-link">
              <i class="nav-icon fas fa-chart-bar"></i>
              <p>
                Ranking
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/ranking_by_union" class="nav-link">
                  <i class="fas fa-angle-right nav-icon"></i>
                  <p>Theo chi đoàn</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/ranking_by_mission" class="nav-link">
                  <i class="fas fa-angle-right nav-icon"></i>
                  <p>Theo nhiệm vụ</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/ranking_by_personal" class="nav-link">
                  <i class="fas fa-angle-right nav-icon"></i>
                  <p>Theo cá nhân</p>
                </a>
              </li>
            </ul>
          </li> 
         
          
          <!-- For High-Level Admin -->
          <?php
          if ($user_info['adminLevel'] >= 10) {
          ?>
          <li class="nav-header">Admin</li>

          <li class="nav-item">
            <a href="/members" class="nav-link">
              <i class="nav-icon fas fa-user"></i>
              <p>
                Đoàn viên
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="/news" class="nav-link">
              <i class="nav-icon far fa-newspaper"></i>
              <p>
                Quản lý tin tức (Bài đăng)
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="/mission_list" class="nav-link">
              <i class="nav-icon fas fa-list"></i>
              <p>
                Quản lí Nhiệm vụ
              </p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="/main_news" class="nav-link">
              <i class="nav-icon fas fa-newspaper"></i>
              <p>
                Quản lí Báo trang chính
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="/digiMap" class="nav-link">
              <i class="nav-icon fas fa-map"></i>
              <p>
                Quản lí Bản đồ số
              </p>
            </a>
          </li>

          <li class="nav-item" id="chatbot_menu">
            <a class="nav-link">
              <i class="nav-icon fas fa-robot"></i>
              <p>
                Chatbot
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/chatbot_instructions" class="nav-link">
                  <i class="fas fa-angle-right nav-icon"></i>
                  <p>Chỉ dẫn</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/chatbot_knowledges" class="nav-link">
                  <i class="fas fa-angle-right nav-icon"></i>
                  <p>Tri thức</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a href="/admin_accounts" class="nav-link">
              <i class="nav-icon far fa-user"></i>
              <p>
                Tài khoản Admin
              </p>
            </a>
          </li>
          <?php
          }
          ?>
          <!-- Logout btn -->
          <li class="nav-item mt-5">
            <a href="/logout.php" class="nav-link text-danger ">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>Đăng xuất</p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Main content -->
    <section class="content p-3">