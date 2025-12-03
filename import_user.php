<?php
require './include/header.php';
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fa fa-file-excel mr-2"></i> Import đoàn viên từ Excel</h3>
  </div>

  <div class="card-body">

    <div class="alert alert-info">
      <b>Yêu cầu file Excel:</b><br>
      Phải theo đúng thứ tự format theo ví dụ dưới đây:<br>  
      <table class="table table-bordered w-50 bg-light mt-2">
        <tr>
          <th>Mã số đoàn viên</th>
          <th>Họ và tên</th>
          <th>Chi đoàn</th>
          <th>Chức vụ</th>
        </tr>
        <tr>
          <td>20180001</td>
          <td>Nguyễn Văn A</td>
          <td>12A1.1</td>
          <td>Bí thư Chi đoàn</td>
        </tr>
        <tr>
          <td>20190501</td>
          <td>Nguyễn Thị B</td>
          <td>11A5</td>
          <td></td>
        </tr>
        <tr>
          <td>...</td>
          <td>...</td>
          <td>...</td>
          <td>...</td>
        </tr>
        
      </table>
      <b>Lưu ý: Chức danh để trống sẽ mặc đinh là Đoàn viên</b><br>
      (Mật khẩu sẽ măc định là <b>123456</b>)<br>
      File phải có định dạng <b>.xlsx</b>
    </div>

    <form id="importForm" enctype="multipart/form-data">

      <div class="form-group">
        <label>Chọn file Excel (.xlsx)</label>
        <input type="file" name="file" class="form-control p-1" accept=".xlsx" required>
      </div>

      <button type="button" class="btn btn-primary" onclick="importExcel()">
        <i class="fa fa-upload"></i> Bắt đầu import
      </button>

    </form>

    <div id="result" class="mt-3"></div>

  </div>
</div>

<script>
function importExcel() {

  let formData = new FormData(document.getElementById("importForm"));
  formData.append("action", "import_user_excel");

  $.ajax({
    url: "./process/process.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function(res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => {location.href = "/members";}, 1000);
      } else {
        toastr.error(res.message);
      }
    },
    error: function(err) {
      toastr.error("Lỗi không xác định");
    }
  });
}
</script>

<?php require './include/footer.php'; ?>
