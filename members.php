<?php
require './include/header.php';
if ($user_info['adminLevel'] < 10) {
  echo "<script>location.href='/'</script>";
  exit();
}
require './process/db_pg.php';

// --- CONFIG ---
$limit = 10; // mỗi trang 10 dòng
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = trim($_GET['search'] ?? "");

$offset = ($page - 1) * $limit;

// --- QUERY COUNT ---
if ($search !== "") {
    $countStmt = $pg->prepare('SELECT COUNT(*) FROM "User" WHERE "studentId" ILIKE :s OR "fullName" ILIKE :s');
    $countStmt->execute([":s" => "%$search%"]);
} else {
    $countStmt = $pg->query('SELECT COUNT(*) FROM "User"');
}
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// --- QUERY DATA ---
if ($search !== "") {
    $stmt = $pg->prepare('SELECT * FROM "User"
                          WHERE "studentId" ILIKE :s OR "fullName" ILIKE :s
                          ORDER BY "studentId" ASC
                          LIMIT :limit OFFSET :offset');
    $stmt->bindValue(":s", "%$search%", PDO::PARAM_STR);
} else {
    $stmt = $pg->prepare('SELECT * FROM "User"
                          ORDER BY "studentId" ASC
                          LIMIT :limit OFFSET :offset');
}

$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll();
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Danh sách Đoàn viên (Tổng số: <?= $totalRows ?>)</h3>
  </div>

  <div class="card-body">
    <form class="form-inline mb-3" method="GET">
      <input
        type="text"
        name="search"
        value="<?= htmlspecialchars($search) ?>"
        class="form-control mr-2 w-25"
        placeholder="Tìm theo Mã Đoàn viên hoặc Tên"
      />
      <button class="btn btn-primary">Search</button>
    </form>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Mã ĐV</th>
          <th>Họ tên</th>
          <th>Chi đoàn</th>
          <th>Chức vụ</th>
          <th width="140px">Chỉnh sửa</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($members as $m): ?>
          <tr>
            <td><?= $m['studentId'] ?></td>
            <td><?= $m['fullName'] ?></td>
            <td><?= $m['unionGroup'] ?></td>
            <td><?= $m['position'] ?></td>

            <td align="center">
              <button class="btn btn-sm btn-primary"
                data-toggle="modal"
                data-target="#editMemberModal"
                onclick='fillEditForm(<?= json_encode($m) ?>)'>
                <i class="fa fa-edit"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- PAGINATION -->
    <div class="mt-3">
      <ul class="pagination justify-content-center"> <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
            <a class="page-link" href="?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>">«</a>
        </li>

        <?php
        // Cấu hình số lượng trang hiển thị xung quanh trang hiện tại
        $delta = 2; // Ví dụ: đang ở trang 10 thì hiện 8,9,10,11,12
        
        // Luôn hiển thị trang 1
        $range = array(1);

        // Tính toán khoảng giữa cần hiển thị
        for ($i = max(2, $page - $delta); $i <= min($totalPages - 1, $page + $delta); $i++) {
            $range[] = $i;
        }

        // Luôn hiển thị trang cuối (nếu tổng trang > 1)
        if ($totalPages > 1) {
            $range[] = $totalPages;
        }

        // Logic hiển thị dấu "..."
        $prev = 0;
        foreach ($range as $i): 
            // Nếu khoảng cách giữa 2 trang trong danh sách hiển thị lớn hơn 1, in ra dấu "..."
            if ($prev > 0 && $i - $prev > 1): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; 
            
            $prev = $i; // Cập nhật trang trước đó
            ?>

            <li class="page-item <?= ($i == $page ? 'active' : '') ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endforeach; ?>

        <li class="page-item <?= ($page >= $totalPages ? 'disabled' : '') ?>">
            <a class="page-link" href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= urlencode($search) ?>">»</a>
        </li>

      </ul>
    </div>

  </div>
</div>

<div class="card">
  <div class="card-body">
    <a href="/import_user" class="btn btn-success"><i class="fa fa-file-excel mx-2"></i>Import đoàn viên từ file XLSX</a>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <button class="btn btn-danger m-2" onclick="deleteAllMembers()">Xóa hết tất cả đoàn viên</button></br>
    <button class="btn btn-danger m-2" onclick="resetPoints()">Reset điểm</button>
  </div>
</div>


<?php require './include/footer.php'; ?>
<!-- Modal sửa member -->
<div class="modal fade" id="editMemberModal">
  <div class="modal-dialog">
    <form id="editMemberForm" class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Sửa thông tin đoàn viên</h4>
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="edit-id" name="studentId">

        <div class="form-group">
          <label>Họ tên</label>
          <input type="text" class="form-control" id="edit-name" name="fullName">
        </div>

        <div class="form-group">
          <label>Chi đoàn</label>
          <input type="text" class="form-control" id="edit-group" name="unionGroup">
        </div>

        <div class="form-group">
          <label>Chức vụ</label>
          <input type="text" class="form-control" id="edit-position" name="position">
        </div>

        <button type="button" onclick="resetPassword()" class="btn btn-warning btn-block mt-3">
          <i class="fa fa-key"></i> Reset mật khẩu
        </button>

      </div>

      <div class="modal-footer">
        <button class="btn btn-primary">Lưu thay đổi</button>
      </div>

    </form>
  </div>
</div>


<script>
var modal_id;
function fillEditForm(m) {
  modal_id = m.studentId;
  $("#edit-name").val(m.fullName);
  $("#edit-group").val(m.unionGroup);
  $("#edit-position").val(m.position);
}

$("#editMemberForm").submit(function(e) {
  e.preventDefault();

  $.ajax({
    url: "./process/process.php",
    type: "POST",
    data: $(this).serialize() + "&action=update_member",
    dataType: "json",
    success: function(res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 1000);
      } else {
        toastr.error(res.message);
      }
    }
  });
});

function resetPassword() {
    let id = modal_id;

    if (!id) {
        toastr.error("Không tìm thấy ID!");
        return;
    }

    if (!confirm("Bạn có chắc muốn reset mật khẩu của đoàn viên này về \"123456\" ?")) {
        return;
    }

    $.ajax({
        url: "./process/process.php",
        type: "POST",
        data: {
            action: "reset_password",
            id: id
        },
        success: function(res) {
            if (res.status === "success") {
                toastr.success(res.message);
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error("Lỗi server!");
        }
    });
}

function deleteAllMembers() {
    if (!confirm("Bạn có chắc muốn xóa hết tất cả đoàn viên? Hành động này không thể hoàn tác!")) {
        return;
    }

    $.ajax({
        url: "./process/process.php",
        type: "POST",
        data: {
            action: "delete_all_members",
            confirm: "yes"
        },
        dataType: "json",
        success: function(res) {
            if (res.status === "success") {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error("Lỗi server!");
        }
    });
}

function resetPoints() {
    if (!confirm("Bạn có chắc muốn reset điểm của tất cả đoàn viên?")) {
        return;
    }

    $.ajax({
        url: "./process/process.php",
        type: "POST",
        data: {
            action: "reset_points"
        },
        dataType: "json",
        success: function(res) {
            if (res.status === "success") {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error("Lỗi server!");
        }
    });
}
</script>
