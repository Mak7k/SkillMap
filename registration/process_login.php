<?php
header('Content-Type: application/json');
session_start(); // Запускаем/продолжаем сессию

// Настройки подключения к БД
$host = 'localhost';
$db   = 'skillmap_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'errors' => ['general' => 'Ошибка подключения к БД.']]);
  exit;
}

// Получаем данные из формы
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

// Валидация
if (!$email) {
  $errors['email'] = "Email обязателен.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors['email'] = "Введите корректный email.";
}

if (!$password) {
  $errors['password'] = "Пароль обязателен.";
}

if (!empty($errors)) {
  echo json_encode(['success' => false, 'errors' => $errors]);
  exit;
}

// Проверяем наличие пользователя
$stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = :email LIMIT 1");
$stmt->execute(['email' => $email]);
$userData = $stmt->fetch();

if (!$userData || !password_verify($password, $userData['password_hash'])) {
  $errors['general'] = "Неверный email или пароль.";
  echo json_encode(['success' => false, 'errors' => $errors]);
  exit;
}

// Если все проверки прошли успешно:
$_SESSION['user_id'] = $userData['id'];
$_SESSION['user_name'] = $userData['full_name'];
$_SESSION['user_email'] = $userData['email'];


echo json_encode(['success' => true]);
