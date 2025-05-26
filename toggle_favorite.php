<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован']);
    exit;
}

if (!isset($_POST['project_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не указан идентификатор проекта']);
    exit;
}

$projectId = intval($_POST['project_id']);
$userId = $_SESSION['user_id'];

$mysqli = new mysqli("localhost", "root", "", "skillmap_db");
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Ошибка подключения к БД']);
    exit;
}

// Проверяем, есть ли уже запись в избранном
$stmt = $mysqli->prepare("SELECT * FROM favorites WHERE user_id = ? AND project_id = ?");
$stmt->bind_param("ii", $userId, $projectId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Если проект уже в избранном – удаляем запись
    $stmt->close();
    $stmt = $mysqli->prepare("DELETE FROM favorites WHERE user_id = ? AND project_id = ?");
    $stmt->bind_param("ii", $userId, $projectId);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'favorite' => false]);
} else {
    // Если нет – добавляем запись в избранное
    $stmt->close();
    $stmt = $mysqli->prepare("INSERT INTO favorites (user_id, project_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $projectId);
    if($stmt->execute()){
      echo json_encode(['success' => true, 'favorite' => true]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении']);
    }
    $stmt->close();
}
$mysqli->close();
?>
