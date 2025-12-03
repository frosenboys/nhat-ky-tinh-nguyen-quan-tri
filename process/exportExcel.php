<?php
require './db_pg.php';

$type = $_GET["type"] ?? "";
$mission = $_GET["mission"] ?? 1;

/*
|--------------------------------------------------------------------------
| 1. XẾP HẠNG CHI ĐOÀN (UNION)
|--------------------------------------------------------------------------
*/
if ($type === "union") {

    $rows = $pg->query('
        SELECT 
            "unionGroup",
            SUM(points) AS total_points,
            COUNT(*) AS member_count
        FROM "User"
        WHERE "unionGroup" IS NOT NULL AND "unionGroup" <> \'\'
        GROUP BY "unionGroup"
        ORDER BY total_points DESC
    ')->fetchAll();

    $filename = "RankingByUnion.xls";
}

/*
|--------------------------------------------------------------------------
| 2. XẾP HẠNG CÁ NHÂN
|--------------------------------------------------------------------------
*/
elseif ($type === "personal") {

    $rows = $pg->query('
        SELECT "studentId", "fullName", "unionGroup", points
        FROM "User"
        ORDER BY points DESC
    ')->fetchAll();

    $filename = "RankingByPersonal.xls";
}

/*
|--------------------------------------------------------------------------
| 3. XẾP HẠNG THEO NHIỆM VỤ (missionId = 1..5)
|--------------------------------------------------------------------------
*/
elseif ($type === "mission") {

    $mission = max(1, min(5, intval($mission)));

    $column = '"points_' . $mission . '"';

    $rows = $pg->query("
        SELECT \"studentId\", \"fullName\", \"unionGroup\", $column AS mission_point
        FROM \"User\"
        ORDER BY mission_point DESC
    ")->fetchAll();

    $filename = "RankingByMission_$mission.xls";

} else {
    die("INVALID EXPORT TYPE!");
}

/*
|--------------------------------------------------------------------------
| XUẤT EXCEL (.xls dạng HTML table)
|--------------------------------------------------------------------------
*/

// THÊM CHARSET UTF-8 Ở ĐÂY
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// IN BOM UTF-8 ĐỂ EXCEL NHẬN DIỆN ĐÚNG
echo "\xEF\xBB\xBF";

// Có thể thêm meta charset để chắc chắn hơn
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';

echo "<table border='1'>";
$rank = 1;

if ($type === "union") {
    echo "<tr>
            <th>Hạng</th>
            <th>Chi đoàn</th>
            <th>Tổng điểm</th>
            <th>Số thành viên</th>
          </tr>";

    foreach ($rows as $r) {
        echo "<tr>
                <td>{$rank}</td>
                <td>{$r["unionGroup"]}</td>
                <td>{$r["total_points"]}</td>
                <td>{$r["member_count"]}</td>
              </tr>";
        $rank++;
    }
}

else if ($type === "personal") {
    echo "<tr>
            <th>Hạng</th>
            <th>MSĐV</th>
            <th>Họ tên</th>
            <th>Chi đoàn</th>
            <th>Tổng điểm</th>
          </tr>";

    foreach ($rows as $r) {
        echo "<tr>
                <td>{$rank}</td>
                <td>{$r["studentId"]}</td>
                <td>{$r["fullName"]}</td>
                <td>{$r["unionGroup"]}</td>
                <td>{$r["points"]}</td>
              </tr>";
        $rank++;
    }
}

else if ($type === "mission") {
    echo "<tr>
            <th>Hạng</th>
            <th>MSĐV</th>
            <th>Họ tên</th>
            <th>Chi đoàn</th>
            <th>Điểm nhiệm vụ</th>
          </tr>";

    foreach ($rows as $r) {
        echo "<tr>
                <td>{$rank}</td>
                <td>{$r["studentId"]}</td>
                <td>{$r["fullName"]}</td>
                <td>{$r["unionGroup"]}</td>
                <td>{$r["mission_point"]}</td>
              </tr>";
        $rank++;
    }
}

echo "</table>";
exit;
