<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Not authorized");
}
$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['project_id'])) {
        die("No project_id provided");
    }
    $project_id = (int)$_POST['project_id'];

    // Подключение к БД
    $mysqli = new mysqli("localhost", "root", "", "skillmap_db");
    if ($mysqli->connect_error) {
        die("DB error: " . $mysqli->connect_error);
    }

    // Удаляем проект текущего пользователя
    $stmtDel = $mysqli->prepare("DELETE FROM projects WHERE id=? AND user_id=?");
    if (!$stmtDel) {
        error_log("Prepare delete error: " . $mysqli->error);
        die("Prepare delete error: " . $mysqli->error);
    }
    $stmtDel->bind_param("ii", $project_id, $user_id);
    $stmtDel->execute();
    $stmtDel->close();

    // Пересчитываем статистику после удаления
    updateUserProjectStats($mysqli, $user_id);

    // После удаления возвращаемся на profile.php
    header("Location: profile.php");
    exit;
}

/**
 * Функция пересчёта статистики проектов пользователя.
 * Дублируем её определение, аналогичное коду сохранения.
 */
function updateUserProjectStats($mysqli, $user_id) {
    // Подсчитываем общее число проектов
    $stmtCount = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM projects WHERE user_id = ?");
    if (!$stmtCount) {
        error_log("Prepare count error: " . $mysqli->error);
        return;
    }
    $stmtCount->bind_param("i", $user_id);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $row = $resultCount->fetch_assoc();
    $project_count = (int)$row['cnt'];
    $stmtCount->close();

    // Получаем топ-3 категорий
    $stmtTop = $mysqli->prepare("SELECT category, COUNT(*) AS cnt FROM projects WHERE user_id = ? GROUP BY category ORDER BY cnt DESC LIMIT 3");
    if (!$stmtTop) {
        error_log("Prepare top categories error: " . $mysqli->error);
        return;
    }
    $stmtTop->bind_param("i", $user_id);
    $stmtTop->execute();
    $resultTop = $stmtTop->get_result();
    $topCategories = [];
    while ($row = $resultTop->fetch_assoc()) {
        $topCategories[] = $row['category'];
    }
    $stmtTop->close();

    $topCategoriesJson = json_encode($topCategories, JSON_UNESCAPED_UNICODE);

    // Обновляем или вставляем данные в user_project_stats
    $stmtUpsert = $mysqli->prepare("INSERT INTO user_project_stats (user_id, project_count, top_categories) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE project_count = VALUES(project_count), top_categories = VALUES(top_categories)");
    if (!$stmtUpsert) {
        error_log("Prepare upsert error: " . $mysqli->error);
        return;
    }
    $stmtUpsert->bind_param("iis", $user_id, $project_count, $topCategoriesJson);
    $stmtUpsert->execute();
    $stmtUpsert->close();
}
?>
