<?php
session_start();
include 'navbar.php';

// Список всех категорий (для фильтра по top_categories)
$allCategories = [
  "Разработка ПО",
  "Веб-разработка",
  "Мобильная разработка",
  "Геймдев",
  "Дизайн",
  "3Д-дизайн",
  "Data Science",
  "Кибербезопасность",
  "DevOps",
  "ИИ"
];

/**
 * Функция для вывода скелетон-карточек пользователей (по умолчанию 9).
 * Так мы изначально показываем 3 ряда по 3 "пустых" карточки.
 */
function outputSkeletons($count = 9) {
  for ($i = 0; $i < $count; $i++): ?>
    <div class="user-card skeleton">
      <div class="user-content">
        <div class="user-header">
          <div class="user-avatar" style="background:#444;"></div>
          <div class="user-info">
            <div class="user-login" style="width:150px; height:20px; background:#444; margin-bottom:8px;"></div>
            <div class="user-workplace" style="width:180px; height:16px; background:#444;"></div>
          </div>
        </div>
        <div class="user-bio" style="margin-top:12px; width:100%; height:50px; background:#444;"></div>
        <div class="user-stats" style="margin-top:auto;">
          <div class="user-project-count" style="height:16px; background:#444; width:120px; margin-bottom:8px;"></div>
          <div class="user-top-cats" style="height:16px; background:#444; width:200px;"></div>
        </div>
      </div>
    </div>
  <?php endfor;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Сообщество</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Иконки dashicons, при необходимости -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dashicons/3.8.1/css/dashicons.min.css">
  <style>
    body {
      margin: 0;
      background: #212628;
      color: #EBEBEA;
      font-family: 'Montserrat', sans-serif;
    }
    .container {
      max-width: 1920px;
      margin: 0 auto;
      padding: 40px;
    }
    .top-heading {
      text-align: center;
      font-size: 40px;
      font-weight: 600;
      line-height: 49px;
      margin-top: 60px;
      margin-bottom: 20px;
    }
    .top-subheading {
      text-align: center;
      font-size: 36px;
      font-weight: 600;
      line-height: 44px;
      margin-bottom: 40px;
    }
    /* Поисковый модуль */
    .search-module {
      width: 1607px;
      max-width: 90%;
      margin: 0 auto 40px auto;
      background: rgba(47,57,61,0.5);
      border-radius: 15px;
      padding: 24px 53px;
      display: flex;
      flex-direction: column;
      gap: 20px;
      position: relative;
    }
    .search-row {
      display: flex;
      flex-direction: row;
      align-items: start;
      gap: 16px;
    }
    .search-box {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 24px;
      background: rgb(242,244,248);
      border-radius: 12px;
      flex: 1;
      min-height: 74px;
    }
    .search-box input[type="text"] {
      background: transparent;
      border: none;
      outline: none;
      font-size: 20px;
      font-weight: 500;
      color: #000;
      width: 100%;
    }
    .search-icon {
      width: 24px;
      height: 25px;
      background: url('/img/pictures/dop/IconSearch.png') no-repeat center;
      background-size: contain;
      flex-shrink: 0;
    }
    .dropdown-box {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding: 24px;
      background: rgb(242,244,248);
      border-radius: 12px;
      min-height: 74px;
      width: 480px;
      cursor: pointer;
    }
    .dropdown-box-text {
      font-size: 20px;
      color: #000;
    }
    .dropdown-box-caret {
      width: 18px;
      height: 6px;
      background: url('/img/pictures/dop/CaretIcon.svg') no-repeat center;
      background-size: contain;
    }
    .dropdown-content {
      position: absolute;
      top: 75px;
      left: 0;
      width: 480px;
      background: #f2f4f8;
      border-radius: 12px;
      padding: 10px;
      display: none;
      flex-direction: column;
      gap: 8px;
      z-index: 999;
    }
    .dropdown-box.open .dropdown-content {
      display: flex;
    }
    .checkbox-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 16px;
      color: #000;
    }
    .checkbox-item input[type="checkbox"] {
      width: 16px;
      height: 16px;
    }
    .btns-container {
      display: flex;
      gap: 10px;
      justify-content: center;
    }
    .search-btn, .reset-btn {
      width: 114px;
      min-height: 74px;
      border-radius: 12px;
      border: none;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.3s;
      font-family: 'Montserrat', sans-serif;
    }
    .search-btn {
      background: rgb(83,243,113);
      color: #212628;
    }
    /* .search-btn.modified, .search-btn:hover {
      background: rgb(70,192,240);
      color: #212628;
    } */
    .search-btn:hover {
      background: rgb(70,192,240);
      color: #212628;
    }
    .search-btn.modified {
      /* background-color: rgba(83, 243, 112, 0.75); */
    }
    .reset-btn {
      background-color: #e31f1f;
      color: #212628;
    }
    .reset-btn:hover {
      background-color: #c22020;
    }
    .tags-row {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .tag {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 4px;
      padding: 8px 12px;
      border: 2px solid rgb(232,234,238);
      border-radius: 4px;
      background: #fff;
      color: #000;
      font-size: 14px;
      font-weight: 400;
    }
    .tag .cross-icon {
      width: 10px;
      height: 10px;
      background: url('/img/pictures/dop/CrossIcon.png') no-repeat center;
      background-size: contain;
      cursor: pointer;
    }
    .category-heading {
      text-align: center;
      font-size: 40px;
      font-weight: 600;
      line-height: 49px;
      margin: 20px 0 40px 0;
      color: #F2F4F8;
    }
    /* Сетка карточек пользователей */
    .users-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      margin-bottom: 40px;
    }
    /* Карточка пользователя */
    .user-card {
      width: 514px;
      height: 300px;
      background: rgb(47,57,61);
      border-radius: 15px;
      display: flex;
      flex-direction: column;
      padding: 24px;
      box-sizing: border-box;
      position: relative;
      cursor: pointer; /* Вся карточка кликабельна */
    }
    .user-content {
      display: flex;
      flex-direction: column;
      height: 100%;
      justify-content: flex-start;
    }
    .user-header {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 16px;
    }
    .user-avatar {
      width: 96px;
      height: 96px;
      border-radius: 50%;
      background: #ccc;
      flex-shrink: 0;
      overflow: hidden;
    }
    .user-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .user-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .user-login {
      color: #fff;
      font-size: 23px;
      font-weight: 500;
      line-height: 28px;
    }
    .user-workplace {
      color: rgba(255,255,255,0.7);
      font-size: 16px;
      font-weight: 400;
      line-height: 20px;
    }
    .user-bio {
      margin-top: 21px;
      color: #EBEBEA;
      font-size: 14px;
      font-weight: 400;
      line-height: 17px;
    }
    .user-stats {
      margin-top: auto;
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 0;
    }
    .user-project-count {
      color: #46C0F0;
      font-size: 14px;
      font-weight: 500;
    }
    .user-top-cats {
      color: #53F371;
      font-size: 14px;
      font-weight: 400;
    }
    /* Скелетоны */
    .skeleton {
      background: #333;
      filter: blur(2px);
      animation: pulse 0.4s infinite;
    }
    @keyframes pulse {
      0% { opacity: 0.6; }
      50% { opacity: 1; }
      100% { opacity: 0.6; }
    }
    /* Мобильная адаптация */
    @media (max-width: 1200px) {
      .container {
        padding: 20px;
      }
      .search-module {
        width: 100%;
        padding: 16px 20px;
      }
      .search-row {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
      }
      .search-box, .dropdown-box, .btns-container button {
        width: 100%;
        min-height: 60px;
        padding: 16px;
      }
      .dropdown-box {
        width: 100%;
      }
      .dropdown-content {
        width: 100%;
        top: 60px;
      }
      .btns-container {
        justify-content: center;
      }
      .search-btn, .reset-btn {
        font-size: 14px;
        width: 48%;
      }
      .top-heading {
        font-size: 28px;
      }
      .top-subheading {
        font-size: 20px;
      }
      .tags-row {
        justify-content: center;
      }
      .user-card {
        width: 100%;
        height: auto;
      }
      
    }




    @media (max-width: 500px){
      .dropdown-box-text{
        font-size: 18px;
      }

      .btns-container button{
        font-size: 18px;
      }

      .top-heading{
        line-height: 150%;
        padding-top: 20px;
        /* padding-top: 100px; */
      }

      .top-subheading{
        line-height: normal;
        margin-bottom: 20px;
      }

      .category-heading{
        margin: 20px 0 15px 0;

      }
      
      .search-box input[type="text"]{
        font-size: 18px;
      }
      .search-module{
        max-width: 100%;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <div class="top-heading">Наше <span style="color: rgb(83,243,113);">сообщество</span> – <span style="color: #46C0F0;"> команда</span> профессионалов</div>
  <div class="top-subheading"><span style="color: #46C0F0;"> Найди</span> специалистов по категориям и интересам</div>

  <!-- Форма поиска (аналогична странице проектов) -->
  <form class="search-module" id="searchForm">
    <div class="search-row">
      <div class="search-box">
        <div class="search-icon"></div>
        <input type="text" name="q" placeholder="Кого ищем?" value="">
      </div>
      <div class="dropdown-box" id="catsDropdown">
        <div class="dropdown-box-text">Выберите категории</div>
        <div class="dropdown-box-caret"></div>
        <div class="dropdown-content" id="dropdownContent">
          <?php foreach ($allCategories as $cat): ?>
            <label class="checkbox-item">
              <input type="checkbox" name="cats[]" value="<?php echo htmlspecialchars($cat); ?>">
              <span><?php echo htmlspecialchars($cat); ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="btns-container">
        <button class="search-btn" type="submit" id="searchBtn">Поиск</button>
        <button class="reset-btn" type="button" id="resetBtn">Сбросить</button>
      </div>
    </div>
    <div class="tags-row" id="tagsRow"></div>
  </form>

  <h2 class="category-heading" id="resultsHeading" style="display:none;">Результаты поиска</h2>
  <div class="users-grid" id="usersGrid">
    <?php outputSkeletons(); ?>
  </div>
</div>

<script>
  // --- Кэш для запросов ---
  var cache = {};
  const CACHE_TTL = 300000; // 5 минут

  function fetchCached(url) {
    if (cache[url] && (Date.now() - cache[url].timestamp < CACHE_TTL)) {
      return Promise.resolve(cache[url].data);
    } else {
      return fetch(url)
        .then(response => response.json())
        .then(data => {
          cache[url] = { data: data, timestamp: Date.now() };
          return data;
        });
    }
  }

  // Переход к профилю пользователя
  function goToProfile(userId) {
    window.location.href = 'profile_view.php?user_id=' + userId;
  }

  // Проверяем, есть ли активный поиск
  function isSearchActive() {
    const form = document.getElementById('searchForm');
    const q = form.elements['q'].value.trim();
    const cats = form.querySelectorAll('input[name="cats[]"]:checked');
    return (q !== '' || cats.length > 0);
  }

  // Получаем корректную подпись для dropdown
  function getCategoryText(count) {
    if (count === 0) {
      return "Выберите категории";
    } else if (count === 1) {
      return "1 категория выбрана";
    } else if (count >= 2 && count <= 4) {
      return count + " категории выбраны";
    } else {
      return count + " категорий выбраны";
    }
  }
  function updateDropdownText() {
    const checked = document.querySelectorAll('#dropdownContent input[name="cats[]"]:checked');
    const count = checked.length;
    document.querySelector('#catsDropdown .dropdown-box-text').textContent = getCategoryText(count);
  }

  // Удаление тега
  window.removeTag = function(cat) {
    const checkboxes = document.querySelectorAll('#dropdownContent input[name="cats[]"]');
    checkboxes.forEach(cb => {
      if (cb.value === cat) {
        cb.checked = false;
      }
    });
    updateTags();
  };

  // Обновляем теги и кнопку "Поиск"
  function updateTags() {
    const tagsRow = document.getElementById('tagsRow');
    tagsRow.innerHTML = '';
    const checkboxes = document.querySelectorAll('#dropdownContent input[name="cats[]"]');
    checkboxes.forEach(cb => {
      if (cb.checked) {
        const tag = document.createElement('div');
        tag.className = 'tag';
        tag.setAttribute('data-cat', cb.value);
        tag.innerHTML = `<div>${cb.value}</div><div class="cross-icon" onclick="removeTag('${cb.value}')"></div>`;
        tagsRow.appendChild(tag);
      }
    });
    updateDropdownText();
    updateSearchBtn();
  }

  // Обновление внешнего вида кнопки поиска
  function updateSearchBtn() {
    const searchInput = document.querySelector('input[name="q"]');
    const dropdownText = document.querySelector('#catsDropdown .dropdown-box-text').textContent;
    const initialSearch = "";
    const initialDropdown = "Выберите категории";
    const searchBtn = document.getElementById('searchBtn');
    if (searchInput.value.trim() !== initialSearch || dropdownText !== initialDropdown) {
      searchBtn.classList.add('modified');
    } else {
      searchBtn.classList.remove('modified');
    }
  }

  // Генерация карточки пользователя
  function generateUserCard(user) {
    const card = document.createElement('div');
    card.className = 'user-card';
    // При клике на карточку переходим к профилю
    card.onclick = function() { goToProfile(user.id); };

    let avatarPath = user.avatar 
      ? "/IMG_WEBSITY/USER_PROFILE_IMG/" + user.avatar 
      : "/img/pictures/dop/profileAvatarDefault.png";

    // Категории через " | "
    let topCats = "";
    if (user.top_categories && Array.isArray(user.top_categories)) {
      topCats = user.top_categories.join(" | ");
    }
    // Обрезаем bio, если слишком длинное
    const truncatedBio = (user.bio && user.bio.length > 150) 
                         ? user.bio.substring(0,150) + "..." 
                         : (user.bio || "");

    card.innerHTML = `
      <div class="user-content">
        <div class="user-header">
          <div class="user-avatar">
            <img src="${avatarPath}" alt="User Avatar">
          </div>
          <div class="user-info">
            <div class="user-login">${user.login}</div>
            <div class="user-workplace">${user.workplace ? user.workplace : user.location ? user.location : 'Без места работы'}</div>
          </div>
        </div>
        <div class="user-bio">${truncatedBio}</div>
        <div class="user-stats">
          <div class="user-project-count">${user.project_count || 0} проектов</div>
          <div class="user-top-cats"> ${topCats}</div>
        </div>
      </div>
    `;
    return card;
  }

  // Загрузка пользователей (AJAX)
  async function loadUsers() {
    const container = document.getElementById('usersGrid');
    // Показываем скелетоны
    container.innerHTML = '';
    for (let i = 0; i < 9; i++) {
      container.innerHTML += `<div class="user-card skeleton">
        <div class="user-content">
          <div class="user-header">
            <div class="user-avatar" style="background:#444;"></div>
            <div class="user-info">
              <div class="user-login" style="width:150px; height:20px; background:#444; margin-bottom:8px;"></div>
              <div class="user-workplace" style="width:180px; height:16px; background:#444;"></div>
            </div>
          </div>
          <div class="user-bio" style="margin-top:12px; width:100%; height:50px; background:#444;"></div>
          <div class="user-stats" style="margin-top:auto;">
            <div class="user-project-count" style="height:16px; background:#444; width:120px; margin-bottom:8px;"></div>
            <div class="user-top-cats" style="height:16px; background:#444; width:200px;"></div>
          </div>
        </div>
      </div>`;
    }

    const form = document.getElementById('searchForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);

    // Если нет активного поиска, ограничимся 9 пользователями
    if (!isSearchActive()) {
      params.append('limit', '200');
    }

    let url = 'fetch_users.php?' + params.toString();
    try {
      fetchCached(url).then(data => {
        setTimeout(() => {
          container.innerHTML = '';
          if (data.length > 0) {
            data.forEach(user => container.appendChild(generateUserCard(user)));
          } else {
            container.innerHTML = '<p style="color:#EBEBEA;">Нет пользователей.</p>';
          }
        }, 200);
      });
    } catch (error) {
      container.innerHTML = '<p style="color:#EBEBEA;">Ошибка загрузки.</p>';
      console.error('Error loading users:', error);
    }
  }

  // Событие отправки формы
  document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const resultsHeading = document.getElementById('resultsHeading');
    if (isSearchActive()) {
      resultsHeading.style.display = 'block';
    } else {
      resultsHeading.style.display = 'none';
    }
    loadUsers();
  });

  // Кнопка сброса
  document.getElementById('resetBtn').addEventListener('click', function() {
    document.getElementById('searchForm').reset();
    document.getElementById('tagsRow').innerHTML = '';
    document.querySelector('#catsDropdown .dropdown-box-text').textContent = "Выберите категории";
    document.getElementById('resultsHeading').style.display = 'none';
    loadUsers();
    updateSearchBtn();
  });

  // Инициализация dropdown и тегов
  document.addEventListener('DOMContentLoaded', function() {
    const dropdownBox = document.getElementById('catsDropdown');
    const dropdownContent = document.getElementById('dropdownContent');
    const checkboxes = dropdownContent.querySelectorAll('input[name="cats[]"]');
    const searchInput = document.querySelector('input[name="q"]');

    searchInput.addEventListener('input', updateSearchBtn);
    checkboxes.forEach(cb => cb.addEventListener('change', updateTags));

    dropdownBox.addEventListener('click', function(e) {
      e.stopPropagation();
      dropdownBox.classList.toggle('open');
    });
    dropdownContent.addEventListener('mouseleave', function() {
      dropdownBox.classList.remove('open');
    });

    updateTags();
    // При первом открытии страницы загружаем пользователей
    loadUsers();
  });
</script>
</body>



<?php
// index.php
// Подключаем navbar.php, который уже содержит doctype, <head>, <body>, <header> и т.д.
include 'footer.php';
?>



</html>
