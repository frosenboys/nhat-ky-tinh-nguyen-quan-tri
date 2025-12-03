<?php
require './include/header.php';
require './process/db_pg.php';

// ======================
// LẤY THỐNG KÊ TỪ POSTGRES
// ======================

// Tổng đoàn viên
$totalMembers = $pg->query('SELECT COUNT(*) FROM "User"')->fetchColumn();

// Tổng bài nộp còn pending
$pendingSubs = $pg->query('SELECT COUNT(*) FROM "MissionSubmission" WHERE status = \'pending\'')->fetchColumn();

// Nhiệm vụ đang mở
$openMissions = $pg->query('SELECT COUNT(*) FROM "Missions" WHERE status = \'open\'')->fetchColumn();

// Tổng tin tức
$totalNews = $pg->query('SELECT COUNT(*) FROM "News"')->fetchColumn();

// Top 5 đoàn viên điểm cao nhất
$topMembers = $pg->query('
    SELECT "studentId", "fullName", points
    FROM "User"
    ORDER BY points DESC
    LIMIT 5
')->fetchAll();

// Thống kê chi đoàn (đếm đoàn viên theo chi đoàn)
$unionStats = $pg->query('
    SELECT "unionGroup", COUNT(*) as total
    FROM "User"
    GROUP BY "unionGroup"
    ORDER BY total DESC
')->fetchAll();

// Lấy 5 bài nộp mới nhất
$latestSubs = $pg->query('
    SELECT m.*, u."fullName"
    FROM "MissionSubmission" m
    LEFT JOIN "User" u ON u."studentId" = m."studentId"
    ORDER BY m.id DESC
    LIMIT 5
')->fetchAll();

?>

<div class="row">

  <!-- Tổng đoàn viên -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3><?= $totalMembers ?></h3>
        <p>Tổng đoàn viên</p>
      </div>
      <div class="icon"><i class="fa fa-users"></i></div>
    </div>
  </div>

  <!-- Nhiệm vụ mở -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3><?= $openMissions ?></h3>
        <p>Nhiệm vụ đang mở</p>
      </div>
      <div class="icon"><i class="fa fa-tasks"></i></div>
    </div>
  </div>

  <!-- Pending submissions -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning" onClick="location.href='/mission_approval';" style="cursor: pointer;">
      <div class="inner">
        <h3><?= $pendingSubs ?></h3>
        <p>Bài chờ duyệt</p>
      </div>
      <div class="icon"><i class="fa fa-clock"></i></div>
    </div>
  </div>

  <!-- Tin tức -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3><?= $totalNews ?></h3>
        <p>Tổng tin tức</p>
      </div>
      <div class="icon"><i class="fa fa-newspaper"></i></div>
    </div>
  </div>

</div>

<!-- ========================= -->
<!-- TOP 5 ĐOÀN VIÊN XUẤT SẮC -->
<!-- ========================= -->
<div class="card mt-3">
  <div class="card-header bg-success text-white">
    <h3 class="card-title">Top 5 đoàn viên xuất sắc</h3>
  </div>
  <div class="card-body">
    <table class="table table-hover">
      <thead class="bg-light">
        <tr>
          <th>MSĐV</th>
          <th>Họ tên</th>
          <th>Điểm</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($topMembers as $m): ?>
          <tr>
            <td><?= $m["studentId"] ?></td>
            <td><?= $m["fullName"] ?></td>
            <td><b><?= $m["points"] ?></b></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>


<!-- ========================= -->
<!-- HOẠT ĐỘNG GẦN ĐÂY -->
<!-- ========================= -->
<div class="card mt-3">
  <div class="card-header bg-dark text-white">
    <h3 class="card-title">Bài nộp gần đây</h3>
  </div>
  <div class="card-body">
    <?php foreach ($latestSubs as $s): ?>
      <div class="border p-2 mb-2 rounded">
        <b>#<?= $s["id"] ?></b> - 
        <b><?= $s["fullName"] ?></b> 
        đã nộp cho nhiệm vụ <b><?= $s["missionId"] ?></b>
        <span class="badge badge-info ml-2"><?= $s["status"] ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ======================= -->
<!-- THỐNG KÊ CHI ĐOÀN -->
<!-- ======================= -->
<div class="card">
  <div class="card-header bg-secondary text-white">
    <h3 class="card-title">Thống kê chi đoàn</h3>
  </div>
  <div class="card-body">
    <table class="table table-bordered text-center">
      <thead class="bg-light">
        <tr>
          <th>Chi đoàn</th>
          <th>Tổng số</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($unionStats as $u): ?>
          <tr>
            <td><?= $u["unionGroup"] ?></td>
            <td><?= $u["total"] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require './include/footer.php'; ?>
