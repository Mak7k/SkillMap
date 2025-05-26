<?php
// index.php
// Подключаем navbar.php, который уже содержит doctype, <head>, <body>, <header> и т.д.
include 'navbar.php';
?>

<main>
  <style>
    body {
      overflow-x: hidden;
    }
    /* ======================
       Стили для Hero-секции
       ====================== */
    .hero-section {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      padding: 40px;
      padding-right: 0;
      gap: 20px;
      position: relative;
      flex-direction: row-reverse; /* Слайдер справа, текст слева */
      min-height: 700px;
      padding-top: 100px;
    }
    /* Левая часть: заголовок, подпись, кнопки */
    .hero-left {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      max-height: 2000px;
      gap: 40px;
      max-width: 600px;
    }
    .hero-left h1 {
      font-size: 64px;
      font-weight: 600;
      line-height: 78px;
      color: #EBEBEA;
    }
    .hero-subtext {
      font-size: 24px;
      line-height: 29px;
      font-weight: 400;
      color: #EBEBEA;
    }
    .hero-buttons {
      margin-top: 10px;
      display: flex;
      gap: 20px;
    }
    .hero-btn-green, .hero-btn-blue {
      padding: 16px 40px;
      font-size: 18px;
      font-family: 'Montserrat', sans-serif;
      font-weight: 500;
      border-radius: 16px;
      transition: background 0.3s, color 0.3s;
      color: #212628;
      border: none;
      cursor: pointer;
    }
    .hero-btn-green {
      background: rgb(83,243,113);
    }
    .hero-btn-blue {
      background: rgb(70,192,240);
    }
    .hero-btn-green:hover {
      /* background: rgb(70,192,240); */
      background: rgba(83, 243, 112, 0.75);
    }
    .hero-btn-blue:hover {
      /* background: rgb(83,243,113); */
      background: rgba(70, 192, 240, 0.75);
    }
    /* Правая часть: слайдер */
    .hero-right {
      flex: 1.2;
      position: relative;
      max-width: 1149px;
      max-height: 662px;
      border-radius: 0 0 0 100px;
      overflow: hidden;
    }
    .slider-container {
      position: relative;
      width: 100%;
      height: 100%;
    }
    .slider {
      display: flex;
      transition: transform 0.6s ease;
    }
    .slider img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }
    /* Точки-индикаторы (в правом верхнем углу слайдера) */
    .slider-dots {
      position: absolute;
      top: 20px;
      right: 20px;
      display: flex;
      gap: 10px;
      z-index: 2;
    }
    .dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: rgba(47, 57, 61, 0.38);
      transition: background 0.3s;
      cursor: pointer;
    }
    .dot.active {
      background: rgb(83,243,113);
    }
    /* Карточка, закрепленная поверх слайдера */
    .hero-card {
      position: absolute;
      right: 20px;
      bottom: 20px;
      width: 400px;
      background: rgba(33,38,40,0.89);
      border-radius: 10px;
      padding: 25px;
      color: #EBEBEA;
      z-index: 2;
    }
    .hero-card h2 {
      font-size: 22px;
      font-weight: 600;
      color: rgb(70,192,240);
      margin-bottom: 10px;
    }
    .hero-card p {
      font-size: 14px;
      line-height: 18px;
      margin-bottom: 15px;
    }
    .hero-card button {
      padding: 10px 20px;
      border-radius: 10px;
      background: rgb(70,192,240);
      color: #212628;
      font-size: 14px;
      font-weight: 400;
      border: none;
      cursor: pointer;
      transition: background 0.3s;
    }
    .hero-card button:hover {
      background: rgb(83,243,113);
    }
    /* =========== Media Queries =========== */
    /* Для экранов до 1280px */
    @media (max-width: 1280px) {
      .hero-left h1 {
        font-size: 48px;
        line-height: 56px;
      }
      .hero-subtext {
        font-size: 20px;
        line-height: 24px;
      }
      .hero-btn-green, .hero-btn-blue {
        font-size: 16px;
        padding: 14px 32px;
      }
      .hero-right {
        max-width: 800px;
        aspect-ratio: 16 / 9;
        max-height: none;
      }
      .slider-container {
        height: 100%;
      }
      .slider img {
        object-fit: cover;
        object-position: center;
      }
    }
    /* Для мобильных (до 992px) */
    @media (max-width: 992px) {
      .hero-section {
        flex-direction: column;
        padding: 20px;
      }
      .hero-right {
        order: 1;
        width: 100%;
        max-width: 100%;
        border-radius: 0;
        height: 400px;
      }
      .hero-left {
        order: 2;
        width: 100%;
        max-width: 100%;
        gap: 25px;
      }
      .hero-card {
        position: static;
        transform: none;
        margin-top: 20px;
        width: 100%;
        max-width: 520px;
      }
      .hero-buttons button {
        width: 100%;
      }
      .slider-dots {
        top: 100px;
      }
    }
    @media (max-width: 500px) {
      .hero-section {
        margin-top: 80px;
      }
      .slider-dots {
        top: 20px;
      }
    }
    /* ================================
       Доработки для экранов 300-700px
       ================================ */
    @media (max-width: 700px) and (min-width: 300px) {
      /* Меняем расположение hero-секции на вертикальное */
      .hero-section {
        flex-direction: column;
        padding: 20px;
        min-height: auto;
      }
      /* Уменьшаем размеры заголовка и текста */
      .hero-left h1 {
        font-size: 32px;
        line-height: 38px;
      }
      .hero-subtext {
        font-size: 16px;
        line-height: 20px;
      }
      /* Уменьшаем отступы и размеры кнопок */
      .hero-btn-green, .hero-btn-blue {
        font-size: 14px;
        padding: 12px 24px;
      }
      .hero-buttons {
        flex-direction: column;
        gap: 10px;
      }
      /* Слайдер адаптируется по высоте */
      .hero-right {
        width: 100%;
        height: 250px;
        border-radius: 0;
      }
      /* Если карточка активна – уменьшаем её размеры */
      .hero-card {
        width: 90%;
        padding: 15px;
      }
    }

    /* Стили для блока "Преимущества" */
    .advantages-section {
      width: 100%;
      padding: 50px 40px;
      margin: 0 auto;
    }
    .advantages-title {
      color: rgb(237, 238, 239);
      font-family: Montserrat, sans-serif;
      font-size: clamp(2rem, 5vw, 3.5rem);
      font-weight: 600;
      line-height: 1.2;
      text-align: center;
      margin-bottom: 30px;
    }
    .advantages-cards {
      display: flex;
      flex-wrap: wrap;
      gap: 25px;
      justify-content: space-evenly;
      max-width: 1740px;
      margin: 0 auto;
    }
    .advantage-card {
      width: 375px;
      background: #2F393D;
      border-radius: 15px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    .advantage-card-header {
      position: relative;
      width: 100%;
      height: 385.27px;
      overflow: hidden;
    }
    .advantage-card-header::after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom, rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.1));
      pointer-events: none;
      z-index: 1;
    }
    .advantage-card-header img {
      position: absolute;
      left: -132.28px;
      top: -16.7px;
      width: 661.39px;
      height: 434.08px;
      border-radius: 25.68px 25.68px 0 0;
      object-fit: cover;
      object-position: center;
    }
    .advantage-card-overlay {
      position: absolute;
      left: 30.82px;
      top: 29.54px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      z-index: 2;
    }
    .advantage-card-overlay .reason-number {
      color: #FFFFFF;
      font-family: Montserrat, sans-serif;
      font-size: 15.41px;
      font-weight: 400;
      line-height: 19px;
    }
    .advantage-card-overlay .reason-title {
      color: #FFFFFF;
      font-family: Montserrat, sans-serif;
      font-size: 24px;
      font-weight: 500;
      line-height: 31px;
    }
    .advantage-card-content {
      padding: 30px;
      display: flex;
      flex-direction: column;
      gap: 5px;
    }
    .advantage-card-content p {
      color: #ECEEEE;
      font-family: Montserrat, sans-serif;
      font-size: 18px;
      font-weight: 400;
      line-height: 22px;
    }
    /* Корректировки для преимуществ на узких экранах АДАПТАЦИЯ */
    @media (max-width: 700px) and (min-width: 300px) {
      .advantages-section {
        padding: 30px 20px;
      }
      .advantage-card {
        width: 100%;
        max-width: 340px;
      }
      .advantage-card-header {
        height: 250px;
      }
      .advantage-card-overlay .reason-number {
        font-size: 13px;
      }
      .advantage-card-overlay .reason-title {
        font-size: 20px;
      }
      .advantage-card-content p {
        font-size: 16px;
      }
    }
  </style>

  <!-- Hero-секция -->
  <section class="hero-section">
    <!-- Правая часть: слайдер -->
    <div class="hero-right">
      <div class="slider-container">
        <div class="slider" id="slider">
          <!-- 4 слайда -->
          <img src="img/pictures/slaider/NewSl1.jpg" alt="Слайд 1">
          <img src="img/pictures/slaider/NewSl2.jpeg" alt="Слайд 2">
          <img src="img/pictures/slaider/NewSl3.jpg" alt="Слайд 3">
          <img src="img/pictures/slaider/NewSl4.jpg" alt="Слайд 4">
        </div>
        <!-- Точки-индикаторы -->
        <div class="slider-dots" id="sliderDots">
          <span class="dot active"></span>
          <span class="dot"></span>
          <span class="dot"></span>
          <span class="dot"></span>
        </div>
        <!-- Карточка (если потребуется, можно включить) -->
        <!--
        <div class="hero-card">
          <h2>Более 1 млн. пользователей</h2>
          <p>Платформа для создания и продажи цифровых проектов, где каждый может реализовать свои идеи. Создавай, делись, продавай и общайся!</p>
          <button>Присоединяйся</button>
        </div>
        -->
      </div>
    </div>

    <!-- Левая часть: заголовок, подпись, кнопки -->
    <div class="hero-left">
      <h1><span style="color: #46C0F0">Создай</span> своё уникальное портфолио<br>и <span style="color: #53F371">делись</span> творчеством!</h1>
      <p class="hero-subtext">
        Платформа для создания и продажи цифровых проектов, где каждый может реализовать свои идеи. Создавай, делись, продавай и общайся!
      </p>
      <div class="hero-buttons">
        <button onclick="document.location='projects.php'" class="hero-btn-green">Начать бесплатно</button>
        <button class="hero-btn-blue">Узнать больше</button>
      </div>
    </div>
  </section>

  <script>
    // Простейший автоперелистывающийся слайдер
    const slider = document.getElementById('slider');
    const slides = slider.querySelectorAll('img');
    const dots = document.getElementById('sliderDots').querySelectorAll('.dot');
    let currentIndex = 0;
    const totalSlides = slides.length;

    function showSlide(index) {
      slider.style.transform = `translateX(-${index * 100}%)`;
      dots.forEach(dot => dot.classList.remove('active'));
      dots[index].classList.add('active');
    }

    function nextSlide() {
      currentIndex = (currentIndex + 1) % totalSlides;
      showSlide(currentIndex);
    }

    setInterval(nextSlide, 5000);

    // Возможность клика по точкам
    dots.forEach((dot, i) => {
      dot.addEventListener('click', () => {
        currentIndex = i;
        showSlide(i);
      });
    });

    // При изменении размера окна пересчитываем слайдер
    window.addEventListener('resize', () => {
      showSlide(currentIndex);
    });
  </script>

  <!-- Блок "Преимущества" -->
  <section class="advantages-section">
    <h2 class="advantages-title">Преимущества</h2>
    <div class="advantages-cards">
      <!-- Карточка 1 -->
      <div class="advantage-card">
        <div class="advantage-card-header">
          <img src="img/pictures/Plus/Plus1.jpg" alt="Преимущество 1">
          <div class="advantage-card-overlay">
            <span class="reason-number">Причина I</span>
            <span class="reason-title">Гибкий конструктор проектов</span>
          </div>
        </div>
        <div class="advantage-card-content">
          <p>Создавайте проекты с помощью удобного конструктора — без необходимости программировать. Легко добавляйте текст, изображения, ссылки и видео!</p>
        </div>
      </div>
      <!-- Карточка 2 -->
      <div class="advantage-card">
        <div class="advantage-card-header">
          <img src="img/pictures/Plus/Plus2.jpg" alt="Преимущество 2">
          <div class="advantage-card-overlay">
            <span class="reason-number">Причина II</span>
            <span class="reason-title">Полная кастомизация</span>
          </div>
        </div>
        <div class="advantage-card-content">
          <p>Настраивайте дизайн, компоненты и структуру по своему вкусу. Сервис даёт полный контроль над внешним видом и функционалом.</p>
        </div>
      </div>
      <!-- Карточка 3 -->
      <div class="advantage-card">
        <div class="advantage-card-header">
          <img src="img/pictures/Plus/Plus3.jpg" alt="Преимущество 3">
          <div class="advantage-card-overlay">
            <span class="reason-number">Причина III</span>
            <span class="reason-title">Удобное взаимодействие</span>
          </div>
        </div>
        <div class="advantage-card-content">
          <p>Общайтесь с сообществом, получайте отзывы и делитесь опытом. Интуитивный интерфейс и гибкая система комментариев!</p>
        </div>
      </div>
      <!-- Карточка 4 -->
      <div class="advantage-card">
        <div class="advantage-card-header">
          <img src="img/pictures/Plus/Plus4.jpg" alt="Преимущество 4">
          <div class="advantage-card-overlay">
            <span class="reason-number">Причина IV</span>
            <span class="reason-title">Монетизация проектов</span>
          </div>
        </div>
        <div class="advantage-card-content">
          <p>Продавайте готовые решения, расширения и шаблоны. Получайте доход от своих цифровых продуктов и повышайте квалификацию!</p>
        </div>
      </div>
    </div>
  </section>
