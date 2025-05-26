<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Не авторизован");
}

// Подключаем navbar в самом верху
include 'navbar.php';

$user_id = (int)$_SESSION['user_id'];

// Подключение к БД
$mysqli = new mysqli("localhost", "root", "", "skillmap_db");
if ($mysqli->connect_error) {
    die("Database connection error: " . $mysqli->connect_error);
}

// Извлекаем купленные проекты
// LEFT JOIN: если project_id отсутствует в таблице projects, значит проект удалён
$sql = "
    SELECT up.*,
           p.id AS project_exists
      FROM user_purchases up
      LEFT JOIN projects p ON up.project_id = p.id
     WHERE up.buyer_id = ?
  ORDER BY up.purchased_at DESC
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$purchases = [];
while ($row = $res->fetch_assoc()) {
    $purchases[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Купленные проекты</title>
  <style>
    body {
      margin: 0;
      background: #212628;
      color: #EBEBEA;
      font-family: 'Montserrat', sans-serif;
    }
    .container {
      width: 100%;
      max-width: 1920px;
      margin: 0 auto;
      padding: 40px;
    }
    .page-title {
      font-size: 40px;
      font-weight: 600;
      line-height: 49px;
      text-align: center;
      margin-top: 80px;
      margin-bottom: 40px;
      padding-top: 20px;
    }
    .purchases-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 40px;
      margin-bottom: 80px;
    }
    /* Карточка */
    .purchase-card {
      width: 1076px;
      min-height: 193px;
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
      padding: 10px 30px 10px 10px;
      background: rgb(39,47,50);
      border-radius: 15px;
      cursor: pointer;
      transition: background 0.3s;
    }
    .purchase-card:hover {
      background: rgba(39,47,50,0.8);
    }
    .purchase-left {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 10px;
    }
    /* Блок изображения */
    .purchase-img {
      width: 245px;
      height: 173px;
      border-radius: 10px;
      overflow: hidden;
      position: relative;
      flex-shrink: 0;
    }
    .purchase-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    /* Если проект удалён - пелена + красный текст */
    .img-overlay {
      position: relative;
      width: 100%;
      height: 100%;
    }
    .img-overlay img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0.3;
    }
    .deleted-text {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: red;
      font-size: 18px;
      font-weight: 600;
      text-align: center;
    }
    /* Информация: название + автор */
    .purchase-info {
      display: flex;
      flex-direction: column;
      gap: 20px;
      padding: 10px;
    }
    .purchase-title {
      font-size: 18px;
      font-weight: 500;
      color: #FFFFFF;
      margin-top: 20px;
    }
    .purchase-author {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 18px;
      font-weight: 400;
      color: rgba(255,255,255,0.8);
    }
    .author-avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      object-fit: cover;
    }

    /* Правая часть */
    .purchase-right {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 30px;
      margin-right: 20px;
    }
    .purchase-price {
      font-size: 18px;
      font-weight: 400;
      color: rgb(83,243,113);
      margin-right: 30px;
    }
    /* Кнопка скачивания */
    .download-btn {
      width: 45px;
      height: 45px;
      cursor: pointer;
      flex-shrink: 0;
    }
    .download-btn img {
      width: 45px;
      height: 45px;
      object-fit: cover;
    }

    /* Ноутбуки (1000px-1400px) */
    @media (max-width: 1400px) and (min-width: 1000px) {
      .purchase-card {
        width: 90%;
      }
    }
    /* Планшеты (450px-1000px) */
    @media (max-width: 1000px) and (min-width: 380px) {
      .purchase-card {
        width: 95%;
        flex-direction: column;
        padding: 20px;
        height: auto;
        
      }
      .purchase-left {
        flex-direction: column;
        width: 100%;
      }
      .purchase-img {
        width: 100%;
        height: 200px;
      }
      .purchase-info {
        width: 100%;
        padding: 10px 0px;
        align-items: flex-start;
      }
      .purchase-right {
        margin-top: 20px;
        align-self: flex-start;
        flex-direction: row-reverse;
        gap: 20px;
      }

      .purchase-author {

        gap: 20px;
      }
    }
    /* Смартфоны (до 450px) */
    @media (max-width: 380px) {
      .page-title {
        font-size: 28px;
      }
      .purchase-card {
        width: 95%;
        flex-direction: column;
        padding: 20px;
      }
      /* Картинка сверху, остальное снизу */
      .purchase-left {
        flex-direction: column;
        width: 100%;
      }
      .purchase-img {
        width: 100%;
        height: 180px;
        margin-bottom: 10px;
      }
      .purchase-info {
        width: 100%;
        align-items: flex-start;
      }
      .purchase-title {
        font-size: 16px;
      }
      .purchase-author {
        font-size: 12px;
        gap: 20px;
      }
      .purchase-price {
        font-size: 14px;
      }
      .download-btn {
        width: 35px;
        height: 35px;
      }
      .download-btn img {
        width: 35px;
        height: 35px;
      }
    }



    .loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0,0,0,0.7);
  z-index: 99999;
  display: none;
  justify-content: center;
  align-items: center;
}
.spinner {
  width: 60px;
  height: 60px;
  border: 6px solid #f3f3f3;
  border-top: 6px solid #53F371;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}


  </style>
</head>
<body>
  <div class="page-title">Купленные проекты</div>

  <div class="purchases-container">
    <?php if (empty($purchases)): ?>
      <div style="font-size: 18px; margin-top: 40px;">У вас еще нет купленных проектов</div>
    <?php else: ?>
      <?php foreach ($purchases as $purchase): 
            // Проверяем, существует ли проект
            $deleted = is_null($purchase['project_exists']);
            // Ссылка для перехода, если проект не удалён
            $projectLink = "view_project.php?id=" . (int)$purchase['project_id'];

            // Авторский аватар, если хранится в user_purchases
            // Допустим, у нас есть поле project_author_avatar. Если нет, используем заглушку
            $authorAvatar = "/img/pictures/dop/profileAvatarDefault.png"; 
            // Если в таблице user_purchases хранится поле project_author_avatar:
            if (!empty($purchase['project_author_avatar'])) {
                $authorAvatar = $purchase['project_author_avatar'];
            }
      ?>
        <div class="purchase-card"
             <?php if (!$deleted): ?>
               onclick="window.location.href='<?php echo htmlspecialchars($projectLink); ?>'"
             <?php else: ?>
               style="cursor: default;"
             <?php endif; ?>>
          
          <!-- Левая часть -->
          <div class="purchase-left">
            <div class="purchase-img">
              <?php if ($deleted): ?>
                <div class="img-overlay">
                  <img src="<?php echo htmlspecialchars($purchase['project_main_image']); ?>" alt="Project Image">
                  <div class="deleted-text">Проект удален</div>
                </div>
              <?php else: ?>
                <img src="<?php echo htmlspecialchars($purchase['project_main_image']); ?>" alt="Project Image">
              <?php endif; ?>
            </div>
            <div class="purchase-info">
              <div class="purchase-title">
                <?php echo htmlspecialchars($purchase['project_title']); ?>
              </div>
              <div class="purchase-author">
                <img src="<?php echo htmlspecialchars($authorAvatar); ?>" alt="Author Avatar" class="author-avatar">
                <span><?php echo "" . htmlspecialchars($purchase['project_author_name']); ?></span>
              </div>
            </div>
          </div>

          <!-- Правая часть -->
          <div class="purchase-right">
            <div class="purchase-price">
            ₽ <?php echo intval($purchase['purchase_price']); ?>
            </div>
            <!-- Кнопка скачивания -->
            <div class="download-btn" onclick="downloadAll(event, '<?php echo $purchase['id']; ?>')">
              <img src="/img/down.png" alt="download">
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <script>
// Функция для скачивания архива с отслеживанием завершения формирования через cookie
function downloadAll(event, purchaseId) {
  event.stopPropagation();
  
  // Показываем загрузочный оверлей (предположим, элемент с id="loadingOverlay" уже добавлен в HTML)
  const loadingOverlay = document.getElementById('loadingOverlay');
  if (loadingOverlay) {
    loadingOverlay.style.display = 'flex';
  }
  
  // Создаем невидимый iframe для запуска скачивания
  let iframe = document.createElement('iframe');
  iframe.style.display = 'none';
  iframe.src = "/download_all.php?purchase_id=" + purchaseId;
  document.body.appendChild(iframe);
  
  // Запускаем опрос cookie каждые 500 мс
  let checkInterval = setInterval(() => {
    const cookies = document.cookie.split(';').map(c => c.trim());
    const fileCookie = cookies.find(c => c.startsWith("fileDownload="));
    if (fileCookie && fileCookie.split("=")[1] === "true") {
      // Скрываем оверлей
      if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
      }
      clearInterval(checkInterval);
      // Удаляем cookie, чтобы не мешало будущим скачиваниям
      document.cookie = "fileDownload=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
      // Удаляем iframe через небольшую задержку
      setTimeout(() => { iframe.remove(); }, 1000);
    }
  }, 500);
}
</script>




<!-- Загрузочный оверлей с анимированным спиннером -->
<div id="loadingOverlay" class="loading-overlay">
  <div class="spinner"></div>
</div>
<!-- ИСПРАВЛЯЮ  -->
</body>


<?php
// index.php
// Подключаем navbar.php, который уже содержит doctype, <head>, <body>, <header> и т.д.
include 'footer.php';
?>



</html>
