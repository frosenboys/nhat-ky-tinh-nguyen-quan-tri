<?php
require './include/header.php';
if ($user_info['adminLevel'] < 10) {
  echo "<script>location.href='/'</script>";
  exit();
}
// Path file instruction
$file = __DIR__ . "/instructions.txt";

// Nếu file chưa tồn tại thì tạo rỗng
if (!file_exists($file)) {
    file_put_contents($file, "");
}

// Đọc nội dung file
$currentText = file_get_contents($file);
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Cấu hình hướng dẫn cho Chatbot</h3>
    </div>

    <div class="card-body">
        <label><b>Nội dung hướng dẫn (instructions.txt)</b></label>

        <textarea id="instructionText" class="form-control" rows="18"
        style="font-family: monospace;"><?= htmlspecialchars($currentText) ?></textarea>

        <button class="btn btn-primary mt-3" id="saveBtn">
            <i class="fa fa-save"></i> Lưu thay đổi
        </button>
    </div>
</div>

<?php require './include/footer.php'; ?>
<script>
$("#saveBtn").click(function () {
    const text = $("#instructionText").val();

    $.post("./process/process.php", {
        action: "save_instruction",
        text: text
    }, function(res) {
        if (res.status === "success") {
            toastr.success(res.message);
        } else {
            toastr.error(res.message);
        }
    }, "json");
});
$("#chatbot_menu").addClass("menu-open");
</script>