</main>


<!-- Адаптация -->


  <!-- Внутри блока "advantages-section" (или под ним)
<div class="advantages-floating-squares"></div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Находим контейнер для квадратиков
  const squaresContainer = document.querySelector('.advantages-floating-squares');
  if (!squaresContainer) return;

  // Функция для создания одного квадратика
  function spawnSquare() {
    const square = document.createElement('div');
    square.classList.add('floating-square');

    // Размер квадратика: от 20 до 80 пикселей
    const size = 20 + Math.random() * 60;
    square.style.width = size + 'px';
    square.style.height = size + 'px';

    // Случайный цвет из двух вариантов
    const colors = ['#53F371', '#46C0F0'];
    const color = colors[Math.floor(Math.random() * colors.length)];
    square.style.backgroundColor = color;

    // Устанавливаем начальную прозрачность
    square.style.opacity = 0.5;

    // Случайная вертикальная позиция внутри контейнера (0–100%)
    const topPos = Math.random() * 20;
    square.style.top = topPos + '%';

    // Случайное направление: с левой или правой стороны
    const fromLeft = Math.random() < 0.5;
    if (fromLeft) {
      square.style.left = `-${parseInt(getComputedStyle(document.documentElement).getPropertyValue('--square-start-offset'))}px`;
      square.classList.add('float-ltr');
    } else {
      square.style.left = 'calc(100% + var(--square-start-offset))';
      square.classList.add('float-rtl');
    }

    // Задаём случайные задержку и длительность анимации
    const delay = Math.random() * 3; // 0–3 сек задержка
    const duration = 40 + Math.random() * 20; // 40–60 сек длительность
    square.style.animationDelay = delay + 's';
    square.style.animationDuration = duration + 's';

    // Добавляем квадрат в контейнер
    squaresContainer.appendChild(square);

    // Удаляем квадрат после завершения анимации (с учётом задержки)
    setTimeout(() => {
      square.remove();
    }, (delay + duration) * 1000);
  }

  // Непрерывное создание квадратиков: создаём новый каждые 800 мс
  setInterval(spawnSquare, 5);
});
</script> -->


