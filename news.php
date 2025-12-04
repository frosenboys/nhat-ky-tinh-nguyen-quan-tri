<?php
  require './include/header.php';
  if ($user_info['adminLevel'] < 10) {
    echo "<script>location.href='/'</script>";
    exit();
  }
  require './process/db_pg.php';

  $limit = 10;
  $page = isset($_GET["page"]) ? max(1, intval($_GET["page"])) : 1;
  $offset = ($page - 1) * $limit;

  $stmt = $pg->prepare('
      SELECT n.*, u."fullName"
      FROM "News" n
      LEFT JOIN "User" u ON u."studentId" = n."authorId"
      ORDER BY n."id" DESC
      LIMIT '.$limit.' OFFSET '.$offset.'
  ');
  $stmt->execute();

  $countStmt = $pg->query('SELECT COUNT(*) FROM "News"');


  $totalRows = $countStmt->fetchColumn();
  $totalPages = ceil($totalRows / $limit);

  $news = $stmt->fetchAll();
?>

<div class="card">

  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Quản lý tin tức (Bài đăng)</h3>
  </div>


  <div class="card-body">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th style="width: 60px">ID</th>
          <th>Nội dung</th>
          <th style="width: 150px">Tác giả</th>
          <th style="width: 140px">Ngày tạo</th>
          <th style="width: 90px">Ảnh</th>
          <th style="width: 100px">Hành động</th>
        </tr>
      </thead>

      <tbody>
      <?php foreach ($news as $n): ?>
        <tr>
          <td><?= $n["id"] ?></td>
          <td><?= nl2br(htmlspecialchars($n["content"])) ?></td>
          <td><?= $n["fullName"] ?: "Ẩn danh" ?></td>
          <td><?= date("d/m/Y", strtotime($n["createdAt"])) ?></td>

          <td>
            <img src="<?= $n["imageUrl"] ?>"
                 onclick="showImage('<?= $n["imageUrl"] ?>')"
                 style="height:60px;width:60px;object-fit:cover;border-radius:5px;cursor:pointer;">
          </td>

          <td>
            <button class="btn btn-sm btn-danger"
                    onclick="deleteNews(<?= $n['id'] ?>)">
              <i class="fa fa-trash"></i>
            </button>
          </td>

        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <!-- PAGINATION -->
    <nav class="mt-3">
      <ul class="pagination justify-content-center">

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link"
               href="?page=<?= $i ?>">
              <?= $i ?>
            </a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
</div>
<div class="card">
  <div class="card-body">
    <button class="btn btn-danger m-2" onclick="deleteAllNews()">Reset tất cả tin tức</button></br>
  </div>
</div>

<!-- ========================== -->
<!-- IMAGE PREVIEW MODAL -->
<!-- ========================== -->
<div class="modal fade" id="imagePreviewModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-2">
      <img id="preview-img" src="" style="width:100%;border-radius:6px;">
    </div>
  </div>
</div>

<script>
function showImage(url) {
  $("#preview-img").attr("src", url);
  $("#imagePreviewModal").modal("show");
}

function deleteNews(id) {
  if (!confirm("Xoá bài viết này?")) return;

  $.post(
    "./process/process.php",
    { action: "delete_news", id: id },
    function(res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 700);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}
function deleteAllNews() {
  if (!confirm("Xoá tất cả bài viết?")) return;

  $.post(
    "./process/process.php",
    { action: "delete_all_news" },
    function(res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 700);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}
</script>

<?php require './include/footer.php'; ?>
