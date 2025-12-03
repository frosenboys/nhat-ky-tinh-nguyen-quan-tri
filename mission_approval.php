<?php
require './include/header.php';
require './process/db_pg.php';

// Lấy tất cả bài pending
$subs = $pg->query('SELECT * FROM "MissionSubmission" WHERE status = \'pending\' ORDER BY id DESC')->fetchAll();
?>

<div class="card">
  <div class="card-header bg-primary text-white">
    <h3 class="card-title">
      <i class="fas fa-clipboard-check mr-2"></i>
      Duyệt bài nhiệm vụ
    </h3>
  </div>

  <div class="card-body">

    <?php if (count($subs) === 0): ?>
      <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i> Không có bài nào cần duyệt.
      </div>
    <?php endif; ?>

    <div class="row">

      <?php foreach ($subs as $s): ?>

        <div class="col-md-6 col-lg-4">
          <div class="card shadow-sm border rounded mb-4">

            <div class="card-header bg-light">
              <strong>Bài #<?= $s["id"] ?></strong>
            </div>

            <div class="card-body">

              <p class="mb-1"><b>MSSV:</b> <?= $s["studentId"] ?></p>
              <p class="mb-1"><b>Mission:</b> <?= $s["missionId"] ?></p>

              <p class="mb-2"><b>Ghi chú:</b><br>
                <?= nl2br(htmlspecialchars($s["note"])) ?>
              </p>

              <div class="text-center mb-2">
                <img src="<?= $s["imageLink"] ?>" 
                     class="img-fluid rounded shadow-sm"
                     style="max-height: 200px; object-fit: cover; cursor:pointer"
                     onclick="openImage('<?= $s['imageLink'] ?>')">
              </div>

              <!-- Nút duyệt thường -->
              <button class="btn btn-success btn-block mb-2"
                onclick='approveNormal(<?= json_encode($s["id"]) ?>)'>
                <i class="fas fa-check"></i> Duyệt
              </button>

              <!-- Nút duyệt + đưa lên News -->
              <button class="btn btn-primary btn-block"
                onclick='approveNews(<?= json_encode($s["id"]) ?>)'>
                <i class="fas fa-bullhorn"></i> Duyệt & Đăng News
              </button>

            </div>
          </div>
        </div>

      <?php endforeach; ?>

    </div>
  </div>
</div>

<!-- MODAL XEM ẢNH -->
<div class="modal fade" id="imageModal">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark text-center">
      <img id="modalImage" src="" class="img-fluid rounded">
    </div>
  </div>
</div>

<?php require './include/footer.php'; ?>
<script>
function openImage(src) {
  $("#modalImage").attr("src", src);
  $("#imageModal").modal("show");
}

// DUYỆT THƯỜNG
function approveNormal(id) {
  if (!confirm("Duyệt bài này?")) return;

  $.post(
    "./process/process.php",
    { action: "approve_submission_normal", id: id },
    function (res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 600);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}

// DUYỆT & ĐĂNG NEWS
function approveNews(id) {
  if (!confirm("Duyệt và ĐĂNG lên mục Tin tức?")) return;

  $.post(
    "./process/process.php",
    { action: "approve_submission_news", id: id },
    function (res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 600);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}
$('#mission_menu').addClass('menu-open');
</script>