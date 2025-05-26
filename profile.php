<?php
session_start();

// Включаем вывод ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаемся к базе данных
$mysqli = new mysqli("localhost", "root", "", "skillmap_db");
if ($mysqli->connect_error) {
    die("Ошибка подключения к БД: " . $mysqli->connect_error);
}

// Определяем текущего пользователя (например, user_id=1 или из сессии)
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

// Подсчитываем количество проектов текущего пользователя
$sqlCount = "SELECT COUNT(*) as total FROM projects WHERE user_id = ?";
$stmtCount = $mysqli->prepare($sqlCount);
$stmtCount->bind_param("i", $user_id);
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$rowCount = $resultCount->fetch_assoc();
$countProjects = $rowCount['total'] ?? 0; // Если вдруг null, подставляем 0
$stmtCount->close();


// Массив для хранения ошибок для каждого поля
$fieldErrors = [];

// Если нажали "Отмена" — перенаправляемся назад без изменений
if (isset($_POST['cancel'])) {
    header("Location: profile.php");
    exit;
}

// Если нажали "Сохранить" — обрабатываем обновление профиля
if (isset($_POST['save'])) {
    // Получаем данные из формы и удаляем лишние пробелы
    $newLogin    = trim($_POST['login'] ?? '');
    $newFullName = trim($_POST['full_name'] ?? '');
    $newLocation = trim($_POST['location'] ?? '');
    $newEmail    = trim($_POST['email'] ?? '');
    $newWorkplace= trim($_POST['workplace'] ?? '');
    $newBio      = trim($_POST['bio'] ?? '');
    
    // Если поле биографии пустое, подставляем значение по умолчанию
    if (empty($newBio)) {
        $newBio = "Здесь пока ничего не написано.";
    }
    
    // Получаем старое имя файла аватара из скрытого поля формы
    $oldpathAvatar = $_POST['old_avatar'] ?? '';
    
    // Валидация поля "Логин"
    if (empty($newLogin)) {
        $fieldErrors['login'][] = "Логин не может быть пустым.";
    }
    if (strlen($newLogin) < 3) {
        $fieldErrors['login'][] = "Логин должен содержать минимум 3 символа.";
    }
    if (!preg_match("/^[a-zA-Z0-9_]+$/", $newLogin)) {
        $fieldErrors['login'][] = "Логин может содержать только латинские буквы, цифры и символ подчеркивания.";
    }
    
    // Валидация поля "ФИО"
    if (empty($newFullName)) {
        $fieldErrors['full_name'][] = "ФИО не может быть пустым.";
    }
    if (strlen($newFullName) < 3) {
        $fieldErrors['full_name'][] = "ФИО должно содержать минимум 3 символа.";
    }
    if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u", $newFullName)) {
        $fieldErrors['full_name'][] = "ФИО может содержать только буквы, пробелы и дефисы.";
    }
    
    // Валидация поля "Почта"
    if (empty($newEmail)) {
        $fieldErrors['email'][] = "Почта не может быть пустой.";
    }
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'][] = "Неверный формат почты.";
    }
    
    // Валидация поля "Место проживания"
    if (empty($newLocation)) {
        $fieldErrors['location'][] = "Место проживания не может быть пустым.";
    }
    if (strlen($newLocation) < 3) {
        $fieldErrors['location'][] = "Место проживания должно содержать минимум 3 символа.";
    }
    if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ0-9\s,.\-]+$/u", $newLocation)) {
        $fieldErrors['location'][] = "Место проживания содержит недопустимые символы.";
    }
    
    // Валидация поля "Место работы"
    if (empty($newWorkplace)) {
        $fieldErrors['workplace'][] = "Место работы не может быть пустым.";
    }
    if (strlen($newWorkplace) < 2) {
        $fieldErrors['workplace'][] = "Место работы должно содержать минимум 2 символа.";
    }
    if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ0-9\s,.\-]+$/u", $newWorkplace)) {
        $fieldErrors['workplace'][] = "Место работы содержит недопустимые символы.";
    }
    
    // Валидация поля "Биография"
    if (strlen($newBio) > 500) {
        $fieldErrors['bio'][] = "Биография не должна превышать 500 символов.";
    }
    
    // Проверка уникальности логина (если изменён)
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE login = ? AND id != ?");
    $stmt->bind_param("si", $newLogin, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $fieldErrors['login'][] = "Логин уже используется другим пользователем.";
    }
    $stmt->close();
    
    // Проверка уникальности почты (если изменена)
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $newEmail, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $fieldErrors['email'][] = "Электронная почта уже используется другим пользователем.";
    }
    $stmt->close();
    
    // Если ошибок нет, продолжаем обновление данных
    if (empty($fieldErrors)) {
        // Обработка загрузки файла для аватарки
        $avatarFileName = '';
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            // Разрешённые типы файлов
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['avatar']['tmp_name']);
            if (in_array($fileType, $allowedTypes)) {
                // Генерируем уникальное имя файла
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $avatarFileName = uniqid('avatar_', true) . '.' . $ext;
                // Путь для сохранения нового файла
                $uploadPath = __DIR__ . '/IMG_WEBSITY/USER_PROFILE_IMG/' . $avatarFileName;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                    error_log("Новый файл загружен: " . $uploadPath);
                    // Если загрузка прошла успешно, удаляем старый файл (если он задан и не равен файлу по умолчанию)
                    if (!empty($oldpathAvatar) && $oldpathAvatar !== 'img/pictures/dop/profileAvatarDefault.png') {
                        $oldAvatarFullPath = __DIR__ . '/IMG_WEBSITY/USER_PROFILE_IMG/' . $oldpathAvatar;
                        error_log("Путь старого файла: " . $oldAvatarFullPath);
                        if (file_exists($oldAvatarFullPath)) {
                            if (unlink($oldAvatarFullPath)) {
                                error_log("Старый файл удалён: " . $oldAvatarFullPath);
                            } else {
                                error_log("Не удалось удалить старый файл: " . $oldAvatarFullPath);
                            }
                        } else {
                            error_log("Старый файл не найден: " . $oldAvatarFullPath);
                        }
                    }
                } else {
                    $avatarFileName = '';
                }
            }
        }
        
        // Обновляем данные в БД. Если новый аватар успешно загружен, обновляем поле avatar, иначе оставляем прежнее значение
        if (!empty($avatarFileName)) {
            $updateSql = "UPDATE users 
                          SET login=?, email=?, full_name=?, location=?, workplace=?, bio=?, avatar=?
                          WHERE id=?";
            $stmtUpd = $mysqli->prepare($updateSql);
            $stmtUpd->bind_param(
                "sssssssi",
                $newLogin,
                $newEmail,
                $newFullName,
                $newLocation,
                $newWorkplace,
                $newBio,
                $avatarFileName,
                $user_id
            );
        } else {
            $updateSql = "UPDATE users 
                          SET login=?, email=?, full_name=?, location=?, workplace=?, bio=?
                          WHERE id=?";
            $stmtUpd = $mysqli->prepare($updateSql);
            $stmtUpd->bind_param(
                "ssssssi",
                $newLogin,
                $newEmail,
                $newFullName,
                $newLocation,
                $newWorkplace,
                $newBio,
                $user_id
            );
        }
        $stmtUpd->execute();
        $stmtUpd->close();
    
        header("Location: profile.php");
        exit;
    } else {
        // Если есть ошибки, сохраняем введённые данные для повторного отображения формы
        $user = [
            'login'     => $newLogin,
            'email'     => $newEmail,
            'full_name' => $newFullName,
            'location'  => $newLocation,
            'workplace' => $newWorkplace,
            'bio'       => $newBio,
            'avatar'    => $oldpathAvatar
        ];
    }
}

