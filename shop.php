<?php
session_start();
include 'navbar.php';

// Список всех категорий для dropdown и мультисекции
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

// Функция для вывода skeleton‑карточек (8 штук по умолчанию)
function outputSkeletons($count = 8) {
  for ($i = 0; $i < $count; $i++): ?>
    <div class="project-card skeleton">
      <div class="project-image">
        <div style="width:100%; height:100%; background:#444;"></div>
      </div>
      <div class="project-content">
        <div class="project-category" style="height:20px; background:#444; margin-bottom:10px; margin-top:15px;"></div>
        <div class="project-title" style="height:24px; background:#444; margin-bottom:10px;"></div>
        <div class="project-description" style="height:60px; background:#444;"></div>
      </div>
      <div class="project-price" style="height:20px; background:#444;"></div>
      <div class="project-author">
        <div class="project-author-inner">
          <div class="author-info" style="display:flex; align-items:center; gap:10px;">
            <div class="author-avatar" style="width:45px; height:45px; border-radius:50%; background:#444;"></div>
            <div class="author-name" style="height:20px; background:#444; width:80px;"></div>
          </div>
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
  <!-- Для мобильной адаптации -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Магазин проектов</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dashicons/3.8.1/css/dashicons.min.css">
  <style>
    /* Общие стили */
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
      margin-top: 70px;
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
      width: 100%;
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
    /* Центрирование кнопок: контейнер занимает всю ширину */
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
      /* background-color: rgba(83, 243, 113, 0.75); */
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
      font-size: 55px;
      font-weight: 600;
      line-height: 67px;
      margin: 20px 0 40px 0;
      color: #F2F4F8;
    }
    .projects-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      margin-bottom: 20px;
    }
    .project-card {
      width: 350px;
      height: 500px;
      background: rgb(47,57,61);
      border-radius: 15px;
      display: flex;
      flex-direction: column;
      position: relative;
      cursor: pointer;
      overflow: hidden;
      padding: 0;
    }
    .project-image {
      width: 100%;
      height: 200px;
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
    .project-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      padding: 0 15px 15px 15px;
      gap: 10px;
      position: relative;
    }
    .project-category {
      color: #EBEBEA;
      font-size: 14px;
      font-weight: 400;
    }
    .project-title {
      color: #46C0F0;
      font-size: 18px;
      font-weight: 600;
      margin-top: 5px;
    }
    .project-description {
      color: #EBEBEA;
      font-size: 14px;
      font-weight: 400;
      line-height: 17px;
    }
    /* Секция цены – прижата к нижней части карточки, над информацией об авторе */
    .project-price {
      font-size: 16px;
      font-weight: 400;
      color: #53F371;
      padding: 15px;
      text-align: left;
      width: 100%;
      box-sizing: border-box;
    }
    .project-author {
      padding: 0 15px 15px 15px;
    }
    .project-author-inner {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
    }
    .author-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .author-avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      overflow: hidden;
      background: #ccc;
      flex-shrink: 0;
    }
    .author-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .author-name {
      font-size: 14px;
      color: #EBEBEA;
      font-weight: 400;
    }
    .fav-container {
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .fav-icon {
      width: 24px;
      height: 24px;
      cursor: pointer;
    }
    /* Стили для скелетон-карточек */
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
    /* Контейнер для кнопки "Смотреть все" – выравнивание по центру */
    .view-all-container {
      width: 100%;
      text-align: center;
    }
    .view-all-btn {
      padding: 10px 20px;
      font-size: 16px;
      border: none;
      border-radius: 16px;
      background: #53F371;
      color: #212628;
      cursor: pointer;
      transition: background 0.3s;
      font-family: 'Montserrat', sans-serif;
      font-weight: 500;
      padding: 16px 40px;
    }
    .view-all-btn:hover {
      background: #3690c0;
    }
    /* Мобильная адаптация */
    @media (max-width: 1200px) {
      .container { padding: 20px; }
      .search-module { width: 100%; padding: 16px 20px; }
      .search-row { flex-direction: column; align-items: stretch; gap: 10px; }
      .search-box, .dropdown-box, .btns-container button { width: 100%; min-height: 60px; padding: 16px; }
      .dropdown-box { width: 100%; }
      .dropdown-content { width: 100%; top: 60px; }
      .btns-container { justify-content: center; }
      .search-btn, .reset-btn { font-size: 14px; width: 48%; }
      .top-heading { font-size: 28px; }
      .top-subheading { font-size: 20px; }
      .tags-row { justify-content: center; }
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
      .category-heading{
        font-size: 30px;
      }


      
    }
  </style>
</head>
<body>
<div class="container">
  <!-- Верхние тексты и поисковая форма -->
  <div class="top-heading">
    <span style="color: rgb(83,243,113);">Ищи,</span> <span style="color: #46C0F0;"> покупай,</span><span style="color: rgb(83,243,113);"> используй</span> готовые идеи для ваших задач
  </div>
  <div class="top-subheading">Воспользуйся <span style="color: #46C0F0;">поиском</span> и фильтрами по категориям</div>

  <form class="search-module" id="searchForm">
    <div class="search-row">
      <div class="search-box">
        <div class="search-icon"></div>
        <input type="text" name="q" placeholder="Что ищем сегодня?" value="">
      </div>
      <div class="dropdown-box" id="catsDropdown">
        <!-- Текст в этом блоке обновляется динамически -->
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

  <!-- Контейнер для результатов поиска (режим поиска) – скрыт по умолчанию -->
  <div id="searchResultsContainer" style="display: none;">
    <h2 class="category-heading" id="resultsHeading">Результаты поиска</h2>
    <div class="projects-grid" id="projectsGrid">
      <?php outputSkeletons(); ?>
    </div>
  </div>

  <!-- Контейнер для мультисекции (режим, когда поиск не активен) -->
  <div id="multiSections">
    <!-- Секция "Новинки" -->
    <div class="section" id="section-new">
      <h2 class="category-heading">Новинки</h2>
      <div class="projects-grid">
        <?php outputSkeletons(); ?>
      </div>
      <div class="view-all-container">
        <button class="view-all-btn" onclick="applyCategorySearch(null)">Смотреть все</button>
      </div>
    </div>
    <!-- Секции для каждой категории -->
    <?php foreach ($allCategories as $cat):
      $sectionId = 'section-' . preg_replace('/\s+/', '-', strtolower($cat));
    ?>
      <div class="section" id="<?php echo $sectionId; ?>">
        <h2 class="category-heading"><?php echo htmlspecialchars($cat); ?></h2>
        <div class="projects-grid">
          <?php outputSkeletons(); ?>
        </div>
        <div class="view-all-container">
          <button class="view-all-btn" onclick="applyCategorySearch('<?php echo addslashes($cat); ?>')">Смотреть все</button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
  // Простейший кэш для запросов: ключ – URL, значение – {data, timestamp}
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

  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function goToProject(id) {
    window.location.href = 'view_project.php?id=' + id;
  }

  // Функция генерации карточки проекта с выводом цены
  function generateProjectCard(project) {
    let imgPath = project.main_image ? "/projects/IMG_WEBSITY/PROJECT_MAIN_IMG/" + project.main_image : "/img/pictures/dop/noProjectImg.png";
    let authorAvatar = project.avatar ? "/IMG_WEBSITY/USER_PROFILE_IMG/" + project.avatar : "/img/pictures/dop/profileAvatarDefault.png";
    let priceHTML = '';
    if(project.price){
      priceHTML = `<div class="project-price">₽ ${parseInt(project.price)}</div>`;
    }
    let card = document.createElement('div');
    card.className = 'project-card';
    card.onclick = function() { goToProject(project.id); };
    card.innerHTML = `
      <div class="project-image">
        <img src="${imgPath}" alt="Project Image">
      </div>
      <div class="project-content">
        <div class="project-category">${project.category}</div>
        <div class="project-title">${project.title}</div>
        <div class="project-description">${project.truncated_description}</div>
      </div>
      ${priceHTML}
      <div class="project-author">
        <div class="project-author-inner">
          <div class="author-info">
            <div class="author-avatar">
              <img src="${authorAvatar}" alt="Author Avatar">
            </div>
            <div class="author-name">${project.login}</div>
          </div>
          ${project.isFavorite !== undefined ? `
          <div class="fav-container">
            <img class="fav-icon" data-project-id="${project.id}" 
                 src="${project.isFavorite ? '/img/pictures/dop/FavHeartFill.png' : '/img/pictures/dop/FavHeart.png'}" 
                 alt="Favorite">
          </div>` : ''}
        </div>
      </div>`;
    return card;
  }

  function attachFavoriteEvents(container) {
    const favIcons = container.querySelectorAll('.fav-icon');
    favIcons.forEach(function(icon) {
      icon.addEventListener('click', function(e) {
        e.stopPropagation();
        const projectId = icon.getAttribute('data-project-id');
        fetch('toggle_favorite.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'project_id=' + encodeURIComponent(projectId)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            icon.src = data.favorite ? '/img/pictures/dop/FavHeartFill.png' : '/img/pictures/dop/FavHeart.png';
          } else {
            alert('Ошибка обновления избранного');
          }
        })
        .catch(error => console.error('Error:', error));
      });
    });
  }

  async function loadSearchResults() {
    const container = document.getElementById('projectsGrid');
    const form = document.getElementById('searchForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    // Дополнительно добавляем параметр для выборки проектов с ценой
    params.append('shop', '1');
    let url = 'fetch_shop_projects.php?' + params.toString();
    try {
      fetchCached(url).then(data => {
        setTimeout(() => {
          container.innerHTML = '';
          if (data.length > 0) {
            data.forEach(project => container.appendChild(generateProjectCard(project)));
            attachFavoriteEvents(container);
          } else {
            container.innerHTML = '<p style="color:#EBEBEA;">Нет проектов.</p>';
          }
        }, 200);
      });
    } catch (error) {
      container.innerHTML = '<p style="color:#EBEBEA;">Ошибка загрузки проектов.</p>';
      console.error('Error loading search results:', error);
    }
  }

  async function loadProjectsSection(sectionElement, category = null) {
    const grid = sectionElement.querySelector('.projects-grid');
    let params = new URLSearchParams();
    params.append('limit', '8');
    // Параметр для выборки проектов с ценой
    params.append('shop', '1');
    if (category) {
      params.append('cats[]', category);
    }
    let url = 'fetch_shop_projects.php?' + params.toString();
    try {
      fetchCached(url).then(data => {
        setTimeout(() => {
          grid.innerHTML = '';
          if (data.length > 0) {
            data.forEach(project => grid.appendChild(generateProjectCard(project)));
            attachFavoriteEvents(grid);
          } else {
            sectionElement.style.display = 'none';
          }
        }, 200);
      });
    } catch (error) {
      grid.innerHTML = '<p style="color:#EBEBEA;">Ошибка загрузки проектов.</p>';
      console.error('Error loading section projects:', error);
    }
  }

  function loadMultiSections() {
    loadProjectsSection(document.getElementById('section-new'));
    <?php foreach ($allCategories as $cat):
      $sectionId = 'section-' . preg_replace('/\s+/', '-', strtolower($cat));
    ?>
      loadProjectsSection(document.getElementById('<?php echo $sectionId; ?>'), "<?php echo $cat; ?>");
    <?php endforeach; ?>
  }

  function isSearchActive() {
    const form = document.getElementById('searchForm');
    const q = form.elements['q'].value.trim();
    const cats = form.querySelectorAll('input[name="cats[]"]:checked');
    return (q !== '' || cats.length > 0);
  }

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

  window.removeTag = function(cat) {
    const checkboxes = document.querySelectorAll('#dropdownContent input[name="cats[]"]');
    checkboxes.forEach(cb => {
      if (cb.value === cat) {
        cb.checked = false;
      }
    });
    updateTags();
  };

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

  function applyCategorySearch(category) {
    const checkboxes = document.querySelectorAll('#dropdownContent input[name="cats[]"]');
    checkboxes.forEach(cb => { cb.checked = false; });
    if (category) {
      checkboxes.forEach(cb => {
        if (cb.value === category) {
          cb.checked = true;
        }
      });
    } else {
      checkboxes.forEach(cb => { cb.checked = true; });
    }
    updateTags();
    scrollToTop();
    document.getElementById('searchForm').dispatchEvent(new Event('submit'));
  }

  document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    scrollToTop();
    if (isSearchActive()) {
      document.getElementById('searchResultsContainer').style.display = 'block';
      document.getElementById('multiSections').style.display = 'none';
      loadSearchResults();
    } else {
      document.getElementById('searchResultsContainer').style.display = 'none';
      document.getElementById('multiSections').style.display = 'block';
      loadMultiSections();
    }
  });

  document.getElementById('resetBtn').addEventListener('click', function() {
    document.getElementById('searchForm').reset();
    document.getElementById('tagsRow').innerHTML = '';
    document.querySelector('#catsDropdown .dropdown-box-text').textContent = "Выберите категории";
    scrollToTop();
    document.getElementById('searchResultsContainer').style.display = 'none';
    document.getElementById('multiSections').style.display = 'block';
    loadMultiSections();
  });

  document.addEventListener('DOMContentLoaded', function() {
    const dropdownBox = document.getElementById('catsDropdown');
    const dropdownContent = document.getElementById('dropdownContent');
    const checkboxes = dropdownContent.querySelectorAll('input[name="cats[]"]');
    const searchInput = document.querySelector('input[name="q"]');

    searchInput.addEventListener('input', updateSearchBtn);
    checkboxes.forEach(cb => {
      cb.addEventListener('change', updateTags);
    });
    dropdownBox.addEventListener('click', function(e) {
      e.stopPropagation();
      dropdownBox.classList.toggle('open');
    });
    dropdownContent.addEventListener('mouseleave', function() {
      dropdownBox.classList.remove('open');
    });

    updateTags();
    if (!isSearchActive()) {
      setTimeout(loadMultiSections, 200);
    }
  });
</script>
</body>




<?php
// index.php
// Подключаем navbar.php, который уже содержит doctype, <head>, <body>, <header> и т.д.
include 'footer.php';
?>
</html>
