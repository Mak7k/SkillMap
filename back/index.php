<?php
session_start();

// Проверяем, авторизован ли пользователь
$isLoggedIn = isset($_SESSION['user_id']);

// Если нужны имя и email из сессии, берём их:
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Имя не задано' : 'Гость';
$userEmail = $isLoggedIn ? $_SESSION['user_email'] ?? 'example@mail.com' : 'guest@mail.com';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Skill Map - Навигация с бургер-меню</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Подключаем шрифт Montserrat -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    /* ---------------------------
       Общие стили
       --------------------------- */
    * {
      margin: 0; padding: 0; box-sizing: border-box;
    }
    body {
      background: #212628;
      font-family: 'Montserrat', sans-serif;
      color: #EBEBEA;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    a {
      text-decoration: none;
      color: inherit;
    }

    /* ---------------------------
       Навигационная панель (верхняя)
       --------------------------- */
    header.navbar {
      width: 100%;
      height: 80px;
      background-color: #212628;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 40px;
      position: relative;
      z-index: 2;
    }
    /* Логотип слева */
    .navbar-logo {
      display: flex;
      align-items: center;
    }
    .navbar-logo img {
      height: 56px;
    }
    /* Обычное меню (для больших экранов) */
    .navbar-menu {
      display: flex;
      gap: 40px;
    }
    .navbar-menu a {
      font-size: 20px;
      font-weight: 400;
      letter-spacing: 2%;
      color: #EBEBEA;
      transition: color 0.2s ease;
    }
    .navbar-menu a:hover {
      color: #53F371;
    }
    .navbar-menu a.active {
      color: #53F371;
      text-decoration: underline;
      text-underline-offset: 3px;
    }

    /* Блок справа (иконки/кнопки/бургер) */
    .navbar-actions {
      display: flex;
      align-items: center;
      gap: 20px;
    }
    /* Кнопки "Вход"/"Регистрация" */
    .nav-btn {
      padding: 16px 32px;
      font-size: 16px;
      font-weight: 700;
      border: 1px solid #53F371;
      border-radius: 16px;
      background: transparent;
      color: #EBEBEA;
      cursor: pointer;
      transition: all 0.3s;
    }
    .nav-btn:hover {
      background: #53F371;
      color: #212628;
    }
    /* Иконки корзины и профиля */
    .nav-icon {
      position: relative;
      width: 40px;
      height: 40px;
      cursor: pointer;
      border: 1px solid #53F371;
      border-radius: 8px;
      background: transparent;
      transition: background 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .nav-icon:hover {
      background: rgba(83, 243, 113, 0.2);
    }
    .icon-vector {
      position: absolute;
      width: 32.5px;
      height: 32.5px;
      background: rgb(70,192,240); 
      border-radius: 4px;
    }

    /* Иконка "бургер" (для мобильных) */
    .burger-icon {
      width: 32px;
      height: 24px;
      cursor: pointer;
      display: none; /* показывается только на мобильных */
      position: relative;
    }
    .burger-icon span {
      position: absolute;
      left: 0;
      width: 100%;
      height: 4px;
      background: #EBEBEA;
      transition: 0.3s;
    }
    .burger-icon span:nth-child(1) { top: 0; }
    .burger-icon span:nth-child(2) { top: 10px; }
    .burger-icon span:nth-child(3) { top: 20px; }

    @media (max-width: 992px) {
      .navbar-menu {
        display: none; /* прячем обычное меню на мобильных */
      }
      .burger-icon {
        display: block;
      }
    }

    /* ---------------------------
       Боковое меню (sidebar)
       --------------------------- */
    .sidebar-overlay {
      position: fixed;
      top: 0; right: 0; bottom: 0; left: 0;
      background: rgba(0, 0, 0, 0.4);
      z-index: 10;
      display: none; 
    }
    .sidebar {
      position: fixed;
      top: 0;
      right: -300px; 
      width: 276px;
      height: 100vh;
      background: linear-gradient(
        199.24deg, 
        rgba(70,192,240,0.26) 3.541%, 
        rgba(47,57,61,0.26) 30.297%, 
        rgba(47,57,61,0.26) 63.049%, 
        rgba(83,243,113,0.26) 104.936%
      );
      backdrop-filter: blur(24.36px);
      box-shadow: 0px 2.4px 4.8px rgba(234,203,227,0.25);
      border-radius: 9.6px 0 0 9.6px;
      display: flex;
      flex-direction: column;
      padding: 19.2px 0;
      gap: 9.6px;
      z-index: 11;
      transition: right 0.3s ease;
    }
    .sidebar.open {
      right: 0; 
    }
    .sidebar-profile {
      width: 100%;
      border-bottom: 1.2px solid rgba(246,247,249,0.22);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 0 19.2px 19.2px 19.2px;
      gap: 14.4px;
      margin: 9.6px 0;
    }
    .sidebar-profile .avatar {
      width: 67.2px;
      height: 67.2px;
      border-radius: 50%;
      object-fit: cover;
      margin: 14.4px 0;
      background: #999; 
    }
    .profile-text {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
    }
    .profile-text .profile-name {
      font-size: 16.8px;
      font-weight: 400;
      line-height: 24px;
      color: #F6F7F9;
      text-align: center;
    }
    .profile-text .profile-email {
      font-size: 14.4px;
      font-weight: 400;
      line-height: 24px;
      color: #F0F0F0;
      text-align: center;
    }
    .sidebar-menu {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 9.6px;
      padding: 0 9.6px;
    }
    .menu-group {
      width: 100%;
      border-bottom: 1.2px solid rgba(246,247,249,0.22);
      display: flex;
      flex-direction: column;
      gap: 9.6px;
      padding: 10px 0 9.6px 0;
    }
    .menu-item {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 8px;
      padding: 16px 9.6px;
      border-radius: 4.8px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .menu-item:hover {
      background: rgba(255,255,255,0.05);
    }
    .item-icon {
      width: 24px;
      height: 24px;
      background: #888; /* замените на иконку или svg */
    }
    .item-text {
      font-size: 16.8px;
      font-weight: 500;
      line-height: 24px;
      color: #F6F7F9;
    }

  </style>
</head>
<body>
<header class="navbar">
  <!-- Логотип -->
  <div class="navbar-logo">
    <img src="img/logo.png" alt="Skill Map Logo">
  </div>

  <!-- Обычное меню (большие экраны) -->
  <nav class="navbar-menu">
    <a href="index.php" class="active">Главная</a>
    <a href="projects.php">Проекты</a>
    <a href="shop.php">Магазин</a>
    <a href="community.php">Сообщество</a>
  </nav>

  <!-- Блок справа -->
  <div class="navbar-actions">
    <?php if ($isLoggedIn): ?>
      <!-- Иконки корзины и профиля (если авторизован) -->
      <div class="nav-icon" title="Корзина">
        <div class="icon-vector"></div>
      </div>
      <div class="nav-icon" title="Профиль">
        <div class="icon-vector"></div>
      </div>
    <?php else: ?>
      <!-- Кнопки "Вход" / "Регистрация" (если не авторизован) -->
      <a href="registration/login.php" class="nav-btn nav-login">Вход</a>
      <a href="registration/registration.php" class="nav-btn nav-register">Регистрация</a>
    <?php endif; ?>

    <!-- Бургер-иконка (мобильные) -->
    <div class="burger-icon" onclick="toggleSidebar()">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>
</header>

<main style="padding: 20px;">
  <h1>Главная страница (пример)</h1>
  <p>Здесь контент вашей страницы.</p>
</main>

<!-- Оверлей для бокового меню -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Боковое меню -->
<div class="sidebar" id="sidebar">
  <?php if ($isLoggedIn): ?>
    <!-- Профиль пользователя -->
    <div class="sidebar-profile">
      <img class="avatar" src="img/avatar.jpg" alt="User Avatar">
      <div class="profile-text">
        <span class="profile-name"><?= htmlspecialchars($userName) ?></span>
        <span class="profile-email"><?= htmlspecialchars($userEmail) ?></span>
      </div>
    </div>
  <?php else: ?>
    <!-- Если не авторизован, можно вывести заглушку или скрыть этот блок -->
    <div class="sidebar-profile">
      <img class="avatar" src="img/avatar.jpg" alt="Guest">
      <div class="profile-text">
        <span class="profile-name">Гость</span>
        <span class="profile-email">guest@example.com</span>
      </div>
    </div>
  <?php endif; ?>

  <!-- Основное меню -->
  <div class="sidebar-menu">
    <!-- Первая группа -->
    <div class="menu-group">
      <div class="menu-item">
        <div class="item-icon"></div>
        <div class="item-text">Главная</div>
      </div>
      <div class="menu-item">
        <div class="item-icon"></div>
        <div class="item-text">Проекты</div>
      </div>
      <div class="menu-item">
        <div class="item-icon"></div>
        <div class="item-text">Магазин</div>
      </div>
      <div class="menu-item">
        <div class="item-icon"></div>
        <div class="item-text">Сообщество</div>
      </div>
    </div>
    <!-- Вторая группа -->
    <div class="menu-group">
      <div class="menu-item">
        <div class="item-icon"></div>
        <div class="item-text">Профиль</div>
      </div>
      <div class="menu-item">
        <div class="item-icon"></div>
        <div class="item-text">Корзина</div>
      </div>
    </div>
  </div>
</div>

<script>
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');

  function toggleSidebar() {
    if (sidebar.classList.contains('open')) {
      sidebar.classList.remove('open');
      overlay.style.display = 'none';
    } else {
      sidebar.classList.add('open');
      overlay.style.display = 'block';
    }
  }
</script>
</body>
</html>