</section>

<!-- КОНЕЦ БЛОКА ПРЕИМУЩСТВ -->








<!-- НАЧАЛО БЛОКА ОТЗЫВОВ -->



<!-- Блок "Что о нас говорят" -->
<section class="reviews-section">
  <h2 class="reviews-title">Что о нас говорят?</h2>

  <div class="reviews-slider">
    <div class="reviews-track">


      <!-- Карточка №1 -->
<div class="review-card">
  <div class="revew-cont">
    <div class="review-avatar">
      <img src="img/pictures/reviews/Rev1.jpg" alt="Avatar 1">
    </div>
    <div class="review-author">
      <p class="author-name">Андрей Соколов</p>
      <p class="author-position">Full Stack Developer</p>
    </div>
  </div>
  <div class="review-text">
    <span class="quotes">&laquo;</span><br>
    Отличная платформа для айтишников! Удобно оформлять проекты и добавлять новые технологии. Рекомендую всем, кто ценит качество и скорость.
    <br><span class="quotes quotes-right">&raquo;</span>
  </div>
</div>

<!-- Карточка №2 (женская) -->
<div class="review-card">
  <div class="revew-cont"> 
    <div class="review-avatar">
      <img src="img/pictures/reviews/Rev2.jpg" alt="Avatar 2">
    </div>
    <div class="review-author">
      <p class="author-name">Марина Кузнецова</p>
      <p class="author-position">Frontend Developer</p>
    </div>
  </div>
  <div class="review-text">
    <span class="quotes">&laquo;</span><br>
    Платформа удобна и интуитивна! Интерфейс радует простотой, а функционал – продуманностью. Очень довольна результатом.
    <br><span class="quotes quotes-right">&raquo;</span>
  </div>