// Если данные пользователя не были переданы через POST (или ошибок нет), загружаем их из БД
if (!isset($user)) {
    $sql = "SELECT login, email, full_name, location, workplace, bio, avatar FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $mysqli->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Задаём значения по умолчанию, если поле пустое или NULL
$login     = !empty($user['login'])     ? $user['login']     : 'UserLogin';
$email     = !empty($user['email'])     ? $user['email']     : 'user@example.com';
$fullName  = !empty($user['full_name']) ? $user['full_name'] : 'Имя не указано';
$location  = !empty($user['location'])  ? $user['location']  : 'Место проживания не указано';
$workplace = !empty($user['workplace']) ? $user['workplace'] : 'Место работы не указано';
// Если биография отсутствует, подставляем значение по умолчанию
$bio       = !empty($user['bio']) ? $user['bio'] : "Здесь пока ничего не написано.";

// Если поле avatar пустое, используем фото по умолчанию
$avatar    = !empty($user['avatar']) ? $user['avatar'] : 'img/pictures/dop/profileAvatarDefault.png';
// Формируем путь к файлу аватарки
$avatarPath = "IMG_WEBSITY/USER_PROFILE_IMG/" . $avatar;

// Определяем, в каком режиме мы находимся (редактирование, если ?edit=1)
$editMode = (isset($_GET['edit']) && $_GET['edit'] == 1);

// Подключаем navbar (содержащий doctype, head, и т.д.)
include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Профиль</title>
  <style>
    /* Общие стили */
    body {
      background-color: #212628;
      margin: 0;
      font-family: 'Montserrat', sans-serif;
      color: #EBEBEA;
    }
    .highlight {
      color: #53F371;
    }
    
    /* Секция профиля */
    .profile-section {
      max-width: 1920px;
      margin: 0 auto;
      padding: 20px;
      margin-top: 50px;
    }
    .profile-heading {
      font-size: 36px;
      font-weight: 600;
      text-align: center;
      margin-bottom: 40px;
    }
    
    /* Карточка профиля */
    .profile-card {
      display: flex;
      gap: 20px;
      background-color: #2F393D;
      border-radius: 20px;
      padding: 20px;
      box-sizing: border-box;
      max-width: 1635px;
      margin: 0 auto;
      height: 582px;
    }
    
    /* Аватарка */
    .profile-avatar {
      position: relative;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .profile-avatar img {
      width: 473px;
      height: 570px;
      object-fit: cover;
      border-radius: 20px;
      display: block;
      background-color: rgba(70, 192, 240, 0.32);
    }
    /* Режим редактирования: блюр для аватара */
    .profile-avatar.edit-avatar img {
      filter: blur(4px);
    }
    /* Блок загрузки аватара. Изменён порядок элементов и позиционирование */
    .avatar-upload-overlay {
      position: absolute;
      bottom: 10px;
      left: 50%;
      transform: translateX(-50%);
      text-align: center;
    }
    .avatar-upload-label {
      color: #EBEBEA;
      cursor: pointer;
      font-family: 'Montserrat', sans-serif;
      font-size: 16px;
      background: rgba(33,38,40,0.8);
      padding: 6px 12px;
      border-radius: 8px;
    }
    #avatar {
      display: none;
    }
    /* Статус загрузки перенесён ниже кнопки, позиционируется статически */
    .avatar-upload-overlay .upload-status {
      position: static;
      margin-top: 8px;
      width: 100%;
      text-align: center;
      color: #EBEBEA;
    }
    .avatar-upload-overlay .progress-bar {
      width: 100%;
      height: 8px;
      background-color: rgba(70, 192, 240, 0.32);
      margin: 5px auto 0;
      border-radius: 4px;
      overflow: hidden;
    }
    .avatar-upload-overlay .progress-fill {
      width: 0;
      height: 100%;
      background-color: #53F371;
      transition: width 1s ease-out;
    }
    
    /* Правая часть: данные профиля */
    .profile-data {
      display: grid;
      grid-template-rows: 1fr auto;
      flex: 1;
      min-width: 300px;
    }
    /* Грид с данными и биографией */
    .profile-info-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      grid-auto-rows: minmax(50px, auto);
      gap: 16px;
      align-content: space-around;
    }
    .info-item {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }
    .info-label {
      font-size: 16px;
      font-weight: 400;
      color: rgba(255,255,255,0.7);
    }
    .info-value {
      font-size: 18px;
      font-weight: 400;
      color: #FFFFFF;
      padding-right: 50px;
    }
    .bio-item {
      grid-column: 1 / -1;
      display: flex;
      flex-direction: column;
      gap: 5px;
    }
    .card-actions {
      margin-top: 20px;
      display: flex;

      flex-wrap: wrap;
      gap: 20px;
      justify-content: flex-end;
    }
    .profile-btn {
      font-family: 'Montserrat', sans-serif;
      background: #53F371;
      color: #212628;
      font-size: 16px;
      font-weight: 500;
      border: none;
      border-radius: 8px;
      padding: 12px 20px;
      cursor: pointer;
      transition: background 0.3s ease;
      width: 25%;
      text-decoration: none;
      text-align: center;
    }
    #red_btn {
      background-color: rgb(228, 31, 31);
    }
    #blue_btn {
      background-color: #46C0F0;
    }
    #blue_btn:hover {
      background-color: #53F371;
    }
    .profile-btn:hover {
      background: #46C0F0;
    }
    
    /* Стили для input/textarea */
    .info-input {
      width: 100%;
      min-height: 40px;
      padding: 8px 16px;
      margin-top: 5px;
      box-sizing: border-box;
      border: 1px solid rgb(207, 211, 213);
      border-radius: 8px;
      background-color: transparent;
      color: #EBEBEA;
      font-family: 'Montserrat', sans-serif;
      font-size: 16px;
      line-height: 22px;
    }
    .info-input:focus {
      outline: 1px solid #46C0F0;
    }
    /* Ограничение ресайза для textarea (биография) */
    textarea.info-input {
      resize: vertical;
      max-height: 120px;
    }
    
    /* Стили для вывода ошибок под полями */
    .field-error {
      color: #e31f1f;
      font-size: 14px;
      margin-top: 5px;
    }
    
    /* Адаптивность */
    @media (max-width: 1335px) {
      .profile-info-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      .profile-btn {
        width: max-content;
      }
    }
    @media (max-width: 1050px) {
      .profile-card {
        flex-direction: column;
        height: auto;
      }
      .profile-avatar {
        width: 100%;
        display: flex;
        justify-content: center;
      }
      .profile-avatar img {
        width: 300px;
        height: auto;
        border-radius: 20px;
        margin-bottom: 20px;
      }
      .profile-data {
        width: 100%;
      }
      .profile-info-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
      }
      .card-actions {
        justify-content: center;
      }
      .profile-btn {
        width: max-content;
      }
      .profile-heading {
        font-size: 32px;
      }
    }

