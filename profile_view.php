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

// Устанавливаем кодировку соединения
$mysqli->set_charset("utf8mb4");

// Определяем текущего пользователя (user_id из сессии)
$current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Определяем id профиля, который необходимо просмотреть (например, ?user_id=2)
$profile_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Если id не передан или совпадает с текущим пользователем, перенаправляем на свою страницу профиля
if ($profile_user_id === 0 || $profile_user_id === $current_user_id) {
    header("Location: profile.php");
    exit;
}

// Загружаем данные пользователя, которого необходимо отобразить
$sql = "SELECT login, email, full_name, location, workplace, bio, avatar FROM users WHERE id = ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $mysqli->error);
}
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("Пользователь не найден.");
}

$stmt->bind_result($login, $email, $fullName, $location, $workplace, $bio, $avatar);
$stmt->fetch();
$profile_user = [
    'login'     => $login,
    'email'     => $email,
    'full_name' => $fullName,
    'location'  => $location,
    'workplace' => $workplace,
    'bio'       => $bio,
    'avatar'    => $avatar
];
$stmt->close();

// Задаём значения по умолчанию, если поля пустые или равны NULL
$login     = !empty($profile_user['login'])     ? $profile_user['login']     : 'UserLogin';
$email     = !empty($profile_user['email'])     ? $profile_user['email']     : 'user@example.com';
$fullName  = !empty($profile_user['full_name']) ? $profile_user['full_name'] : 'Имя не указано';
$location  = !empty($profile_user['location'])  ? $profile_user['location']  : 'Место проживания не указано';
$workplace = !empty($profile_user['workplace']) ? $profile_user['workplace'] : 'Место работы не указано';
$bio       = !empty($profile_user['bio'])       ? $profile_user['bio']       : "Здесь пока ничего не написано.";
$avatar    = !empty($profile_user['avatar'])    ? $profile_user['avatar']    : 'img/pictures/dop/profileAvatarDefault.png';
$avatarPath = "IMG_WEBSITY/USER_PROFILE_IMG/" . $avatar;

// Подсчитываем количество проектов данного пользователя
$sqlCount = "SELECT COUNT(*) as total FROM projects WHERE user_id = ?";
$stmtCount = $mysqli->prepare($sqlCount);
$stmtCount->bind_param("i", $profile_user_id);
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$rowCount = $resultCount->fetch_assoc();
$countProjects = $rowCount['total'] ?? 0;
$stmtCount->close();

// Загружаем проекты пользователя
$sqlProjects = "SELECT id, title, category, short_description, main_image, price, created_at
                FROM projects
                WHERE user_id = ?
                ORDER BY created_at DESC";
$stmtProj = $mysqli->prepare($sqlProjects);
$stmtProj->bind_param("i", $profile_user_id);
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

// Подключаем navbar (содержащий doctype, head, и т.д.)
include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Профиль пользователя</title>
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
    
    /* Правая часть: данные профиля */
    .profile-data {
      display: grid;
      grid-template-rows: 1fr;
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
    
    /* Кнопки (например, "Назад") */
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
      text-decoration: none;
      text-align: center;
    }
    #blue_btn:hover {
      background-color: #53F371;
    }
    
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
    /* Область для изображения проекта */
    .project-image {
      width: 100%;
      height: 200px;
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
    }
    .project-description {
      color: #EBEBEA;
      font-size: 14px;
      font-weight: 400;
      line-height: 17px;
      font-family: 'Montserrat', sans-serif;
      overflow: hidden;
    }
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
    <div class="profile-card">
      <!-- Левая часть: аватар -->
      <div class="profile-avatar">
        <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="User Avatar" onerror="this.onerror=null; this.src='img/pictures/dop/profileAvatarDefault.png';">
      </div>
      
      <!-- Правая часть: данные профиля -->
      <div class="profile-data">
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
          <!-- Кнопка "Назад" для возврата на предыдущую страницу -->
          <!-- <div class="card-actions" style="text-align: center; margin-top: 20px;">
            <a href="javascript:history.back()" class="profile-btn">Назад</a>
          </div> -->
      </div>
    </div>
    
    <!-- Вывод проектов пользователя -->
    <h2 class="projects-heading">Проекты</h2>
    <div class="projects-grid">
      <?php if (!empty($projects)): ?>
        <?php foreach ($projects as $proj): ?>
          <div class="project-card" onclick="goToProject(<?php echo $proj['id']; ?>)">
            <div class="project-image">
              <?php
                $imgPath = !empty($proj['main_image'])
                    ? "/projects/IMG_WEBSITY/PROJECT_MAIN_IMG/" . $proj['main_image']
                    : "img/pictures/dop/noProjectImg.png";
              ?>
              <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="Project Image">
            </div>
            <div class="project-content">
              <div class="project-category">
                <?php echo htmlspecialchars($proj['category']); ?>
              </div>
              <div class="project-title">
                <?php echo htmlspecialchars($proj['title']); ?>
              </div>
              <div class="project-description">
                <?php echo htmlspecialchars(truncateText($proj['short_description'] ?? '', 300)); ?>
              </div>
            </div>
            <?php if (!empty($proj['price'])): ?>
              <!-- <div class="project-price">₽ <?php /*echo htmlspecialchars($proj['price']);*/ ?></div> -->
              <div class="project-price">₽ <?php echo intval($proj['price']); ?></div>

            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width: 100%; color: #EBEBEA;">
          У пользователя пока нет проектов.
        </p>
      <?php endif; ?>
    </div>
    
  </section>
</main>
<script>
// Переход к странице просмотра проекта
function goToProject(projectId) {
  window.location.href = 'view_project.php?id=' + projectId;
}
</script>
</body>



<?php
// index.php
// Подключаем navbar.php, который уже содержит doctype, <head>, <body>, <header> и т.д.
include 'footer.php';
?>



</html>
