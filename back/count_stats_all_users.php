<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "skillmap_db");
if ($mysqli->connect_error) {
    die("Database connection error: " . $mysqli->connect_error);
}

/**
 * Функция обновляет статистику для пользователя:
 *  - Считает количество проектов
 *  - Выбирает топ-3 категории (по количеству проектов)
 *  - Обновляет или вставляет запись в таблицу user_project_stats
 */
function updateUserProjectStats($mysqli, $user_id) {
    // Считаем количество проектов пользователя
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

    // Получаем топ-3 категории по количеству проектов
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

    // Обновляем статистику в таблице user_project_stats
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

// Если форма отправлена, обновляем статистику для всех пользователей
if (isset($_POST['update_stats'])) {
    $result = $mysqli->query("SELECT id FROM users");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            updateUserProjectStats($mysqli, $row['id']);
        }
        $result->free();
    }
    $mysqli->close();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Обновление статистики пользователей</title>
    <style>
        body { 
            background-color: #212628; 
            color: #EBEBEA; 
            font-family: 'Montserrat', sans-serif;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0;
        }
        .container { text-align: center; }
        .btn { 
            padding: 12px 24px; 
            background-color: #53F371; 
            border: none; 
            border-radius: 8px; 
            color: #212628; 
            font-size: 16px; 
            cursor: pointer; 
            transition: background 0.3s; 
        }
        .btn:hover { background-color: #46C0F0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Обновление статистики проектов пользователей</h1>
        <form method="post">
            <button type="submit" name="update_stats" class="btn">Обновить статистику</button>
        </form>
    </div>
</body>
</html>
