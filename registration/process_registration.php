<?php
header('Content-Type: application/json');

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
$login     = trim($_POST['login'] ?? '');
$email     = trim($_POST['email'] ?? '');
$fullname  = trim($_POST['fullname'] ?? '');
$location  = trim($_POST['location'] ?? '');
$workplace = trim($_POST['workplace'] ?? '');
$password  = $_POST['password'] ?? '';

$errors = [];

// Базовая серверная валидация
if (!$login)      $errors['login']     = "Логин обязателен.";
if (!$email)      $errors['email']     = "Email обязателен.";
if (!$fullname)   $errors['fullname']  = "ФИО обязательно.";
if (!$location)   $errors['location']  = "Место проживания обязательно.";
if (!$workplace)  $errors['workplace'] = "Место работы обязательно.";
if (!$password)   $errors['password']  = "Пароль обязателен.";
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors['email'] = "Введите корректный email.";
}
if ($password && strlen($password) < 6) {
  $errors['password'] = "Пароль должен быть не короче 6 символов.";
}

// Проверка на уникальность логина и email, делается отдельно
if (empty($errors)) {
  $stmt = $pdo->prepare("SELECT login, email FROM users WHERE login = :login OR email = :email LIMIT 1");
  $stmt->execute(['login' => $login, 'email' => $email]);
  $existing = $stmt->fetch();
  if ($existing) {
    if (strcasecmp($existing['login'], $login) === 0) {
      $errors['login'] = "Пользователь с таким логином уже существует.";
    }
    if (strcasecmp($existing['email'], $email) === 0) {
      $errors['email'] = "Пользователь с таким email уже существует.";
    }
  }
}

if (!empty($errors)) {
  echo json_encode(['success' => false, 'errors' => $errors]);
  exit;
}

// Хэшируем пароль и сохраняем данные
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO users (login, email, full_name, location, workplace, password_hash, created_at)
                       VALUES (:login, :email, :full_name, :location, :workplace, :password, NOW())");

$result = $stmt->execute([
  'login'     => $login,
  'email'     => $email,
  'full_name' => $fullname,
  'location'  => $location,
  'workplace' => $workplace,
  'password'  => $passwordHash
]);

if ($result) {
  // Получаем ID нового пользователя
  $newUserId = $pdo->lastInsertId();
  
  // При создании пользователя сразу вставляем запись в user_project_stats с 0 проектов и пустым списком категорий.
  $stmtStats = $pdo->prepare("INSERT INTO user_project_stats (user_id, project_count, top_categories) VALUES (:user_id, 0, :top_categories)");
  // Здесь в top_categories сохраняется пустой JSON-массив: "[]"
  $stmtStats->execute([
    'user_id'       => $newUserId,
    'top_categories'=> json_encode([])
  ]);
  
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'errors' => ['general' => 'Ошибка регистрации. Попробуйте позже.']]);
}
?>
