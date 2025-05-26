<?php
session_start();

// Подключение к базе
$mysqli = new mysqli("localhost", "root", "", "skillmap_db");
if ($mysqli->connect_error) {
    die(json_encode(['error' => 'DB Connection Error: ' . $mysqli->connect_error]));
}

// Параметры поиска
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$chosenCats = isset($_GET['cats']) ? (array)$_GET['cats'] : [];
$searchActive = (!empty($searchTerm) || !empty($chosenCats));
$customLimit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;

/**
 * Пример простого способа "обрезки" длинных текстов (при необходимости).
 */
function truncateText($text, $limit = 150) {
    $text = trim($text);
    if (mb_strlen($text) > $limit) {
        return mb_substr($text, 0, $limit) . '...';
    }
    return $text;
}

// Основной запрос: соединяем таблицы users и user_project_stats
$sql = "SELECT u.id, u.login, u.full_name, u.location, u.workplace, u.bio, u.avatar,
               IFNULL(s.project_count, 0) AS project_count,
               IFNULL(s.top_categories, '[]') AS top_categories
        FROM users u
        LEFT JOIN user_project_stats s ON u.id = s.user_id
        WHERE 1=1"; // 1=1 для удобства добавления условий

$binds = [];
$types = "";

// Если хотим исключить текущего пользователя из поиска (необязательно)
if (isset($_SESSION['user_id'])) {
    $sql .= " AND u.id <> ?";
    $binds[] = $_SESSION['user_id'];
    $types .= "i";
}

// Поиск по строке: логин, полное имя, био
if (!empty($searchTerm)) {
    $sql .= " AND (u.login LIKE ? OR u.full_name LIKE ? OR u.bio LIKE ?)";
    $likeTerm = '%' . $searchTerm . '%';
    $binds[] = $likeTerm;
    $binds[] = $likeTerm;
    $binds[] = $likeTerm;
    $types .= "sss";
}

// Поиск по категориям: если top_categories хранится в JSON,
// можно использовать JSON_SEARCH или JSON_CONTAINS.  
// Для простоты ниже — поиск через LIKE (не идеально, но проще).
if (!empty($chosenCats)) {
    foreach ($chosenCats as $cat) {
        $sql .= " AND s.top_categories LIKE ?";
        $binds[] = '%"'.$cat.'"%'; // ищем подстроку вида "DevOps"
        $types .= "s";
    }
}

// Сортируем, например, по дате создания (новые сверху) или по логину
// Здесь для демонстрации сортируем по id убыв. Можно изменить на created_at, если есть поле
$sql .= " ORDER BY u.id DESC";

// Лимит, если нет активного поиска, либо если передан customLimit
if ($customLimit > 0) {
    $sql .= " LIMIT " . $customLimit;
} else if (!$searchActive) {
    // Например, показываем 9 по умолчанию
    $sql .= " LIMIT 100";
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
$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

// Преобразуем поле top_categories из JSON-строки в массив
foreach ($users as &$u) {
    $u['bio'] = truncateText($u['bio'], 300);
    $tc = json_decode($u['top_categories'], true);
    if (!is_array($tc)) {
        $tc = [];
    }
    $u['top_categories'] = $tc;
}
unset($u);

// Возвращаем данные в JSON
header('Content-Type: application/json');
echo json_encode($users);
