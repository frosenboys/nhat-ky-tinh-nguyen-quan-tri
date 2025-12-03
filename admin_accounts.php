<?php
require './include/header.php';
if ($user_info['adminLevel'] < 10) {
  echo "<script>location.href='/'</script>";
  exit();
}

// load danh sách admin
$admins = $mysql->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();
?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center w-100">
    <h3 class="card-title"><i class="fa fa-user-shield mr-2"></i> Quản lý tài khoản Admin</h3>

    <button class="btn btn-primary btn-sm right-0" data-toggle="modal" data-target="#addModal">
      <i class="fa fa-plus"></i> Thêm Admin
    </button>
  </div>

  <div class="card-body">
    <table class="table table-bordered table-striped text-center">
      <thead class="bg-dark text-white">
        <tr>
          <th width="60">ID</th>
          <th>Email</th>
          <th width="200">Hành động</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($admins as $a): ?>
        <tr>
          <td><?= $a["id"] ?></td>
          <td><?= htmlspecialchars($a["email"]) ?></td>

          <td>
            <button class="btn btn-sm btn-warning" 
                    onclick='fillEditForm(<?= json_encode($a) ?>)'
                    data-toggle="modal" data-target="#editModal">
              <i class="fa fa-edit"></i>
            </button>

            <button class="btn btn-sm btn-info" onclick="resetPass(<?= $a['id'] ?>)">
              <i class="fa fa-redo"></i>
            </button>

            <?php
            if ($a['id'] != $user_info['id']) {
            ?>
              <button class="btn btn-sm btn-danger" onclick="deleteAdmin(<?= $a['id'] ?>)">
              <i class="fa fa-trash"></i>
            </button>
            <?php
            }
            ?>
            
          </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($admins)): ?>
        <tr><td colspan="3" class="text-muted p-2">Không có tài khoản nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ========================== -->
<!-- ADD ADMIN MODAL -->
<!-- ========================== -->
<div class="modal fade" id="addModal">
  <div class="modal-dialog">
    <form id="addForm" class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Thêm Admin</h4>
        <button class="close" data-dismiss="modal">×</button>
      </div>
        
      <div class="modal-body">
        <div class="form-group">
          <label>Họ tên</label>
          <input type="text" name="fullName" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Mật khẩu</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Admin Level (Max 10)</label>
          <input type="number" name="adminLevel" class="form-control" required max="10" min="0">
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" type="button" onclick="addAdmin()">Thêm</button>
      </div>

    </form>
  </div>
</div>

<!-- ========================== -->
<!-- EDIT ADMIN MODAL -->
<!-- ========================== -->
<div class="modal fade" id="editModal">
  <div class="modal-dialog">
    <form id="editForm" class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Sửa Admin</h4>
        <button class="close" data-dismiss="modal">×</button>
      </div>

      <div class="modal-body">

        <input type="hidden" name="id" id="edit-id">

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" id="edit-email" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Họ tên</label>
          <input type="text" name="fullName" id="edit-fullName" class="form-control" required>
        </div>
        
        <div class="form-group">
          <label>Admin Level (Max 10)</label>
          <input type="number" name="adminLevel" id="edit-adminLevel" class="form-control" required max="10" min="0">
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-warning" type="button" onclick="saveEdit()">Lưu</button>
      </div>

    </form>
  </div>
</div>


<script>
function addAdmin() {
  $.post("./process/process.php",
    $("#addForm").serialize() + "&action=add_admin",
    res => {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 800);
      } else toastr.error(res.message);
    }, "json"
  );
}

function fillEditForm(a) {
  $("#edit-id").val(a.id);
  $("#edit-fullName").val(a.fullName);
  $("#edit-email").val(a.email);
  $("#edit-adminLevel").val(a.adminLevel);
}

function saveEdit() {
  $.post("./process/process.php",
    $("#editForm").serialize() + "&action=edit_admin",
    res => {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 800);
      } else toastr.error(res.message);
    }, "json"
  );
}

function resetPass(id) {
  if (!confirm("Reset mật khẩu admin này về 123456?")) return;

  $.post("./process/process.php",
    { action: "reset_admin_pass", id },
    res => {
      if (res.status === "success") {
        toastr.success(res.message);
      } else toastr.error(res.message);
    }, "json"
  );
}

function deleteAdmin(id) {
  if (!confirm("Xóa tài khoản admin này?")) return;

  $.post("./process/process.php",
    { action: "delete_admin", id },
    res => {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 800);
      } else toastr.error(res.message);
    }, "json"
  );
}
</script>

<?php require './include/footer.php'; ?>
