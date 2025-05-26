<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not authorized']));
}

$mysqli = new mysqli("localhost", "root", "", "skillmap_db");
if ($mysqli->connect_error) {
    die(json_encode(['error' => 'DB Connection Error: ' . $mysqli->connect_error]));
}

$userId = $_SESSION['user_id'];
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$customLimit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;

// Добавляем 1 AS isFavorite, поскольку все проекты в избранном – избранные
$sql = "SELECT p.id, p.title, p.category, p.short_description, p.main_image, p.price,
               u.login, u.avatar, p.created_at, 1 AS isFavorite
        FROM favorites f
        JOIN projects p ON f.project_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE f.user_id = ?";

$binds = [];
$types = "i";
$binds[] = $userId;

if (!empty($searchTerm)) {
    $sql .= " AND (p.title LIKE ? OR p.short_description LIKE ? OR u.login LIKE ?)";
    $likeTerm = '%' . $searchTerm . '%';
    $binds[] = $likeTerm;
    $binds[] = $likeTerm;
    $binds[] = $likeTerm;
    $types .= "sss";
}

$sql .= " ORDER BY p.created_at DESC";
if ($customLimit > 0) {
    $sql .= " LIMIT " . $customLimit;
}

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die(json_encode(['error' => 'SQL Prepare Error: ' . $mysqli->error]));
}

$stmt->bind_param($types, ...$binds);
$stmt->execute();
$result = $stmt->get_result();
$projects = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

function truncateText($text, $limit = 190) {
    $text = trim($text);
    if (mb_strlen($text) > $limit) {
        return mb_substr($text, 0, $limit) . '...';
    }
    return $text;
}
foreach ($projects as &$project) {
    $project['truncated_description'] = truncateText($project['short_description'], 190);
}
unset($project);

header('Content-Type: application/json');
echo json_encode($projects);
?>
