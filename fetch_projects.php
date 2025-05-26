<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "skillmap_db");
if ($mysqli->connect_error) {
    die(json_encode(['error' => 'DB Connection Error: ' . $mysqli->connect_error]));
}

$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$chosenCats = isset($_GET['cats']) ? (array)$_GET['cats'] : [];
$searchActive = (!empty($searchTerm) || !empty($chosenCats));

// Если передан параметр limit, используем его (для мультисекции)
$customLimit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;

function truncateText($text, $limit = 300) {
    $text = trim($text);
    if (mb_strlen($text) > $limit) {
        return mb_substr($text, 0, $limit) . '...';
    }
    return $text;
}

$sql = "SELECT p.id, p.title, p.category, p.short_description, p.main_image, p.price,
               u.login, u.avatar, p.created_at, p.user_id
        FROM projects p
        JOIN users u ON p.user_id = u.id
        WHERE (p.price IS NULL)";

$binds = [];
$types = "";

// Исключаем проекты текущего пользователя
if (isset($_SESSION['user_id'])) {
    $sql .= " AND p.user_id <> ?";
    $binds[] = $_SESSION['user_id'];
    $types .= "i";
}

// Если есть строка поиска, добавляем условие
if (!empty($searchTerm)) {
    $sql .= " AND (p.title LIKE ? OR p.short_description LIKE ?)";
    $binds[] = '%' . $searchTerm . '%';
    $binds[] = '%' . $searchTerm . '%';
    $types .= "ss";
}

// Если выбраны категории, добавляем условие IN
if (!empty($chosenCats)) {
    $placeholders = implode(',', array_fill(0, count($chosenCats), '?'));
    $sql .= " AND p.category IN ($placeholders)";
    foreach ($chosenCats as $cat) {
        $binds[] = $cat;
        $types .= "s";
    }
}

// Добавляем сортировку и лимит: если передан limit, используем его
if ($customLimit > 0) {
    $sql .= " ORDER BY p.created_at DESC LIMIT " . $customLimit;
} else {
    if ($searchActive) {
        $sql .= " ORDER BY p.created_at DESC";
    } else {
        $sql .= " ORDER BY p.created_at DESC LIMIT 8";
    }
}

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die(json_encode(['error' => 'SQL Prepare Error: ' . $mysqli->error]));
}
if (!empty($types)) {
    $stmt->bind_param($types, ...$binds);
}
$stmt->execute();
$result = $stmt->get_result();
$projects = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

// Получаем избранные проекты текущего пользователя
$favorites = [];
if (isset($_SESSION['user_id'])) {
    $favQuery = "SELECT project_id FROM favorites WHERE user_id = ?";
    $favStmt = $mysqli->prepare($favQuery);
    if ($favStmt) {
        $favStmt->bind_param("i", $_SESSION['user_id']);
        $favStmt->execute();
        $favResult = $favStmt->get_result();
        while ($favRow = $favResult->fetch_assoc()) {
            $favorites[] = $favRow['project_id'];
        }
        $favStmt->close();
    }
}

// Обработка проектов: обрезаем описание и добавляем статус избранного
foreach ($projects as &$project) {
    $project['truncated_description'] = truncateText($project['short_description'], 200);
    if (isset($_SESSION['user_id'])) {
        $project['isFavorite'] = in_array($project['id'], $favorites);
    }
    unset($project['user_id']);
}
unset($project);

header('Content-Type: application/json');
echo json_encode($projects);
?>