/* Адаптация, */

@media (max-width: 550px) and (min-width: 350px) {
  /* Перестраиваем карточку профиля в колонку */
  .profile-card {
    flex-direction: column;
    padding: 10px;
    height: auto; /* высота подстраивается по содержимому */
  }
  
  /* Аватарка – прижата к левой стороне */
  /* .profile-avatar {
    width: 100%;
    display: flex;
    justify-content: flex-start;
    margin-bottom: 10px;
  } */
  /* .profile-avatar img {
    width: 100%;
    max-width: 180px; 
    height: auto;
    border-radius: 10px;
  } */
  .avatar-upload-overlay{
    width: 100%;
    bottom: 80px;
  }


  /* Блок с данными профиля занимает всю ширину */
  .profile-data {
    width: 100%;
  }
  
  /* Грид данных: два столбца, при этом левая колонка выравнивается влево, правая – вправо */
  .profile-info-grid {
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }
  .profile-info-grid .info-item:nth-child(odd) {
    text-align: left;
  }
  .profile-info-grid .info-item:nth-child(even) {
    text-align: right;
  }

  .info-value{
    padding: 0px;;
  }
  
  /* Уменьшаем размеры шрифтов для меток и значений */
  .info-label {
    font-size: 14px;
  }
  .info-value {
    font-size: 14px;
  }
  
  /* Если требуется, можно задать отдельные размеры для некоторых элементов, например, для биографии */
  .bio-item .info-label,
  .bio-item .info-value {
    font-size: 14px;
  }
  
  /* Блок с кнопками: располагаем кнопки колонной и делаем их на всю ширину */
  .card-actions {
    flex-direction: column;
    gap: 10px;
    width: 100%;
    align-items: center;
  }
  .profile-btn {
    width: 100%;
    max-width: none;
    font-size: 14px;
    padding: 10px;
  }
}




  </style>