</div>

<!-- Карточка №3 -->
<div class="review-card">
  <div class="revew-cont"> 
    <div class="review-avatar">
      <img src="img/pictures/reviews/Rev3.jpg" alt="Avatar 3">
    </div>
    <div class="review-author">
      <p class="author-name">Иван Петров</p>
      <p class="author-position">Frontend Developer</p>
    </div>
  </div>
  <div class="review-text">
    <span class="quotes">&laquo;</span><br>
    Прекрасное решение для оптимизации рабочего процесса. Всё работает быстро и без сбоев. Рекомендую для эффективной работы.
    <br><span class="quotes quotes-right">&raquo;</span>
  </div>
</div>

<!-- Карточка №4 (женская) -->
<div class="review-card">
  <div class="revew-cont"> 
    <div class="review-avatar">
      <img src="img/pictures/reviews/Rev4.jpg" alt="Avatar 4">
    </div>
    <div class="review-author">
      <p class="author-name">Ольга Сергеева</p>
      <p class="author-position">UI/UX Designer</p>
    </div>
  </div>
  <div class="review-text">
    <span class="quotes">&laquo;</span><br>
    Отличный инструмент для реализации креативных идей. Интерфейс вдохновляет на новые проекты. Очень рекомендую платформу!
    <br><span class="quotes quotes-right">&raquo;</span>
  </div>
</div>

<!-- Карточка №5 -->
<div class="review-card">
  <div class="revew-cont"> 
    <div class="review-avatar">
      <img src="img/pictures/reviews/Rev5.jpg" alt="Avatar 5">
    </div>
    <div class="review-author">
      <p class="author-name">Дмитрий Иванов</p>
      <p class="author-position">Project Manager</p>
    </div>
  </div>
  <div class="review-text">
    <span class="quotes">&laquo;</span><br>
    Платформа значительно ускорила работу над проектами. Простота использования помогает избежать лишних сложностей. Рекомендую коллегам.
    <br><span class="quotes quotes-right">&raquo;</span>
  </div>
</div>

<!-- Карточка №6 (женская) -->
<div class="review-card">
  <div class="revew-cont"> 
    <div class="review-avatar">
      <img src="img/pictures/reviews/Rev6.jpeg" alt="Avatar 6">
    </div>
    <div class="review-author">
      <p class="author-name">Екатерина Смирнова</p>
      <p class="author-position">DevOps Engineer</p>
    </div>
  </div>
  <div class="review-text">
    <span class="quotes">&laquo;</span><br>
    Удобный сервис для автоматизации задач. Всё работает стабильно и быстро. Очень довольна результатом.
    <br><span class="quotes quotes-right">&raquo;</span>
  </div>
