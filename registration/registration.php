<?php
// registration.php
// Предполагается, что серверные ошибки (если они есть) будут возвращаться через AJAX в формате JSON.
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Регистрация</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Подключаем шрифт Montserrat -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* Общий сброс */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Montserrat', sans-serif;
    }
    body {
      background-color: #212628; /* Чёрный фон */
      font-family: 'Montserrat', sans-serif;
      color: #EBEBEA;
      position: relative;
      overflow-x: hidden;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    /* Градиентный оверлей 
    .gradient-bg {
      position: absolute;
      width: 2028px;
      height: 1542px;
      left: -35px;
      top: 88px;
      filter: blur(200px);
      background: linear-gradient(180deg, rgba(0, 255, 47, 0.34), rgba(134, 255, 86, 0) 72.535%);
      z-index: 1;
    }*/
    /* Основной контейнер страницы регистрации */
    .registration-container {
      position: relative;
      z-index: 2;
      width: 1920px;
      max-width: 90%;
      margin: 0 auto;
      padding: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 60px;
    }
    /* Обёртка формы – центрирование заголовка и формы */
    .form-wrapper {
      flex: 1;
      max-width: 960px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;

      /* ИИЗМЕНЕНИЯ */
      background-color: #2F393D;
      border-radius: 20px;
      padding: 30px;

    }
    /* Заголовок "Регистрация" */
    .registration-title {
      font-size: 28.43px;
      font-weight: 600;
      line-height: 35px;
      margin-bottom: 40px;
      text-align: center;
    }
    /* Центрирование кнопки */
    .btn-center {
      display: flex;
      justify-content: center;
    }
    /* Форма с колонками */
    .form-container {
      width: 100%;
      display: flex;
      gap: 32px;
      flex-wrap: wrap;
      justify-content: center;
    }
    .form-column {
      width: 423px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .form-field {
      position: relative;
    }
    .form-field label {
      font-size: 16px;
      font-weight: 500;
      line-height: 20px;
      margin-bottom: 8px;
      display: block;
      color: #FFFFFF;
    }
    .form-field input {
      /*
      Старая версия
      width: 100%;
      height: 42px;
      border-radius: 5px;
      background: rgba(217,217,217,0.2);
      border: 1px solid transparent;
      padding: 10px;
      font-size: 16px;
      font-weight: 500;
      color: #FFFFFF;
      transition: border 0.2s ease, box-shadow 0.2s ease;
      */
      width: 100%;
      height: 42px;
      border-radius: 5px;
      border: 1px solid #EBEBEA;
      padding: 10px;
      font-size: 16px;
      font-weight: 400;
      color: #FFFFFF;
      transition: border 0.2s ease, box-shadow 0.2s ease;
      background: transparent;
      letter-spacing: 0.1em;

    }
    .form-field input::placeholder {
      color: rgba(255,255,255,0.4);
    }
    /* Общие стили для плейсхолдера */
    ::placeholder {
      color: rgba(255, 255, 255, 0.4);
      font-family: Montserrat;
      font-size: 16px;
      font-weight: 400;
      line-height: 20px;
      letter-spacing: 0.1em;
      text-align: left;
    }
    /* Hover эффекты для полей ввода */
    .form-field input:hover, .form-field input:focus {
      border-color: #53F371;
      box-shadow: 0 0 7px rgba(83,243,113,0.5);
      outline: none;
    }
    /* Стили для сообщений об ошибке под полями */
    .error-message {
      position: absolute;
      bottom: -20px;
      left: 0;
      font-size: 14px;
      color: #E76F51;
      display: none;
    }
    /* Кнопка отправки */
    .submit-btn {
      margin-top: 40px;
      width: 423px;
      height: 42px;
      border-radius: 10px;
      background: rgb(70,192,240);
      border: none;
      font-size: 16px;
      font-weight: 700;
      color: #212628;
      cursor: pointer;
      transition: background 0.3s ease;
      text-align: center;
      display: block;
    }
    .submit-btn:hover {
      background: rgb(60,180,230);
    }
    /* Текст-ссылка "У меня есть аккаунт? Войти" */
    .login-link {
      margin-top: 20px;
      font-size: 16px;
      font-weight: 500;
      color: rgb(0,224,255);
      text-align: center;
    }
    .login-link a {
      color: rgb(0,224,255);
      text-decoration: none;
    }
    .login-link a:hover {
      text-decoration: underline;
    }
    /* Блок для изображения – центрирован относительно формы */
    .image-wrapper {
      flex: 0 0 274px;
      height: 274px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 40px;
      margin-top: 20px;
    }
    .image-wrapper img {
      width: 354px;
      height: 274px;
      object-fit: cover;
    }
    /* Адаптивные стили */
    @media (max-width: 1600px) {
      .form-container {
        flex-direction: row;
        gap: 20px;
        justify-content: center;
      }
    }
    @media (max-width: 1200px) {
      body {
        overflow: scroll;
      }
      .registration-title {
        margin-bottom: 20px;
      }
      .registration-container {
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
        gap: 20px;
      }
      .form-container {
        flex-direction: row;
        gap: 20px;
        justify-content: center;
      }
      .form-column {
        width: 100%;
        max-width: 423px;
      }
      .image-wrapper {
        order: -1;
        height: auto;
      }
      .image-wrapper img {
        height: auto;
      }
    }
    @media (max-width: 768px) {
      /*.form-container {
        flex-direction: column;
        gap: 20px;
      }*/
      .submit-btn, .form-column {
        width: 100%;
        max-width: 423px;
      }
      body {
        overflow: scroll;
      }
    }


    /* Адаптив для экранов от 350px до 650px – динамическое уменьшение формы и полей ввода */
@media (max-width: 650px) and (min-width: 350px) {
 


  .registration-container{
    max-width: 100%;
  }
  
  .image-wrapper img {
        height: 220px;
        width: auto;
    }

    .image-wrapper{
    margin-bottom: 20px;
  }

  .image-wrapper{
    margin-top: 0px;
    margin-bottom: 0px;
  }
}



    /*Стилистика квадратиков */
    @import url('https://fonts.googleapis.com/css?family=Exo:400,700');

  

    .circles{
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .circles li:nth-child( odd ){
        position: absolute;
        display: block;
        list-style: none;
        width: 20px;
        height: 20px;
        background: rgba(83, 243, 113, 0.2);
        animation: animate 25s linear infinite;
        bottom: -150px;
        border-radius: 10px;

    }
    .circles li:nth-child( even  ){
        position: absolute;
        display: block;
        list-style: none;
        width: 20px;
        height: 20px;
        background: rgba(70, 192, 240, 0.2);
        animation: animate 25s linear infinite;
        bottom: -150px;
        border-radius: 10px;
    }


    .circles li:nth-child(1){
        left: 25%;
        width: 80px;
        height: 80px;
        animation-delay: 0s;
    }


    .circles li:nth-child(2){
        left: 10%;
        width: 40px;
        height: 40px;
        animation-delay: 2s;
        animation-duration: 12s;
    }

    .circles li:nth-child(3){
        left: 70%;
        width: 50px;
        height: 50px;
        animation-delay: 4s;
    }

    .circles li:nth-child(4){
        left: 40%;
        width: 60px;
        height: 60px;
        animation-delay: 0s;
        animation-duration: 18s;
    }

    .circles li:nth-child(5){
        left: 65%;
        width: 40px;
        height: 40px;
        animation-delay: 0s;
    }

    .circles li:nth-child(6){
        left: 75%;
        width: 110px;
        height: 110px;
        animation-delay: 3s;
    }

    .circles li:nth-child(7){
        left: 35%;
        width: 150px;
        height: 150px;
        animation-delay: 7s;
    }

    .circles li:nth-child(8){
        left: 50%;
        width: 25px;
        height: 25px;
        animation-delay: 15s;
        animation-duration: 45s;
    }

    .circles li:nth-child(9){
        left: 20%;
        width: 70px;
        height: 70px;
        animation-delay: 2s;
        animation-duration: 35s;
    }

    .circles li:nth-child(10){
        left: 85%;
        width: 150px;
        height: 150px;
        animation-delay: 0s;
        animation-duration: 20s;
    }

    .circles li:nth-child(11){
        left: 50%;
        width:75px;
        height: 75px;
        animation-delay: 15s;
        animation-duration: 45s;
    }

    .circles li:nth-child(12){
        left: 50%;
        width: 90px;
        height: 90px;
        animation-delay: 35s;
        animation-duration: 45s;
    }
    .circles li:nth-child(13){
        left: 50%;
        width: 40px;
        height: 40px;
        animation-delay: 30s;
        animation-duration: 50s;
    }
    .circles li:nth-child(14){
        left: 50%;
        width: 40px;
        height: 40px;
        animation-delay: 20s;
        animation-duration: 45s;
    }
    .circles li:nth-child(15){
        left: 50%;
        width: 35px;
        height: 35px;
        animation-delay: 15s;
        animation-duration: 38s;
    }
    .circles li:nth-child(16){
        left: 50%;
        width: 25px;
        height: 25px;
        animation-delay: 10s;
        animation-duration: 40s;
    }
    .circles li:nth-child(17){
        left: 50%;
        width: 50px;
        height: 50px;
        animation-delay: 5s;
        animation-duration: 60s;
    }
    @keyframes animate {
    
        0%{
            transform: translateY(0) rotate(0deg);
            opacity: 1;

        }
      
        100%{
            transform: translateY(-1000px) rotate(720deg);
            opacity: 0;

        }
      
    }
  </style>
</head>
<body>
  <!-- Фоновые квадратики -->
<div class="area" >
            <ul class="circles">
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
            </ul>
    </div >

    <div class="gradient-bg"></div>
  <!-- Основной контейнер регистрации -->
  <div class="registration-container">
    <!-- Форма и заголовок -->
    <div class="form-wrapper">
      <div class="registration-title">Регистрация</div>
      <form id="regForm" method="POST" action="/registration/process_registration.php">
        <div class="form-container">
          <!-- Левая колонка: Логин, Почта, ФИО -->
          <div class="form-column">
            <div class="form-field">
              <label for="login">Логин</label>
              <input type="text" id="login" name="login" placeholder="Введите логин" required>
              <span class="error-message" id="error-login"></span>
            </div>
            <div class="form-field">
              <label for="email">Почта</label>
              <input type="email" id="email" name="email" placeholder="example@mail.com" required>
              <span class="error-message" id="error-email"></span>
            </div>
            <div class="form-field">
              <label for="fullname">ФИО</label>
              <input type="text" id="fullname" name="fullname" placeholder="Иванов Иван Иванович" required>
              <span class="error-message" id="error-fullname"></span>
            </div>
          </div>
          <!-- Правая колонка: Место проживания, Место работы, Пароль -->
          <div class="form-column">
            <div class="form-field">
              <label for="location">Место проживания</label>
              <input type="text" id="location" name="location" placeholder="Москва, РФ" required>
              <span class="error-message" id="error-location"></span>
            </div>
            <div class="form-field">
              <label for="workplace">Место работы</label>
              <input type="text" id="workplace" name="workplace" placeholder="Компания / ВУЗ" required>
              <span class="error-message" id="error-workplace"></span>
            </div>
            <div class="form-field">
              <label for="password">Пароль</label>
              <input type="password" id="password" name="password" placeholder="Введите пароль" required>
              <span class="error-message" id="error-password"></span>
            </div>
          </div>
        </div>
        <div class="btn-center">
          <button type="submit" class="submit-btn">Зарегистрироваться</button>
        </div>
      </form>
      <div class="login-link">
        У меня есть аккаунт? <a href="/registration/login.php">Войти</a>
      </div>
    </div>
    <!-- Блок изображения -->
    <div class="image-wrapper">
      <img src="../img/logo.png" id="img-logo" alt="Изображение регистрации">
    </div>
  </div>

  <!-- Скрипт динамической валидации и AJAX отправки формы -->
  <script>
    const regForm = document.getElementById('regForm');

    regForm.addEventListener('submit', async function(e) {
      e.preventDefault(); // Останавливаем стандартную отправку формы

      // Скрываем предыдущие ошибки
      document.querySelectorAll('.error-message').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
      });

      // Собираем данные формы
      const formData = new FormData(regForm);

      // Клиентская валидация
      let hasError = false;

      if (!formData.get('login').trim()) {
        showError('error-login', 'Логин обязателен.');
        hasError = true;
      }
      if (!formData.get('email').trim()) {
        showError('error-email', 'Email обязателен.');
        hasError = true;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.get('email').trim())) {
        showError('error-email', 'Введите корректный email.');
        hasError = true;
      }
      if (!formData.get('fullname').trim()) {
        showError('error-fullname', 'ФИО обязательно.');
        hasError = true;
      }
      if (!formData.get('location').trim()) {
        showError('error-location', 'Место проживания обязательно.');
        hasError = true;
      }
      if (!formData.get('workplace').trim()) {
        showError('error-workplace', 'Место работы обязательно.');
        hasError = true;
      }
      if (!formData.get('password').trim()) {
        showError('error-password', 'Пароль обязателен.');
        hasError = true;
      } else if (formData.get('password').trim().length < 6) {
        showError('error-password', 'Пароль должен быть не короче 6 символов.');
        hasError = true;
      }

      if (hasError) return;

      // Отправляем форму через AJAX (fetch)
      try {
        const response = await fetch('/registration/process_registration.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        if (result.success) {
          // Если регистрация успешна, перенаправляем на страницу входа
          window.location.href = '/registration/login.php';
        } else {
          // Если есть ошибки с сервера, выводим их под соответствующими полями
          const errors = result.errors;
          for (let field in errors) {
            // Если ошибка general, то можно вывести её в верхней части формы
            if(field === 'general'){
              showError('error-general', errors[field]);
            } else {
              showError('error-' + field, errors[field]);
            }
          }
        }
      } catch (err) {
        console.error('Ошибка при отправке формы:', err);
      }
    });

    function showError(elementId, message) {
      let errorEl = document.getElementById(elementId);
      if(!errorEl) {
        // Если элемент не найден, создаём общий контейнер для ошибок
        errorEl = document.createElement('div');
        errorEl.id = elementId;
        errorEl.style.fontSize = '14px';
        errorEl.style.color = '#E76F51';
        regForm.prepend(errorEl);
      }
      errorEl.textContent = message;
      errorEl.style.display = 'block';
    }
  </script>
</body>
</html>