</head>
<body>
<main>
  <section class="profile-section">
    <h2 class="profile-heading">
      Каждый <span class="highlight">участник</span> нашего сообщества — 
      <span class="highlight" style="color:#46C0F0">уникален</span>
    </h2>
    <?php if ($editMode): ?>
      <!-- Оборачиваем карточку в форму и передаём старый аватар через скрытое поле -->
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="old_avatar" value="<?php echo htmlspecialchars($user['avatar']); ?>">
    <?php endif; ?>
    
    <div class="profile-card">
      <!-- Левая часть: аватар -->
      <div class="profile-avatar <?php echo $editMode ? 'edit-avatar' : ''; ?>">
        <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="User Avatar" onerror="this.onerror=null; this.src='img/pictures/dop/profileAvatarDefault.png';">
        <?php if ($editMode): ?>
          <div class="avatar-upload-overlay">
            <label for="avatar" class="avatar-upload-label">Загрузить аватар</label>
            <!-- Статус загрузки отображается под кнопкой -->
            <div class="upload-status" id="uploadStatus" style="display: none;">
              <div class="upload-text" id="uploadText"></div>
              <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
              </div>
            </div>
          </div>
          <input type="file" id="avatar" name="avatar">
        <?php endif; ?>
      </div>
      
      <!-- Правая часть: данные профиля -->
      <div class="profile-data">
        <?php if ($editMode): ?>
          <div class="profile-info-grid">
            <div class="info-item">
              <label class="info-label" for="login">Логин</label>
              <input type="text" id="login" name="login" class="info-input" value="<?php echo htmlspecialchars($login); ?>">
              <?php if(isset($fieldErrors['login'])): ?>
                <div class="field-error">
                  <?php foreach($fieldErrors['login'] as $err): ?>
                    <span><?php echo htmlspecialchars($err); ?></span><br>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="info-item">
              <label class="info-label" for="full_name">ФИО</label>
              <input type="text" id="full_name" name="full_name" class="info-input" value="<?php echo htmlspecialchars($fullName); ?>">
              <?php if(isset($fieldErrors['full_name'])): ?>
                <div class="field-error">
                  <?php foreach($fieldErrors['full_name'] as $err): ?>
                    <span><?php echo htmlspecialchars($err); ?></span><br>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="info-item">
              <label class="info-label" for="location">Место проживания</label>
              <input type="text" id="location" name="location" class="info-input" value="<?php echo htmlspecialchars($location); ?>">
              <?php if(isset($fieldErrors['location'])): ?>
                <div class="field-error">
                  <?php foreach($fieldErrors['location'] as $err): ?>
                    <span><?php echo htmlspecialchars($err); ?></span><br>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="info-item">
              <label class="info-label" for="email">Почта</label>
              <input type="email" id="email" name="email" class="info-input" value="<?php echo htmlspecialchars($email); ?>">
              <?php if(isset($fieldErrors['email'])): ?>
                <div class="field-error">
                  <?php foreach($fieldErrors['email'] as $err): ?>
                    <span><?php echo htmlspecialchars($err); ?></span><br>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="info-item">
              <label class="info-label" for="workplace">Место работы</label>
              <input type="text" id="workplace" name="workplace" class="info-input" value="<?php echo htmlspecialchars($workplace); ?>">
              <?php if(isset($fieldErrors['workplace'])): ?>
                <div class="field-error">
                  <?php foreach($fieldErrors['workplace'] as $err): ?>
                    <span><?php echo htmlspecialchars($err); ?></span><br>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="bio-item">
              <label class="info-label" for="bio">Краткая биография</label>
              <textarea id="bio" name="bio" class="info-input" rows="3"><?php echo htmlspecialchars($bio); ?></textarea>
              <?php if(isset($fieldErrors['bio'])): ?>
                <div class="field-error">
                  <?php foreach($fieldErrors['bio'] as $err): ?>
                    <span><?php echo htmlspecialchars($err); ?></span><br>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <div class="card-actions">
            <button type="submit" name="cancel" class="profile-btn" style="background-color:#e31f1f;">Отмена</button>
            <button type="submit" name="save" class="profile-btn" style="background-color:#53F371;">Сохранить</button>
          </div>
        <?php else: ?>
          <div class="profile-info-grid">
            <div class="info-item">
              <span class="info-label">Логин</span>
              <span class="info-value"><?php echo htmlspecialchars($login); ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">ФИО</span>
              <span class="info-value"><?php echo htmlspecialchars($fullName); ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Место проживания</span>
              <span class="info-value"><?php echo htmlspecialchars($location); ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Почта</span>
              <span class="info-value"><?php echo htmlspecialchars($email); ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Место работы</span>
              <span class="info-value"><?php echo htmlspecialchars($workplace); ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Кол-во проектов</span>
              <span class="info-value"><?php echo $countProjects; ?></span>
            </div>
            <div class="bio-item">
              <span class="info-label">Краткая биография</span>
              <span class="info-value"><?php echo htmlspecialchars($bio); ?></span>
            </div>
          </div>
          <div class="card-actions">
            <a href="profile.php?edit=1" class="profile-btn">Изменить</a>
            <button class="profile-btn" id="blue_btn" onclick="window.location='projects/create_project.php';">Добавить проект</button>
            <button class="profile-btn" id="red_btn" onclick="window.location='registration/logout.php';">Выход</button>
          </div>
        <?php endif; ?>
      </div>
    </div>

