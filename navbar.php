<?php
session_start();

// Проверяем, авторизован ли пользователь
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Имя не задано' : 'Гость';
$userEmail = $isLoggedIn ? $_SESSION['user_email'] ?? 'example@mail.com' : 'guest@example.com';
$userAvatar = '';

// Если пользователь авторизован – подтягиваем данные из БД
if ($isLoggedIn) {
    // Параметры подключения к БД
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'skillmap_db';

    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
    
    // Получаем данные из таблицы users
    $stmt = $conn->prepare("SELECT full_name, email, avatar FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($dbFullName, $dbEmail, $dbAvatar);
    if ($stmt->fetch()) {
        // Если данные есть, используем их
        $userName = $dbFullName ? $dbFullName : $userName;
        $userEmail = $dbEmail ? $dbEmail : $userEmail;
        $userAvatar = $dbAvatar;
    }
    $stmt->close();
    $conn->close();
}

// Если аватарки нет – подставляем изображение по умолчанию
if (empty($userAvatar)) {
    $userAvatar = '/img/pictures/dop/profileAvatarDefault.png';
} else {
    // Если аватарка указана, предполагается, что она хранится в папке uploads/avatars
    $userAvatar = '/uploads/avatars/' . $userAvatar;
}

// Определяем текущую страницу для выделения активного пункта
$currentPage = basename($_SERVER['PHP_SELF']); // например, "index.php"
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Skill Map</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" href="/img/logoShapcka.svg" type="image/x-icon">
    <!-- Подключаем шрифт Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Общий сброс */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #212628;
            font-family: 'Montserrat', sans-serif;
            color: #EBEBEA;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Навигационная панель (header) */
        header.navbar {
            width: 100%;
            height: 100px;
            background-color: #212628;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 60px;
            position: relative;
            z-index: 9;
            position: fixed; top: 0; left: 0;
            gap: 30px;
        }
        
        .navbar-razdel{
            display: flex;
            align-items: center;
            gap: 60px;
        }

        .navbar-logo img {
            height: 56px;
        }

        /* Меню (для больших экранов) */
        .navbar-menu {
            display: flex;
            gap: 40px;
            position: relative;
        }

        .navbar-menu a {
            font-size: 20px;
            font-weight: 400;
            letter-spacing: 0.02em;
            color: #EBEBEA;
            position: relative;
            padding: 8px 0;
            transition: color 0.2s ease;
        }

        .navbar-menu a:hover {
            color: #53F371;
        }

        .navbar-menu a.active {
            color: #53F371;
        }

        /* Подчёркивающая линия (underline) */
        .underline-indicator {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            background-color: #53F371;
            width: 0;
            transition: left 0.3s ease, width 0.3s ease;
            pointer-events: none;
        }

        /* Блок справа (кнопки или иконки) */
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav-btn {
            padding: 16px 32px;
            font-size: 16px;
            font-weight: 700;
            border: 1px solid #53F371;
            border-radius: 16px;
            background: transparent;
            color: #EBEBEA;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background: #53F371;
            color: #212628;
        }

        /* Иконки справа (корзина и профиль) */
        .nav-icon {
            position: relative;
            width: 45px;
            height: 45px;
            cursor: pointer;
            border-radius: 8px;
            background: transparent;
            transition: background 0.3s ease;
            border: 0px solid transparent;
            transition: border-color 0.3s ease, border-width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-icon:hover {
            border-color: #53F371;
            border-width: 1px;
        }

        .icon-vector {
            display: flex;
            justify-content: center;
            align-items: center;
            position: absolute;
            width: 32.5px;
            height: 32.5px;
            border-radius: 4px;
        }

        /* Бургер-иконка (для мобильных) */
        .burger-icon {
            width: 32px;
            height: 24px;
            cursor: pointer;
            display: none;
            position: relative;
        }

        .burger-icon span {
            position: absolute;
            border-radius: 5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #EBEBEA;
            transition: 0.3s;
        }

        .burger-icon span:nth-child(1) {
            top: 0;
        }

        .burger-icon span:nth-child(2) {
            top: 10px;
        }

        .burger-icon span:nth-child(3) {
            top: 20px;
        }

        @media (max-width: 1200px) {
            .navbar {
                padding: 0 25px !important;
            }

            .navbar-menu {
                display: none;
            }

            .nav-icon{
                display: none;
            }

            .burger-icon {
                display: block;
            }
        }

        @media (max-width: 750px) {
            .navbar-logo img{
                height: 30px;
            }

            .nav-btn{
                padding: 8px 16px;
                font-size: 14px;
                font-weight: 600;
            }

            .navbar-actions{
                gap: 15px;
            }

            header.navbar{
                padding: 0 20px !important;
                gap: 15px;
            }

            .menu-group{
                gap:  0px !important;
                padding: 12px 8px !important;
            }

            /* Скрываем кнопки "Вход" и "Регистрация" в шапке для экранов 300-750px */
            .nav-btn.nav-login,
            .nav-btn.nav-register {
                display: none;
            }
        }

        /* Боковое меню (sidebar) */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 10;
            display: none;
        }

        .sidebar {
            position: fixed;
            top: 0;
            right: -300px;
            width: 276px;
            /* height: 100vh; */
            height: 100%;
            background-color:rgba(47, 57, 61, 0.8);
            backdrop-filter: blur(24.36px);
            box-shadow: 0px 2.4px 4.8px rgba(234, 203, 227, 0.25);
            border-radius: 9.6px 0 0 9.6px;
            display: flex;
            flex-direction: column;
            padding: 19.2px 0;
            z-index: 11;
            transition: right 0.3s ease;
        }

        .sidebar.open {
            right: 0;
        }

        .sidebar-profile {
            width: 100%;
            border-bottom: 1.2px solid rgba(246, 247, 249, 0.22);
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
            flex: 1;
        }

        .menu-group {
            width: 100%;
            border-bottom: 1.2px solid rgba(246, 247, 249, 0.22);
            display: flex;
            flex-direction: column;
            gap: 9.6px;
            padding: 10px 0 9.6px 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 9.6px;
            border-radius: 4.8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .item-icon {
            width: 24px;
            height: 24px;
        }

        .item-text {
            margin-top: 2px;
            font-size: 18px;
            font-weight: 400;
            letter-spacing: 1px;
            line-height: 15px;
            color: #F6F7F9;
        }

        /* Кнопка "Выход" (для мобильного меню) */
        .logout-btn {
            margin: 10px auto;
            padding: 12px 32px;
            border: 1px solid #53F371;
            border-radius: 12px;
            background: transparent;
            color: #EBEBEA;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: block;
            text-align: center;
        }

        .logout-btn:hover {
            background: #53F371;
            color: #212628;
        }

        @media (max-width: 380px) {
            .menu-item{
                padding: 10px 10px;
            }
        }
    </style>
</head>

<body>
    <header class="navbar">
        <div class="navbar-razdel">
            <!-- Логотип -->
            <div class="navbar-logo">
                <img src="/img/mainLogo.png" alt="Skill Map Logo">
            </div>
            <!-- Меню (большие экраны) -->
            <nav class="navbar-menu" id="navbarMenu">
                <a href="/index.php" class="<?php if ($currentPage === 'index.php') echo 'active'; ?>">Главная</a>
                <a href="/projects.php" class="<?php if ($currentPage === 'projects.php') echo 'active'; ?>">Проекты</a>
                <a href="/shop.php" class="<?php if ($currentPage === 'shop.php') echo 'active'; ?>">Магазин</a>
                <a href="/community.php" class="<?php if ($currentPage === 'community.php') echo 'active'; ?>">Сообщество</a>
                <span class="underline-indicator" id="underlineIndicator"></span>
            </nav>
        </div>

        <!-- Блок справа -->
        <div class="navbar-actions">
            <?php if ($isLoggedIn): ?>
                <div class="nav-icon" title="История покупок" onclick="window.location='/user_purchases.php';">
                    <div class="icon-vector"><img src="/img/ShopIconNavBar.png" alt="" style="height: 30px;"></div>
                </div>
                <div class="nav-icon" title="Избранное" onclick="window.location='/favorites.php';">
                    <div class="icon-vector"><img src="/img/favoritesIconNavBar3.png" alt="" style="height: 30px;"></div>
                </div>
                <div class="nav-icon" title="Профиль" onclick="window.location='/profile.php';">
                    <div class="icon-vector"><img src="/img/ProfileIconNavBar.png" alt=""></div>
                </div>
            <?php else: ?>
                <!-- Для широких экранов кнопки показываются, а для узких скрываются через CSS -->
                <a href="/registration/login.php" class="nav-btn nav-login">Вход</a>
                <a href="/registration/registration.php" class="nav-btn nav-register">Регистрация</a>
            <?php endif; ?>
            <!-- Бургер-иконка (мобильные) -->
            <div class="burger-icon" onclick="toggleSidebar()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </header>

    <!-- Основной контент страницы -->

    <!-- Оверлей для бокового меню -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Боковое меню (sidebar) -->
    <div class="sidebar" id="sidebar">
        <!-- Часть с профилем (только для бургер-меню) -->
        <?php if ($isLoggedIn): ?>
            <div class="sidebar-profile">
                <img class="avatar" src="<?= htmlspecialchars($userAvatar) ?>" alt="User Avatar">
                <div class="profile-text">
                    <span class="profile-name"><?= htmlspecialchars($userName) ?></span>
                    <span class="profile-email"><?= htmlspecialchars($userEmail) ?></span>
                </div>
            </div>
        <?php else: ?>
            <div class="sidebar-profile">
                <img class="avatar" src="/img/Svg/IconRightBarProfileTopDefault.svg" alt="Guest">
                <div class="profile-text">
                    <span class="profile-name">Гость</span>
                    <span class="profile-email">guest@example.com</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Часть с разделами -->
        <div class="sidebar-menu">
            <!-- Первая группа разделов -->
            <div class="menu-group">
                <div class="menu-item" onclick="window.location='/index.php';">
                    <img src="/img/Svg/IconRightBarHOME.svg" alt="Главная" class="item-icon">
                    <div class="item-text">Главная</div>
                </div>
                <div class="menu-item" onclick="window.location='/projects.php';">
                    <img src="/img/Svg/IconRightBarPROJECTS.svg" alt="Проекты" class="item-icon">
                    <div class="item-text">Проекты</div>
                </div>
                <div class="menu-item" onclick="window.location='/shop.php';">
                    <img src="/img/Svg/IconRightBarSHOP.svg" alt="Магазин" class="item-icon">
                    <div class="item-text">Магазин</div>
                </div>
                <div class="menu-item" onclick="window.location='/community.php';">
                    <img src="/img/Svg/IconRightBarGROUP.svg" alt="Сообщество" class="item-icon">
                    <div class="item-text">Сообщество</div>
                </div>
            </div>

            <!-- Если пользователь не авторизован, добавляем группу с кнопками "Вход" и "Регистрация" -->
            <?php if (!$isLoggedIn): ?>
            <div class="menu-group">
                <div class="menu-item" onclick="window.location='/registration/login.php';">
                    <div class="item-text">Вход</div>
                </div>
                <div class="menu-item" onclick="window.location='/registration/registration.php';">
                    <div class="item-text">Регистрация</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Вторая группа (для авторизованных: профиль, избранное) -->
            <?php if ($isLoggedIn): ?>
            <div class="menu-group">
                <div class="menu-item" onclick="window.location='/profile.php';">
                    <img src="/img/Svg/IconRightBarPROFILE.svg" alt="Профиль" class="item-icon">
                    <div class="item-text">Профиль</div>
                </div>
                <div class="menu-item" onclick="window.location='/favorites.php';">
                    <img src="/img/favoritesIconNavBar3.png" alt="Избранное" class="item-icon">
                    <div class="item-text">Избранное</div>
                </div>

                <div class="menu-item" onclick="window.location='/user_purchases.php';">
                    <img src="/img/ShopIconNavBar.png" alt="Покупки" class="item-icon">
                    <div class="item-text">Покупки</div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Кнопка "Выход" (только для авторизованных) -->
        <?php if ($isLoggedIn): ?>
            <button class="logout-btn" onclick="window.location='/registration/logout.php';">Выход</button>
        <?php endif; ?>
    </div>

    <script>
        // Функция управления боковым меню
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

        // Анимация underline для меню
        const navbarMenu = document.getElementById('navbarMenu');
        const underlineIndicator = document.getElementById('underlineIndicator');
        const menuLinks = navbarMenu.querySelectorAll('a');

        function moveIndicator(link, disableTransition = false) {
            const linkRect = link.getBoundingClientRect();
            const menuRect = navbarMenu.getBoundingClientRect();
            if (disableTransition) {
                underlineIndicator.style.transition = 'none';
            }
            underlineIndicator.style.width = linkRect.width + 'px';
            underlineIndicator.style.left = (linkRect.left - menuRect.left) + 'px';
            if (disableTransition) {
                underlineIndicator.offsetHeight;
                underlineIndicator.style.transition = 'left 0.3s ease, width 0.3s ease';
            }
        }

        // При загрузке страницы устанавливаем позицию underline без анимации
        window.addEventListener('DOMContentLoaded', () => {
            const activeLink = navbarMenu.querySelector('a.active');
            if (activeLink) {
                moveIndicator(activeLink, true);
            }
        });

        // При наведении – перемещаем underline
        menuLinks.forEach(link => {
            link.addEventListener('mouseenter', () => {
                moveIndicator(link);
            });
        });

        // При уходе курсора – возвращаемся к активному пункту
        navbarMenu.addEventListener('mouseleave', () => {
            const activeLink = navbarMenu.querySelector('a.active');
            if (activeLink) moveIndicator(activeLink);
        });

        // При клике – обновляем активный класс и оставляем underline на выбранном пункте
        menuLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                menuLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                moveIndicator(this);
                window.location.href = this.getAttribute('href');
            });
        });
    </script>
</body>
</html>

<!-- </body>
</html> -->

<!-- </body>

</html> -->


<!-- МЕНЯЮ 2-->

<!-- </body>
</html> -->

<!-- </body>

</html> -->


<!-- МЕНЯЮ 3333-->

<!-- МОБИЛЬНАЯ АДАПТАЦИЯ -->