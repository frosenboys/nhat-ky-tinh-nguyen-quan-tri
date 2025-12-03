<?php
require './include/header.php';
if ($user_info['adminLevel'] < 10) {
  echo "<script>location.href='/'</script>";
  exit();
}

// Path file knowledge
$file = __DIR__ . "/knowledges.txt";

// Nếu file chưa tồn tại -> tạo mới
if (!file_exists($file)) {
    file_put_contents($file, "");
}

// Đọc nội dung file
$currentText = file_get_contents($file);
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quản lý tri thức cho Chatbot</h3>
    </div>

    <div class="card-body">
        <label><b>Nội dung tri thức (knowledges.txt)</b></label>

        <textarea id="knowledgeText" class="form-control" rows="18"
        style="font-family: monospace;"><?= htmlspecialchars($currentText) ?></textarea>

        <button class="btn btn-primary mt-3" id="saveBtn">
            <i class="fa fa-save"></i> Lưu thay đổi
        </button>
    </div>
</div>

<?php require './include/footer.php'; ?>
<script>
$("#saveBtn").click(function () {
    const text = $("#knowledgeText").val();

    $.post("./process/process.php", {
        action: "save_knowledge",
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