<!-- ВЫВОД ПРОЕКТОВ ПОЛЬЗОВАТЕЛЯ -->




<?php
// Дополнительно: SQL-запрос, включающий поле price
$sqlProjects = "SELECT id, title, category, short_description, main_image, price
                FROM projects
                WHERE user_id = ?
                ORDER BY created_at DESC";
$stmtProj = $mysqli->prepare($sqlProjects);
$stmtProj->bind_param("i", $user_id);
$stmtProj->execute();
$resultProj = $stmtProj->get_result();
$projects = $resultProj->fetch_all(MYSQLI_ASSOC);
$stmtProj->close();

// Функция для обрезки описания до 300 символов
function truncateText($text, $limit = 300) {
    $text = trim($text);
    if (mb_strlen($text) > $limit) {
        return mb_substr($text, 0, $limit) . '...';
    }
    return $text;
}
?>



<style>
/* Заголовок "Проекты" */
.projects-heading {
  font-size: 55px;
  font-weight: 600;
  line-height: 67px;
  text-align: center;
  margin: 50px 0 40px 0;
  font-family: 'Montserrat', sans-serif;
  color: #EBEBEA;
}

/* Контейнер карточек */
.projects-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 30px;
  margin-bottom: 80px;
}

/* Карточка проекта */
.project-card {
  width: 350px;
  height: 500px;
  background: rgb(47, 57, 61);
  border-radius: 15px;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  overflow: hidden;
  position: relative;
}