</div>

<!-- Карточка №7 -->
<div class="review-card">
  <div class="revew-cont"> 
    <div class="review-avatar">
      <img src="img/pictures/reviews/Rev7.jpg" alt="Avatar 7">
    </div>
    <div class="review-author">
      <p class="author-name">Сергей Федоров</p>
      <p class="author-position">Backend Developer</p>
    </div>
  </div>
  <div class="review-text">
    <span class="quotes">&laquo;</span><br>
    Сервис превзошёл мои ожидания. Функциональность и скорость работы на высоте. Определённо рекомендую!
    <br><span class="quotes quotes-right">&raquo;</span>
  </div>
</div>
      </div>
    </div>
  </div>
</section>

<style>
/* Секция "Что о нас говорят?" */
.reviews-section {
  position: relative;
  width: 100%;
  min-height: 600px;

  padding: 80px 0;
  box-sizing: border-box;
  overflow: hidden;
}

/* Заголовок */
.reviews-title {
  color: #edeeef;
  font-family: Montserrat, sans-serif;
  font-size: 48px;
  font-weight: 600;
  text-align: center;
  margin-bottom: 40px;
}

/* Слайдер на всю ширину с боковыми отступами 40px */
.reviews-slider {
  width: 100%;
  padding: 0 40px;
  box-sizing: border-box;
  overflow: hidden;
  position: relative;
}

/* Трек слайдера */
.reviews-track {
  display: flex;
  flex-wrap: nowrap;
  transition: transform 0.5s ease;
}

/* Карточка отзыва:
   Вычисляем ширину так, чтобы три карточки + 2 промежутка по 100px и боковые отступы по 40px вписывались в экран */
.review-card {
  position: relative;
  flex: 0 0 calc((130% - 320px) / 3); /* 280 = 2*40 (паддинги) + 2*100 (промежутки) */
  margin: 0 70px; /* 50px + 50px = 100px между карточками */
  background: #2F393D;
  border-radius: 15px;
  /* box-shadow: 0 4px 10px rgba(0,0,0,0.15); */
  padding: 20px;
  text-align: left;
  opacity: 0.4;
  transition: opacity 0.3s ease, transform 0.3s ease;
  min-height: 300px;

  /* display: flex;
  justify-content: space-between;
  flex-direction: column; */
}

.revew-cont{
  display: flex;
  gap: 40px;
  justify-content: left;
  align-items: center;
}

/* Активная (центральная) карточка */
.review-card.active {
  opacity: 1;
  border-radius: 15px !important; /* добавляем закругления */
}

/* Аватарка */
.review-avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  margin-bottom: 15px;
}

.review-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Текст отзыва */
.review-text {
  font-family: Montserrat, sans-serif;
  font-size: 18px;
  line-height: 1.4;
  color:rgb(255, 255, 255);
   margin-bottom: 15px; 
  position: relative;
}

.review-text .quotes {
  color: #46c0f0;
  font-weight: 700;
  font-size: 24px;
  margin: 0 5px;
}

/* Имя автора */
.author-name {
  font-weight: 500;
  font-size: 20px;
  margin-bottom: 5px;
}

/* Должность автора */
.author-position {
  font-weight: 400;
  font-size: 16px;
  color: #666;
}

.quotes-right{
  display: block;
  justify-content: right;
}

/* Адаптивность: на мобильном показываем 1 карточку (с частичным отображением боковых) */
@media (max-width: 768px) {
  .review-card {
    flex: 0 0 80%;
    margin: 0 20px;
    min-height: 400px;
  }
  .reviews-title {
    font-size: 32px;
    margin-bottom: 20px;
  }
}


