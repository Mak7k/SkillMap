<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Избранное</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Подключаем dashicons для иконок -->
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
      padding-top: 70px;
    }
    .page-heading {
      text-align: center;
      font-size: 40px;
      font-weight: 600;
      margin-bottom: 40px;
    }
    .page-heading span.green {
      color: #53F371;
    }
    .page-heading span.blue {
      color: #46C0F0;
    }
    .search-module {
      width: 70%;
      max-width: 90%;
      margin: 0 auto 40px auto;
      background: rgba(47,57,61,0.5);
      border-radius: 15px;
      padding: 24px 20px;
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 16px;
    }
    .search-box {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 16px;
      background: #F2F4F8;
      border-radius: 12px;
    }
    .search-box input[type="text"] {
      width: 100%;
      font-size: 20px;
      font-weight: 500;
      border: none;
      outline: none;
      background: transparent;
      color: #000;
    }
    .search-icon {
      width: 24px;
      height: 24px;
      background: url('/img/pictures/dop/IconSearch.png') no-repeat center;
      background-size: contain;
      flex-shrink: 0;
    }
    .btns-container {
      display: flex;
      gap: 10px;
    }
    .search-btn, .reset-btn {
      width: 114px;
      min-height: 50px;
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
    .search-btn.modified, .search-btn:hover {
      background: rgb(70,192,240);
      color: #212628;
    }
    .search-btn.modified {
      background-color: rgba(83, 243, 113, 0.75);
    }
    .reset-btn {
      background-color: #e31f1f;
      color: #212628;
    }
    .reset-btn:hover {
      background-color: #c22020;
    }
    .projects-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      margin-bottom: 40px;
    }
    .project-card {
      width: 370px;
      height: 550px;
      background: rgb(47,57,61);
      border-radius: 15px;
      display: flex;
      flex-direction: column;
      position: relative;
      cursor: pointer;
      overflow: hidden;
    }
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
    .project-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      padding: 10px 15px 15px 15px;
      gap: 10px;
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
    /* Стили цены – теперь как в магазине проектов */
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
      margin-top: auto;
      padding-top: 10px;
    }
    .project-author-inner {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      padding: 0px 15px 15px 15px;
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
    .fav-container { }
    .fav-icon {
      width: 24px;
      height: 24px;
      cursor: pointer;
    }
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
    @media (max-width: 1200px) {
      .container { padding: 20px; padding-top: 70px; }
      .search-module { width: 100%; padding: 16px 20px; }
      .projects-grid { gap: 20px; }
    }



    @media (max-width: 600px) {
      .search-module{
        flex-direction: column;
        /* padding: 0px; */
        /* margin: 0 auto; */
        max-width: 100%;
      }

      .container{
        padding-top: 30px;
      }
      .btns-container{
        flex-direction: column;
        width: 100%;
      }

      .search-btn, .reset-btn, .search-box{
        width: 100%;
      }

      
    }
  </style>
</head>
<body>
<div class="container">
  <h1 class="page-heading">
    Здесь хранятся <span class="green">ваши избранные</span> <span class="blue">проекты</span>
  </h1>
  <form class="search-module" id="searchForm">
    <div class="search-box">
      <div class="search-icon"></div>
      <input type="text" name="q" placeholder="Кто интересует?" value="">
    </div>
    <div class="btns-container">
      <button class="search-btn" type="submit" id="searchBtn">Поиск</button>
      <button class="reset-btn" type="button" id="resetBtn">Сбросить</button>
    </div>
  </form>
  <div class="projects-grid" id="projectsGrid">
    <?php
      // При первичной загрузке выводим скелетоны
      for ($i = 0; $i < 8; $i++):
    ?>
      <div class="project-card skeleton">
        <div class="project-image">
          <div style="width:100%; height:100%; background:#444;"></div>
        </div>
        <div class="project-content">
          <div class="project-category" style="height:20px; background:#444; margin-bottom:10px;"></div>
          <div class="project-title" style="height:24px; background:#444; margin-bottom:10px;"></div>
          <div class="project-description" style="height:60px; background:#444;"></div>
        </div>
        <div class="project-price" style="display:none;">0 ₽</div>
        <div class="project-author" style="margin-top:auto;">
          <div class="project-author-inner">
            <div class="author-info">
              <div class="author-avatar" style="width:45px; height:45px; border-radius:50%; background:#444;"></div>
              <div class="author-name" style="height:20px; background:#444; width:80px;"></div>
            </div>
            <div class="fav-container">
              <img class="fav-icon" src="/img/pictures/dop/FavHeartFill.png" alt="Favorite">
            </div>
          </div>
        </div>
      </div>
    <?php endfor; ?>
  </div>
</div>

<script>
  var cache = {};
  const CACHE_TTL = 300000;
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
  function generateProjectCard(project) {
    let imgPath = project.main_image ? "/projects/IMG_WEBSITY/PROJECT_MAIN_IMG/" + project.main_image : "/img/pictures/dop/noProjectImg.png";
    let authorAvatar = project.avatar ? "/IMG_WEBSITY/USER_PROFILE_IMG/" + project.avatar : "/img/pictures/dop/profileAvatarDefault.png";
    let priceHtml = "";
    if (project.price) {
      priceHtml = `<div class="project-price">₽ ${parseInt(project.price)}</div>`;
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
      ${priceHtml}
      <div class="project-author">
        <div class="project-author-inner">
          <div class="author-info">
            <div class="author-avatar">
              <img src="${authorAvatar}" alt="Author Avatar">
            </div>
            <div class="author-name">${project.login}</div>
          </div>
          ${ project.isFavorite !== undefined ? `
          <div class="fav-container">
            <img class="fav-icon" data-project-id="${project.id}" 
                 src="${project.isFavorite ? '/img/pictures/dop/FavHeartFill.png' : '/img/pictures/dop/FavHeart.png'}" 
                 alt="Favorite">
          </div>` : '' }
        </div>
      </div>
    `;
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
          if(data.success) {
            icon.src = data.favorite ? '/img/pictures/dop/FavHeartFill.png' : '/img/pictures/dop/FavHeart.png';
          } else {
            alert('Ошибка обновления избранного');
          }
        })
        .catch(error => console.error('Error:', error));
      });
    });
  }
  async function loadFavorites() {
    const container = document.getElementById('projectsGrid');
    container.innerHTML = '';
    for (let i = 0; i < 8; i++) {
      container.innerHTML += `<div class="project-card skeleton">
        <div class="project-image">
          <div style="width:100%; height:100%; background:#444;"></div>
        </div>
        <div class="project-content">
          <div class="project-category" style="height:20px; background:#444; margin-bottom:10px;"></div>
          <div class="project-title" style="height:24px; background:#444; margin-bottom:10px;"></div>
          <div class="project-description" style="height:60px; background:#444;"></div>
        </div>
        <div class="project-price" style="display:none;">0 ₽</div>
        <div class="project-author" style="margin-top:auto;">
          <div class="project-author-inner">
            <div class="author-info">
              <div class="author-avatar" style="width:45px; height:45px; border-radius:50%; background:#444;"></div>
              <div class="author-name" style="height:20px; background:#444; width:80px;"></div>
            </div>
            <div class="fav-container">
              <img class="fav-icon" src="/img/pictures/dop/FavHeartFill.png" alt="Favorite">
            </div>
          </div>
        </div>
      </div>`;
    }
    const form = document.getElementById('searchForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    let url = '/fetch_favorites.php?' + params.toString();
    try {
      fetchCached(url).then(data => {
        setTimeout(() => {
          container.innerHTML = '';
          if (data.length > 0) {
            data.forEach(project => container.appendChild(generateProjectCard(project)));
            attachFavoriteEvents(container);
          } else {
            container.innerHTML = '<p style="color:#EBEBEA; text-align:center;">Нет избранных проектов.</p>';
          }
        }, 200);
      });
    } catch (error) {
      container.innerHTML = '<p style="color:#EBEBEA; text-align:center;">Ошибка загрузки избранного.</p>';
      console.error('Error loading favorites:', error);
    }
  }
  document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    scrollToTop();
    loadFavorites();
  });
  document.getElementById('resetBtn').addEventListener('click', function() {
    document.getElementById('searchForm').reset();
    scrollToTop();
    loadFavorites();
  });
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="q"]');
    searchInput.addEventListener('input', function() {});
    loadFavorites();
  });
</script>
</body>


<?php
// index.php
// Подключаем navbar.php, который уже содержит doctype, <head>, <body>, <header> и т.д.
include 'footer.php';
?>



</html>