/* Красный крестик для удаления */
.delete-icon {
  position: absolute;
  top: 10px;
  right: 10px;
  color: #e31f1f;
  font-size: 20px;
  cursor: pointer;
  z-index: 2;
}

/* Область для изображения проекта */
.project-image {
  width: 100%;
  height: 200px;
  /* background: #fff; */
  border-radius: 15px 15px 0 0;
  overflow: hidden;
}
.project-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Текстовая часть карточки */
.project-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 15px;
  gap: 10px;
}

/* Категория проекта */
.project-category {
  color: #EBEBEA;
  font-size: 14px;
  font-weight: 400;
  font-family: 'Montserrat', sans-serif;
  /* margin-top: 10px; */
}

/* Название проекта */
.project-title {
  color: #46C0F0;
  font-size: 18px;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  /* margin-top: 5px; */
}

/* Описание проекта */
.project-description {
  color: #EBEBEA;
  font-size: 14px;
  font-weight: 400;
  line-height: 17px;
  font-family: 'Montserrat', sans-serif;
  /* margin-top: 10px; */
  overflow: hidden;
}

/* Блок с ценой (прижат к низу карточки) */
.project-price {
  font-size: 16px;
  font-weight: 400;
  color: #53F371;
  margin-top: auto;
  padding: 15px;
  text-align: left;
  width: 100%;
  box-sizing: border-box;
}

.project-card {
  position: relative; /* Для абсолютного позиционирования иконки редактирования */
}
.edit-icon {
  position: absolute;
  bottom: 15px;
  right: 15px;
  z-index: 2;
}

</style>