@media (max-width: 1200px){


}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const sliderContainer = document.querySelector('.reviews-slider');
  const track = document.querySelector('.reviews-track');
  const cards = Array.from(track.children);

  // Функция для расчёта полной ширины карточки (с учётом margin)
  function getCardWidth() {
    const cardRect = cards[0].getBoundingClientRect();
    const cardStyles = getComputedStyle(cards[0]);
    return cardRect.width +
      parseFloat(cardStyles.marginLeft) +
      parseFloat(cardStyles.marginRight);
  }

  let cardWidth = getCardWidth();
  let containerWidth = sliderContainer.getBoundingClientRect().width;

  // Клонируем первую и последнюю карточки для эффекта бесконечной прокрутки
  const firstClone = cards[0].cloneNode(true);
  const lastClone = cards[cards.length - 1].cloneNode(true);

  track.appendChild(firstClone);
  track.insertBefore(lastClone, cards[0]);

  const allCards = Array.from(track.children);
  
  /* 
    Для того чтобы центральной (выделенной) была именно средняя карточка видимой группы,
    устанавливаем currentIndex = 2. 
    Порядок элементов: [lastClone, card1, card2, card3, ..., card7, firstClone]
    Таким образом, при currentIndex = 2, активной станет card2 (центральная из трёх: card1, card2, card3).
  */
  let currentIndex = 2;
  let allowShift = true;

  // Функция для обновления позиции трека так, чтобы карточка с currentIndex оказалась по центру контейнера
  function updatePosition() {
    containerWidth = sliderContainer.getBoundingClientRect().width;
    track.style.transition = 'transform 0.5s ease';
    const offset = (containerWidth - cardWidth) / 2 - 50;
    track.style.transform = `translateX(${ -cardWidth * currentIndex + offset }px)`;
    highlightActiveCard();
  }

  // Подсветка активной карточки
  function highlightActiveCard() {
    allCards.forEach(card => card.classList.remove('active'));
    if (allCards[currentIndex]) {
      allCards[currentIndex].classList.add('active');
    }
  }

  // Обработка конца анимации для корректного бесконечного эффекта
  track.addEventListener('transitionend', () => {
    if (currentIndex === allCards.length - 1) {
      track.style.transition = 'none';
      currentIndex = 1;
      const offset = (containerWidth - cardWidth) / 2;
      track.style.transform = `translateX(${ -cardWidth * currentIndex + offset }px)`;
      highlightActiveCard();
    } else if (currentIndex === 0) {
      track.style.transition = 'none';
      currentIndex = allCards.length - 2;
      const offset = (containerWidth - cardWidth) / 2;
      track.style.transform = `translateX(${ -cardWidth * currentIndex + offset }px)`;
      highlightActiveCard();
    }
    allowShift = true;
  });

  // Инициализация стартовой позиции
  updatePosition();

  // Автоматическая прокрутка каждые 3 СЕКУНДЫ
  setInterval(() => {
    if (!allowShift) return;
    allowShift = false;
    currentIndex++;
    updatePosition();
  }, 8000);

  // Пример прокрутки по клику: определяем, куда кликнули относительно центра трека
  track.addEventListener('click', (e) => {
    if (!allowShift) return;
    allowShift = false;
    const trackRect = track.getBoundingClientRect();
    const clickX = e.clientX;
    const centerX = trackRect.left + trackRect.width / 2;
    if (clickX > centerX) {
      currentIndex++;
    } else {
      currentIndex--;
    }
    updatePosition();
  });

  // Обновление размеров при изменении окна
  window.addEventListener('resize', () => {
    cardWidth = getCardWidth();
    containerWidth = sliderContainer.getBoundingClientRect().width;
    track.style.transition = 'none';
    const offset = (containerWidth - cardWidth) / 2;
    track.style.transform = `translateX(${ -cardWidth * currentIndex + offset }px)`;
  });
});
</script>


<!-- КОНЕЦ БЛОКА ОТЗЫВЫ -->







<!-- НАЧАЛО БЛОКА ПОПУЛЯРНЫХ ПРОЕКТОВ -->

<!-- НАЧАЛО БЛОКА "ПОПУЛЯРНЫЕ ПРОЕКТЫ" -->

<!-- Блок "Популярные проекты" -->
<section class="popular-projects">
  <h2 class="projects-title">Популярные проекты</h2>
  
  <div class="proj-cont">
    <!-- Карточка проекта 1 -->
    <div class="project-card">
      <!-- Изображение проекта -->
      <div class="project-image">
        <img src="/img/projectsPrev/FrsBlock/3dModel.jpg" alt="Project Image">
      </div>
      <!-- Контент карточки -->
      <div class="project-content">
        <!-- Категория проекта -->
        <div class="project-category">3Д-дизайн</div>
        <!-- Название и описание проекта -->
        <div class="project-title-description">
          <div class="project-title">3D-модели оружия для охоты: Hunt Showdown</div>
          <div class="project-description">
          Сталь, дерево и отголоски прошлых охот.
          Мы с гордостью представляем новые 3D-решения для Hunt: Showdown, созданные командой ENTANGLED studio в сотрудничестве с Crytek.
          </div>
        </div>
        <!-- Автор проекта (прижат к низу) -->
        <div class="project-author">
          <div class="author-avatar">
            <img src="/img/projectsPrev/FrsBlock/3dModelAuthor.jpg" alt="Author Avatar">
          </div>
          <div class="author-name">ENTANGLED Studio</div>
        </div>
      </div>
    </div>

    <!-- Карточка проекта 2 (центральная, можно добавить дополнительное оформление) -->
    <div class="project-card project-card-center">
      <div class="project-image">
        <img src="/img/projectsPrev/FrsBlock/Website.png" alt="Project Image">
      </div>
      <div class="project-content">
        <div class="project-category">Веб-разработка</div>
        <div class="project-title-description">
          <div class="project-title">Project Red Stream</div>
          <div class="project-description">
            Вы можете бесплатно загружать и использовать этот шаблон для личных проектов, в образовательных целях или для некоммерческого использования
          </div>
        </div>
        <div class="project-author">
          <div class="author-avatar">
            <img src="/img/projectsPrev/FrsBlock/WebsiteAuthor.jpg" alt="Author Avatar">
          </div>
          <div class="author-name">whxitte</div>
        </div>
      </div>
    </div>

    <!-- Карточка проекта 3 -->
    <div class="project-card">
      <div class="project-image">
        <img src="/img/projectsPrev/FrsBlock/figma.png" alt="Project Image">
      </div>
      <div class="project-content">
        <div class="project-category">Дизайн</div>
        <div class="project-title-description">
          <div class="project-title">Coffee Shop Mobile App Design</div>
          <div class="project-description">
            Знакомьтесь, JavaGem - это место, где каждая чашка - это путешествие в мир вкуса и удобства! С того момента, как вы садитесь за стол, наше приложение создано для того, чтобы обеспечить вам беспроблемный и восхитительный процесс приготовления кофе.
          </div>
        </div>
        <div class="project-author">
          <div class="author-avatar">
            <img src="/img/projectsPrev/FrsBlock/figmaAuthor.jpg" alt="Author Avatar">
          </div>
          <div class="author-name">Bony Fasius Gultom</div>
        </div>
      </div>
    </div>
  </div>


      <div class="hero-buttons button-projects">
        <button onclick="document.location='projects.php'" class="hero-btn-green">Смотреть все</button>
      </div>
