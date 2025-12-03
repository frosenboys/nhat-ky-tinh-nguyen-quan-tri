<?php
  require './include/header.php';
  require './process/db_pg.php';

  $stmt = $pg->query('
      SELECT 
          "unionGroup",
          SUM(points) AS total_points,
          COUNT(*) AS member_count
      FROM "User"
      WHERE "unionGroup" IS NOT NULL AND "unionGroup" <> \'\'
      GROUP BY "unionGroup"
      ORDER BY total_points DESC
      LIMIT 10
  ');

  $ranks = $stmt->fetchAll();
?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">🏆 Xếp hạng Chi Đoàn</h3>

    <a href="./process/exportExcel?type=union" class="btn btn-success btn-sm">
      <i class="fa fa-file-excel"></i> Xuất Excel
    </a>
  </div>

  <div class="card-body">

    <table class="table table-bordered table-striped text-center">
      <thead class="bg-primary text-white">
        <tr>
          <th style="width:80px">Rank</th>
          <th>Chi đoàn</th>
          <th style="width:150px">Số thành viên</th>
          <th style="width:150px">Tổng điểm</th>
        </tr>
      </thead>

      <tbody>
      <?php
      $rank = 1;
      foreach ($ranks as $row): ?>
        <tr>
          <td><span class="badge badge-info p-2"><?= $rank++ ?></span></td>

          <td>
            <b><?= htmlspecialchars($row["unionGroup"]) ?></b>
          </td>

          <td>
            <span class="badge badge-secondary">
              <?= $row["member_count"] ?>
            </span>
          </td>

          <td>
            <span class="badge badge-success">
              <?= $row["total_points"] ?>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>

      <?php if (count($ranks) === 0): ?>
        <tr>
          <td colspan="4" class="text-center text-muted p-3">
            Không có chi đoàn nào phù hợp.
          </td>
        </tr>
      <?php endif; ?>

      </tbody>
    </table>

  </div>
</div>

<?php require './include/footer.php'; ?>

<script>
  $("#ranking_menu").addClass("menu-open");
</script>