<h2 class="projects-heading">Проекты</h2>
<div class="projects-grid">
  <?php if (!empty($projects)): ?>
    <?php foreach ($projects as $proj): ?>
      <div class="project-card" onclick="goToProject(<?php echo $proj['id']; ?>)" style="display: flex; flex-direction: column; position: relative;">
        <!-- Кнопка удаления -->
        <div class="delete-icon" onclick="event.stopPropagation(); openDeleteModal(<?php echo $proj['id']; ?>, '<?php echo htmlspecialchars($proj['title']); ?>')">✖</div>
        
        <!-- Область изображения -->
        <div class="project-image">
          <?php
            $imgPath = !empty($proj['main_image'])
                ? "/projects/IMG_WEBSITY/PROJECT_MAIN_IMG/" . $proj['main_image']
                : "img/pictures/dop/noProjectImg.png";
          ?>
          <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="Project Image">
        </div>
        
        <!-- Текстовая информация -->
        <div class="project-content">
          <div class="project-category">
            <?php echo htmlspecialchars($proj['category']); ?>
          </div>
          <div class="project-title">
            <?php echo htmlspecialchars($proj['title']); ?>
          </div>
          <!-- Блок для информации об авторе (с аватаркой и именем) -->
          <!-- <div class="project-author" style="display: flex; align-items: center; gap: 8px;">
            <img src="/img/pictures/dop/profileAvatarDefault.png" alt="Author Avatar" class="author-avatar" style="width:30px; height:30px; border-radius:50%;">
            <span><?php //echo "Автор: " . htmlspecialchars($proj['project_author_name'] ?? ''); ?></span>
          </div> -->
          <div class="project-description">
            <?php echo htmlspecialchars(truncateText($proj['short_description'] ?? '', 300)); ?>
          </div>
        </div>
        
        <!-- Если цена указана, выводим блок цены -->
        <?php if (!empty($proj['price'])): ?>
          <div class="project-price" style="padding: 15px; text-align: left;">
            <span>₽ <?php echo intval($proj['price']); ?></span>
          </div>
        <?php endif; ?>
        
        <!-- Иконка редактирования (всегда отображается) -->
        <a href="/projects/edit_project.php?id=<?php echo $proj['id']; ?>" class="edit-icon" onclick="event.stopPropagation();">
          <img src="/img/edit.svg" alt="Редактировать" style="width:24px; height:24px;">
        </a>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="text-align: center; width: 100%; color: #EBEBEA;">
      У вас пока нет проектов.
    </p>
  <?php endif; ?>
</div>

<script>
function goToProject(projectId) {
  window.location.href = 'view_project.php?id=' + projectId;
}
</script>

<!-- МЕНЯЮ -->


<!-- Форма для удаления проекта (POST на delete_project.php) -->
<form id="deleteForm" action="delete_project.php" method="POST" style="display: none;">
  <input type="hidden" name="project_id" id="deleteProjectId">
</form>

<!-- Модальное окно с оверлеем -->
<div class="modal-overlay" id="modalOverlay" style="display: none;">
  <div class="modal-window" id="modalWindow">
    <p class="modal-text" id="modalText" style="margin-bottom: 20px;"></p>
    <div class="modal-buttons">
      <button type="button" class="modal-btn yes-btn" onclick="confirmDelete()">Да</button>
      <button type="button" class="modal-btn no-btn" onclick="closeModal()">Нет</button>
    </div>
  </div>
</div>