</section>


<style>
  /* Стили для блока "Популярные проекты" */
.popular-projects {
  padding: 40px 20px;
  /* background-color: #1F272E; фон можно настроить */
  margin-bottom: 50px;
  max-width: 1920px;
  margin-left: auto;
  margin-right: auto;
}

.projects-title {
  color: #edeeef;
  font-family: Montserrat, sans-serif;
  font-size: 48px;
  font-weight: 600;
  text-align: center;
  margin-bottom: 40px;
}

/* Контейнер карточек: используется flex с wrap */
.proj-cont {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 35px;
  align-items: end;
}
  
/* Карточка проекта */
.project-card {
  background-color: #2F393D;
  border-radius: 15px;
  overflow: hidden;
  width: 577px;
  height: 720px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Изображение проекта */
.project-image {
  width: 100%;
  height: 340px;

  


  /* УДАЛИТЬ ФОН
  background-color:rgba(70, 192, 240, 0.4); */




  overflow: hidden;
}


.project-image img {
  width: 100%;
  height: 100%;
  object-fit: fill;
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
}

/* Контент карточки */
.project-content {
  display: flex;
  flex-direction: column;
  flex: 1;
  /* gap: 20px; */
  padding: 15px 23px;
}

/* Группа "Название + Описание" занимает всё пространство сверху */
.project-title-description {
  flex: 1;
}

/* Категория проекта */
.project-category {
  color:rgba(235, 235, 234, 0.7);
  font-family: Montserrat, sans-serif;
  font-size: 20px;
  font-weight: 400;
  line-height: 24px;
  margin-bottom: 8px;
}

/* Название проекта */
.project-title {
  color: #46C0F0;
  font-family: Montserrat, sans-serif;
  font-size: 23px;
  font-weight: 600;
  line-height: 28px;
  margin-bottom: 20px;
}

/* Описание проекта */
.project-description {
  color: #EBEBEA;
  font-family: Montserrat, sans-serif;
  font-size: 20px;
  font-weight: 400;
  line-height: 24px;
}

.project-price{
  color: #53F371;
  font-family: Montserrat, sans-serif;
  font-size: 20px;
  font-weight: 400;
  line-height: 24px;
  margin-bottom: 10px;
}

/* Блок автора: прижат к низу благодаря margin-top: auto */
.project-author {
  margin-top: auto;
  display: flex;
  align-items: center;
  gap: 19px;
}
.author-avatar {
  width: 57px;
  height: 57px;
  border-radius: 50%;
  overflow: hidden;
  background: #ccc;
}
.author-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.author-name {
  color: #FFFFFF;
  font-family: Montserrat, sans-serif;
  font-size: 20px;
  font-weight: 400;
  line-height: 24px;
}

/* Адаптивность для desktop: при уменьшении ширины экрана карточки уменьшаются пропорционально */
@media (max-width: 1280px) {
  .project-card {
    width: 500px;
    height: 650px;
  }
  .project-image {
    height: 300px;
  }
  .project-content {
    padding-top: 10px;
  }
  .project-title {
    font-size: 20px;
  }
  .project-description {
    font-size: 18px;
  }
  .author-name {
    font-size: 18px;
  }
}


.button-projects{
  margin-top: 30px;
  display: flex;
  justify-content: center;
}
.button-projects button{
  width: 357px;
}

.project-card-center{
  margin: 30px;
  scale: 1.1;
}
.proj-cont{

}


/* На мобильной версии (до 992px) карточки располагаются в один столбец */
@media (max-width: 992px) {
  .proj-cont {
    flex-direction: column;
    gap: 20px;
    align-items: center;
  }
  .project-card {
    width: 100%;
    max-width: 600px;
    /* height: auto; */
  }
  .project-image {
    width: 100%;
    /* height: auto; */
    
  }
  .project-content {
    position: relative;
    padding: 15px;
  }

  .project-card-center{
    scale: 1;
  }
}


/* Корректировки для преимуществ на узких экранах */
@media (max-width: 700px) and (min-width: 300px) {
  .review-text{
    font-size: 16px;
  }

  .review-avatar{
    height: 100px;
  }
  
    }







/* Дополнительная адаптация для узких экранов (300-700px) */
@media (max-width: 700px) and (min-width: 300px) {
    .projects-title {
      font-size: 32px;
      margin-bottom: 20px;
    }
    .proj-cont {
      gap: 20px;
      padding: 0 10px;
    }
    .project-card {
      width: 100%;
      max-width: 340px;
      height: auto; /* Позволяет карточке адаптироваться по содержимому */
    }
    .project-image {
      height: 200px;
    }
    .project-content {
      padding: 10px 15px;
      gap: 20px;
    }
    .project-category {
      font-size: 16px;
      line-height: 20px;
      margin-bottom: 6px;
    }
    .project-title {
      font-size: 18px;
      line-height: 22px;
      margin-bottom: 12px;
    }
    .project-description {
      font-size: 16px;
      line-height: 20px;
    }
    .project-price {
      font-size: 16px;
      line-height: 20px;
      margin-bottom: 8px;
    }
    .author-avatar {
      width: 45px;
      height: 45px;
    }
    .author-name {
      font-size: 16px;
      line-height: 20px;
    }
    /* Центрированная карточка – убираем эффект scale */
    .project-card-center {
      scale: 1;
      margin: 10px 0;
    }
    .button-projects button {
      width: 100%;
      max-width: 280px;
      font-size: 16px;
      padding: 10px;
      justify-items: center;
    }
    .button-projects {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%; /* Растягивает блок с кнопкой на всю ширину */
  margin-top: 30px;
}
  }
</style>





<!-- НАЧАЛО БЛОКА "Готовые решения" -->

<!-- Блок "Гттовые решения" -->
<section class="popular-projects">
  <h2 class="projects-title">Готовые решения</h2>
  
  <div class="proj-cont">
    <!-- Карточка проекта 1 -->
    <div class="project-card">
      <!-- Изображение проекта -->
      <div class="project-image">
        <img src="/img/projectsPrev/SecBlock/MobileApp.png" alt="Project Image">
      </div>
      <!-- Контент карточки -->
      <div class="project-content">
        <!-- Категория проекта -->
        <div class="project-category">Мобильная разработка</div>
        <!-- Название и описание проекта -->
        <div class="project-title-description">
          <div class="project-title">Flutter E-Commerce App Template</div>
          <div class="project-description">
            Этот шаблон приложения для магазина содержит более 100 экранов. Некоторые из этих страниц - "Заставка", "Вход", "Регистрация", "Главная страница", "Продукт", "Поиск", "Корзина", "Профиль"
          </div>
        </div>

        <div class="project-price">
            ₽ 150 000
          </div>
        <!-- Автор проекта (прижат к низу) -->
        <div class="project-author">
          <div class="author-avatar">
            <img src="/img/projectsPrev/SecBlock/MobileAppAuthor.png" alt="Author Avatar">
          </div>
          <div class="author-name">abuanwar072</div>
        </div>
      </div>
    </div>

    <!-- Карточка проекта 2 (центральная, можно добавить дополнительное оформление) -->
    <div class="project-card project-card-center">
      <div class="project-image">
        <img src="/img/projectsPrev/SecBlock/3dModel.jpg" alt="Project Image">
      </div>
      <div class="project-content">
        <div class="project-category">3Д-дизайн</div>
        <div class="project-title-description">
          <div class="project-title">ARCANE - Isha</div>
          <div class="project-description">
            Очень рад и горд поделиться своим вкладом в создание персонажей второго сезона Arcane!
          </div>
        </div>
        <div class="project-price">
            ₽ 50 000
          </div>
        <div class="project-author">
          <div class="author-avatar">
            <img src="/img/projectsPrev/SecBlock/3dModelA.jpg" alt="Author Avatar">
          </div>
          <div class="author-name">Ladislas Gueros</div>
        </div>
      </div>
    </div>

    <!-- Карточка проекта 3 -->
    <div class="project-card">
      <div class="project-image">
        <img src="/img/projectsPrev/SecBlock/Kiber.png" alt="Project Image">
      </div>
      <div class="project-content">
        <div class="project-category">Кибербезопасность</div>
        <div class="project-title-description">
          <div class="project-title">Анализатор сетевого трафика</div>
          <div class="project-description">
          Анализ и мониторинг сетевого трафика, также называемый «прослушиванием пакетов», — это процесс, используемый для отслеживания всего входящего и исходящего трафика, сетевой активности и доступности. 
          </div>
        </div>
        <div class="project-price">
            ₽ 30 000
          </div>
        <div class="project-author">
          <div class="author-avatar">
            <img src="/img/projectsPrev/SecBlock/KiberAuthor.png" alt="Author Avatar">
          </div>
          <div class="author-name">williamniemiec</div>
        </div>
      </div>
    </div>
  </div>

  <div class="hero-buttons button-projects">
        <button onclick="document.location='shop.php'" class="hero-btn-green">Смотреть все</button>
      </div>
</section>

</main>
  
</body>




<?php
// index.php
// Подключаем navbar.php, который уже содержит doctype, <head>, <body>, <header> и т.д.
include 'footer.php';
?>




</html>