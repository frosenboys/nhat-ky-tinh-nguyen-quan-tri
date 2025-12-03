<?php
session_start();
header("Content-Type: application/json");

require_once "./db_my.php";
require_once "./db_pg.php";
require_once "./functions.php";

// Chỉ cho phép POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    alert("error", "Phương thức không hợp lệ!");
    exit();
}

$action = $_POST["action"] ?? null;

/*
|--------------------------------------------------------------------------
| LOGIN (AJAX)
|--------------------------------------------------------------------------
*/
if ($action === "login") {

    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($email === "" || $password === "") {
        alert("error", "Vui lòng nhập email và mật khẩu!");
    }

    try {
        $stmt = $mysql->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(["email" => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            alert("error", "Email không tồn tại!");
        }

        if (!password_verify($password, $user["password"])) {
            alert("error", "Mật khẩu không đúng!");
        }

        // SAVE SESSION
        $_SESSION["logged_in"] = true;
        $_SESSION["user"] = [
            "id" => $user["id"],
        ];

        alert("success", "Đăng nhập thành công!");

    } catch (Exception $e) {
        alert("error", "Lỗi server: " . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| UPDATE MEMBER
|--------------------------------------------------------------------------
*/
if ($action === "update_member") {

    $id         = $_POST["studentId"] ?? "";
    $name       = trim($_POST["fullName"] ?? "");
    $group      = trim($_POST["unionGroup"] ?? "");
    $position   = trim($_POST["position"] ?? "");

    if ($name === "" || $group === "" || $position === "") {
        alert("error", "Vui lòng nhập đầy đủ họ thông tin!");
    }

    try {
        $stmt = $pg->prepare('UPDATE "User"
                              SET "fullName" = :name,
                                  "unionGroup" = :group,
                                  "position" = :position
                              WHERE "studentId" = :id');

        $stmt->execute([
            ":name" => $name,
            ":group" => $group,
            ":position" => $position,
            ":id" => $id
        ]);

        alert("success", "Cập nhật thành công!");
    } 
    catch (Exception $e) {
        alert("error", "Lỗi: " . $e->getMessage());
    }
}
/*
|--------------------------------------------------------------------------
| RESET PASSWORD – đặt về 123456
|--------------------------------------------------------------------------
*/
if ($action === "reset_password") {

    $id = $_POST["id"] ?? null;

    if (!$id) alert("error", "Thiếu studentId!");

    try {
        $newPass = password_hash("123456", PASSWORD_BCRYPT);

        $stmt = $pg->prepare('UPDATE "User" SET "password" = ? WHERE "studentId" = ?');
        $stmt->execute([$newPass, $id]);

        if ($stmt->rowCount() === 0) {
            alert("error", "Không tìm thấy đoàn viên!");
        }

        alert("success", "Đã reset mật khẩu về 123456!");

    } catch (Exception $e) {
        alert("error", "Lỗi reset mật khẩu: " . $e->getMessage());
    }
}

/*
|------------------------------------------------------------------
| IMPORT USER FROM EXCEL (.xlsx)
|------------------------------------------------------------------
*/
if ($action === "import_user_excel") {

    require_once __DIR__ . "/SimpleXLSX.php";

    if (!isset($_FILES["file"])) {
        alert("error", "Không có file upload!");
    }

    $filePath = $_FILES["file"]["tmp_name"];

    if (!$xlsx = SimpleXLSX::parse($filePath)) {
        alert("error", "Không đọc được file Excel!");
    }

    try {
        $pg->beginTransaction();

        $rows = $xlsx->rows();
        $count = 0;

        foreach ($rows as $i => $r) {

            // Bỏ header
            if ($i === 0) continue;

            // Bỏ dòng trống hoặc thiếu dữ liệu
            if (!isset($r[0]) || trim($r[0]) === "") continue;

            $studentId  = trim($r[0]);
            $fullName   = trim($r[1] ?? "");
            $unionGroup = trim($r[2] ?? "");
            $position   = trim($r[3]);
            if ($position === "") $position = "Đoàn viên";

            if ($fullName === "") continue;

            $hashed = password_hash("123456", PASSWORD_BCRYPT);

            // Query thuần
            $sql = '
                INSERT INTO "User" ("studentId","fullName","unionGroup","position","password")
                VALUES (
                    \''.$studentId.'\',
                    \''.$fullName.'\',
                    \''.$unionGroup.'\',
                    \''.$position.'\',
                    \''.$hashed.'\'
                )
                ON CONFLICT ("studentId") DO UPDATE SET
                    "fullName"   = EXCLUDED."fullName",
                    "unionGroup" = EXCLUDED."unionGroup",
                    "position"   = EXCLUDED."position";
            ';

            $pg->query($sql);
            $count++;
        }

        $pg->commit();
        alert("success", "Đã import $count đoàn viên!");

    } catch (Exception $e) {
        $pg->rollBack();
        alert("error", "Lỗi import: " . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| DELETE ALL MEMBERS
|--------------------------------------------------------------------------
*/
if ($action === "delete_all_members") {

    if (!isset($_POST["confirm"]) || $_POST["confirm"] !== "yes") {
        alert("error", "Hành động không được xác nhận!");
    }

    try {
        $pg->query('DELETE FROM "User"');
        alert("success", "Đã xóa hết tất cả đoàn viên!");
    } catch (Exception $e) {
        alert("error", "Lỗi xóa: " . $e->getMessage());
    }
}

/* 
|--------------------------------------------------------------------------
|   EDIT MISSION
|--------------------------------------------------------------------------
*/
if ($action === "edit_mission") {

    $id = $_POST["id"] ?? null;
    $missionName = trim($_POST["missionName"] ?? "");
    $for = trim($_POST["for"] ?? "");
    $status = trim($_POST["status"] ?? "");

    if (!$id) alert("error", "Thiếu ID nhiệm vụ!");

    try {
        $stmt = $pg->prepare('UPDATE "Missions"
            SET "missionName" = :missionName,
                "for"         = :for,
                "status"      = :status
            WHERE id = :id');

        $stmt->execute([
            ":missionName" => $missionName,
            ":for"         => $for,
            ":status"      => $status,
            ":id"          => $id
        ]);

        alert("success", "Cập nhật nhiệm vụ thành công!");

    } catch (Exception $e) {
        alert("error", "Lỗi SQL: " . $e->getMessage());
    }
}


/*
|--------------------------------------------------------------------------
|   RESET MISSION SUBMISSIONS
|--------------------------------------------------------------------------
*/
if ($action === "reset_mission") {

    $id = $_POST["id"] ?? null;
    if (!$id) alert("error", "Thiếu ID!");

    try {
        // $pg->beginTransaction();
        // $stmt = $pg->prepare('DELETE FROM "MissionSubmission" WHERE "missionId" = ?');
        // $stmt->execute([$id]);
        // $stmt = $pg->prepare('UPDATE "Missions" SET "joined" = 0, "status" = \'close\' WHERE "id" = ?');
        // $stmt->execute([$id]);
        // $pg->commit();
        $pg->query('DELETE FROM "MissionSubmission" WHERE "missionId" = '.$id);
        $pg->query('UPDATE "Missions" SET "joined" = 0, "status" = \'close\' WHERE "id" = '.$id);
        alert("success", "Đã reset nhiệm vụ!");
    } catch (Exception $e) {
        $pg->rollBack();
        alert("error", "Lỗi reset: " . $e->getMessage());
    }
}

// ===============================================
// DUYỆT THƯỜNG
// ===============================================
if ($action === "approve_submission_normal") {

    $id = $_POST["id"] ?? null;
    if (!$id) alert("error", "Thiếu ID submission!");

    try {
        $sub = $pg->query('SELECT * FROM "MissionSubmission" WHERE id = '.$id)->fetch(PDO::FETCH_ASSOC);
        if (!$sub) alert("error", "Submission không tồn tại!");

        $missionId = intval($sub["missionId"]);
        $studentId = $sub["studentId"];

        $pointColumn = "\"points_{$missionId}\"";

        $pg->query('UPDATE "MissionSubmission" SET status = \'approved\' WHERE id = '.$id);

        $pg->query('UPDATE "User" SET 
                       '.$pointColumn.' = '.$pointColumn.' + 1,
                       points = points + 1
                    WHERE "studentId" = \''.$studentId.'\'');

        $pg->query('UPDATE "Missions" SET "joined" = "joined" + 1 WHERE id = '.$missionId);

        alert("success", "Duyệt thành công! Đã cộng điểm nhiệm vụ.");

    } catch (Exception $e) {
        alert("error", "Lỗi: ".$e->getMessage());
    }
}


// ===============================================
// DUYỆT + ĐĂNG NEWS
// ===============================================
if ($action === "approve_submission_news") {

    $id = $_POST["id"] ?? null;
    if (!$id) alert("error", "Thiếu ID submission!");

    try {

        $sub = $pg->query('SELECT * FROM "MissionSubmission" WHERE id = '.$id)->fetch(PDO::FETCH_ASSOC);
        if (!$sub) alert("error", "Submission không tồn tại!");

        $missionId = intval($sub["missionId"]);
        $studentId = $sub["studentId"];

        $pointColumn = "\"points_{$missionId}\"";

        $pg->query('UPDATE "MissionSubmission" SET status = \'approved\' WHERE id = '.$id);

        $pg->query('UPDATE "User" SET 
                       '.$pointColumn.' = '.$pointColumn.' + 1,
                       points = points + 1
                    WHERE "studentId" = \''.$studentId.'\'');

        $pg->query('UPDATE "Missions" SET "joined" = "joined" + 1 WHERE id = '.$missionId);

        $title   = "Bài nộp nhiệm vụ #".$sub["missionId"];
        $content = $sub["note"] ?? "";
        $image   = $sub["imageLink"];

        $queryNews = '
            INSERT INTO "News" ("authorId", "title", "content", "imageUrl", "submissionId") 
            VALUES (
                \''.$studentId.'\',
                \''.$title.'\',
                \''.$content.'\',
                \''.$image.'\',
                '.$sub["id"].'
            )';

        $pg->query($queryNews);

        alert("success", "Đã duyệt + đăng News + cộng điểm!");

    } catch (Exception $e) {
        alert("error", "Lỗi: ".$e->getMessage());
    }
}
/*
|--------------------------------------------------------------------------
| SAVE INSTRUCTION TEXT
|--------------------------------------------------------------------------
*/
if ($action === "save_instruction") {

    $text = $_POST["text"] ?? null;

    if ($text === null) {
        alert("error", "Không có nội dung để lưu!");
    }

    try {
        $path = __DIR__ . "/../instructions.txt";
        file_put_contents($path, $text);
        alert("success", "Đã lưu thành công!");
    } catch (Exception $e) {
        alert("error", "Lỗi khi lưu file: " . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| SAVE KNOWLEDGE TEXT
|--------------------------------------------------------------------------
*/
if ($action === "save_knowledge") {

    $text = $_POST["text"] ?? null;

    if ($text === null) {
        alert("error", "Không có nội dung để lưu!");
    }

    try {
        $path = __DIR__ . "/../knowledges.txt";
        file_put_contents($path, $text);
        alert("success", "Đã lưu tri thức thành công!");
    } catch (Exception $e) {
        alert("error", "Lỗi khi lưu file: " . $e->getMessage());
    }
}

if ($action === "test_pg") {
    if (!$pg) alert("error", "PG NOT CONNECTED!");
    alert("success", "PG OK!");
}

/*
|--------------------------------------------------------------------
| DELETE NEWS
|--------------------------------------------------------------------
*/
if ($action === "delete_news") {

    $id = intval($_POST["id"] ?? 0);
    if ($id <= 0) alert("error", "Thiếu ID!");

    try {
        $pg->query('DELETE FROM "NewsLike" WHERE "newsId" = '.$id);
        $pg->query('DELETE FROM "NewsComment" WHERE "newsId" = '.$id);
        $pg->query('DELETE FROM "News" WHERE "id" = '.$id);
        alert("success", "Đã xoá bài viết thành công!");

    } catch (Exception $e) {
        $pg->rollBack();
        alert("error", "Lỗi xóa: " . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| ADD MAIN NEWS
|--------------------------------------------------------------------------
*/

if ($action === "add_main_news") {

    $link  = trim($_POST["link"] ?? "");
    $image = trim($_POST["image"] ?? "");

    if ($link === "" || $image === "") {
        alert("error", "Vui lòng nhập đầy đủ!");
    }

    try {
        $pg->query('INSERT INTO "main_news" (link, image)
                    VALUES (\''.$link.'\', \''.$image.'\')');

        alert("success", "Đã thêm bài!");
    } catch (Exception $e) {
        alert("error", $e->getMessage());
    }
}

/*|--------------------------------------------------------------------------
| EDIT MAIN NEWS
|--------------------------------------------------------------------------
*/
if ($action === "edit_main_news") {

    $id    = intval($_POST["id"] ?? 0);
    $link  = trim($_POST["link"] ?? "");
    $image = trim($_POST["image"] ?? "");

    if ($id <= 0) alert("error", "Thiếu ID!");

    try {
        $pg->query('UPDATE "main_news"
                    SET link = \''.$link.'\',
                        image = \''.$image.'\'
                    WHERE id = '.$id);

        alert("success", "Đã cập nhật!");
    } catch (Exception $e) {
        alert("error", $e->getMessage());
    }
}

/*|--------------------------------------------------------------------------
| DELETE MAIN NEWS
|--------------------------------------------------------------------------
*/
if ($action === "delete_main_news") {
    $id = intval($_POST["id"] ?? 0);
    if ($id <= 0) alert("error", "Thiếu ID!");

    try {
        $pg->query('DELETE FROM "main_news" WHERE id = '.$id);
        alert("success", "Đã xóa!");
    } catch (Exception $e) {
        alert("error", $e->getMessage());
    }
}

/*
|---------------------------------------------------------------------
| ADD DIGIMAP
|---------------------------------------------------------------------
*/
if ($action === "add_digi") {
    $name = trim($_POST["pinName"]);
    $link = trim($_POST["pinLink"]);

    if ($name === "" || $link === "") alert("error", "Thiếu dữ liệu!");

    try {
        $pg->query('INSERT INTO "digiMap" ("pinName","pinLink","joined") 
                    VALUES (\''.$name.'\', \''.$link.'\', 0)');
        alert("success", "Đã thêm điểm mới!");
    } catch (Exception $e) {
        alert("error", $e->getMessage());
    }
}

/*
|---------------------------------------------------------------------
| EDIT DIGIMAP
|---------------------------------------------------------------------
*/
if ($action === "edit_digi") {
    $id = $_POST["id"];
    $name = trim($_POST["pinName"]);
    $link = trim($_POST["pinLink"]);

    try {
        $pg->query('UPDATE "digiMap" SET 
                       "pinName"=\''.$name.'\',
                       "pinLink"=\''.$link.'\'
                    WHERE id='.$id);
        alert("success", "Đã lưu thay đổi!");
    } catch (Exception $e) {
        alert("error", $e->getMessage());
    }
}

/*
|---------------------------------------------------------------------
| RESET DIGIMAP
|---------------------------------------------------------------------
*/
if ($action === "reset_digi") {
    $id = $_POST["id"];
    try {
        $pg->query('UPDATE "digiMap" SET "joined"=0 WHERE id='.$id);
        alert("success", "Đã reset!");
    } catch (Exception $e) {
        alert("error", $e->getMessage());
    }
}

/*
|---------------------------------------------------------------------
| DELETE DIGIMAP
|---------------------------------------------------------------------
*/
if ($action === "delete_digi") {
    $id = $_POST["id"];
    try {
        $pg->query('DELETE FROM "digiMap" WHERE id='.$id);
        alert("success", "Đã xóa điểm!");
    } catch (Exception $e) {
        alert("error", $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------
| ADD ADMIN
|--------------------------------------------------------------------
*/
if ($action === "add_admin") {

    $fullName   = trim($_POST["fullName"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $password   = trim($_POST["password"] ?? "");
    $adminLevel = intval($_POST["adminLevel"] ?? 0);

    if ($fullName === "" || $email === "" || $password === "")
        alert("error", "Vui lòng nhập đầy đủ thông tin!");

    try {
        // Kiểm tra email tồn tại
        $check = $mysql->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0)
            alert("error", "Email này đã tồn tại!");

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $mysql->prepare("
            INSERT INTO users (fullName, email, password, adminLevel)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$fullName, $email, $hash, $adminLevel]);

        alert("success", "Thêm admin thành công!");
    }
    catch (Exception $e) {
        alert("error", "Lỗi SQL: " . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------
| EDIT ADMIN
|--------------------------------------------------------------------
*/
if ($action === "edit_admin") {

    $id         = $_POST["id"] ?? "";
    $email      = trim($_POST["email"] ?? "");
    $fullName   = trim($_POST["fullName"] ?? "");
    $adminLevel = intval($_POST["adminLevel"] ?? 0);

    if (!$id) alert("error", "Thiếu ID admin!");

    if ($email === "" || $fullName === "")
        alert("error", "Không được để trống!");

    try {
        // kiểm tra email mới trùng email người khác?
        $check = $mysql->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $id]);

        if ($check->rowCount() > 0)
            alert("error", "Email này đã thuộc về admin khác!");

        $stmt = $mysql->prepare("
            UPDATE users 
            SET fullName = ?, email = ?, adminLevel = ?
            WHERE id = ?
        ");

        $stmt->execute([$fullName, $email, $adminLevel, $id]);

        alert("success", "Cập nhật admin thành công!");
    }
    catch (Exception $e) {
        alert("error", "Lỗi SQL: " . $e->getMessage());
    }
}
/*
|--------------------------------------------------------------------
| RESET ADMIN PASSWORD
|--------------------------------------------------------------------
*/
if ($action === "reset_admin_pass") {

    $id = $_POST["id"] ?? null;
    if (!$id) alert("error", "Thiếu ID!");

    try {
        $newPass = password_hash("123456", PASSWORD_BCRYPT);

        $stmt = $mysql->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newPass, $id]);

        alert("success", "Đặt lại mật khẩu thành 123456 thành công!");
    }
    catch (Exception $e) {
        alert("error", "Lỗi: " . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------
| DELETE ADMIN
|--------------------------------------------------------------------
*/
if ($action === "delete_admin") {

    $id = $_POST["id"] ?? null;
    if (!$id) alert("error", "Thiếu ID!");

    // Không cho xóa chính mình
    if (isset($_SESSION["user"]["id"]) && $_SESSION["user"]["id"] == $id)
        alert("error", "Bạn không thể tự xóa chính mình!");

    try {
        $stmt = $mysql->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0)
            alert("error", "Không tìm thấy admin để xóa!");

        alert("success", "Đã xóa admin!");
    }
    catch (Exception $e) {
        alert("error", "Lỗi SQL: " . $e->getMessage());
    }
}


// Nếu action không hợp lệ
alert("error", "Invalid action!");
?>