<style>
/* Заголовок "Проекты" */
.projects-heading {
  font-size: 55px;
  font-weight: 600;
  line-height: 67px;
  text-align: center;
  margin: 50px 0 40px 0;
  font-family: 'Montserrat', sans-serif;
  color: #EBEBEA;
}
/* Сетка карточек */
.projects-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 30px;
  margin-bottom: 80px;
}
/* Карточка проекта */
.project-card {
  width: 350px;
  height: 500px;
  background: rgb(47, 57, 61);
  border-radius: 15px;
  display: flex;
  flex-direction: column;
  cursor: pointer; /* чтобы было видно, что карточка кликабельна */
  overflow: hidden;
  position: relative;
}
/* Кнопка удаления (красный крестик) */
.delete-icon {
  position: absolute;
  top: 10px;
  right: 15px;
  color: #e31f1f;
  font-size: 20px;
  cursor: pointer;
  z-index: 2;
}
/* Верхняя часть (изображение) */
.project-image {
  width: 100%;
  height: 200px;
  /* background: #fff; */
  border-radius: 15px 15px 0 0;
  overflow: hidden;
}
.project-image img {
  padding: 10px;
  border-radius: 15px;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
/* Текстовая часть */
.project-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding:  0px 15px 15px 15px;
  gap: 10px;
}
.project-category {
  color: #EBEBEA;
  font-size: 14px;
  font-weight: 400;
  font-family: 'Montserrat', sans-serif;
}
.project-title {
  color: #46C0F0;
  font-size: 18px;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  margin-top: 5px;
}
.project-description {
  color: #EBEBEA;
  font-size: 14px;
  font-weight: 400;
  line-height: 17px;
  font-family: 'Montserrat', sans-serif;
  /* margin-top: 10px; */
}
/* Модальное окно */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-window {
  background: #2F393D;
  padding: 30px 40px;
  border-radius: 15px;
  max-width: 400px;
  text-align: center;
}
.modal-text {
  color: #EBEBEA;
  font-family: 'Montserrat', sans-serif;
  font-size: 16px;
  margin: 0;
}
.modal-buttons {
  display: flex;
  gap: 20px;
  justify-content: center;
}
.modal-btn {
  background: #53F371;
  color: #212628;
  font-size: 16px;
  padding: 8px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s;
  font-family: 'Montserrat', sans-serif;
}
.modal-btn:hover {
  background: #46C0F0;
}
.no-btn {
  background: #e31f1f;
  color: #EBEBEA;
}
.no-btn:hover {
  background: #c32020;
}
</style>




<script>
// Переход к странице просмотра проекта
function goToProject(projectId) {
  // Например, открываем view_project.php?id=...
  window.location.href = 'view_project.php?id=' + projectId;
}

// Открываем модальное окно удаления
function openDeleteModal(projectId, projectTitle) {
  const modalOverlay = document.getElementById('modalOverlay');
  const modalText = document.getElementById('modalText');
  modalText.textContent = "Вы точно хотите удалить проект «" + projectTitle + "»?";
  
  // Сохраняем projectId во временном месте (атрибут data-*)
  modalOverlay.setAttribute('data-project-id', projectId);
  
  modalOverlay.style.display = 'flex';
}

// Закрыть модальное окно
function closeModal() {
  const modalOverlay = document.getElementById('modalOverlay');
  modalOverlay.style.display = 'none';
  modalOverlay.removeAttribute('data-project-id');
}

// При нажатии "Да" в модальном окне
function confirmDelete() {
  const modalOverlay = document.getElementById('modalOverlay');
  const projectId = modalOverlay.getAttribute('data-project-id');
  if (!projectId) return;

  // Устанавливаем project_id в форму
  document.getElementById('deleteProjectId').value = projectId;
  // Отправляем форму
  document.getElementById('deleteForm').submit();
}
</script>


    



    <?php if ($editMode): ?>
      </form>
    <?php endif; ?>
  </section>
</main>

<?php if ($editMode): ?>
<script>
document.getElementById('avatar').addEventListener('change', function(event) {
  var fileInput = event.target;
  if (fileInput.files && fileInput.files[0]) {
    var fileName = fileInput.files[0].name;
    var uploadStatus = document.getElementById('uploadStatus');
    var progressFill = document.getElementById('progressFill');
    var uploadText = document.getElementById('uploadText');
    
    // Показываем блок статуса загрузки
    uploadStatus.style.display = 'block';
    // Сбрасываем прогресс
    progressFill.style.width = '0%';
    uploadText.innerText = '';
    
    // Имитация анимации прогресса
    setTimeout(function() {
      progressFill.style.width = '100%';
    }, 100);
    
    // После завершения анимации выводим сообщение с именем файла
    setTimeout(function() {
      uploadText.innerText = fileName + " успешно загружен";
    }, 1100);
  }
});
</script>
<?php endif; ?>
</body>


<?php
// index.php
// Подключаем navbar.php, который уже содержит doctype, <head>, <body>, <header> и т.д.
include 'footer.php';
?>



</html>
 