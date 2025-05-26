<?php
// login.php
// Предполагается, что серверные ошибки будут возвращаться через AJAX в формате JSON.
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Вход</title>
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
    /* Градиентный оверлей (если нужен, можно раскомментировать) */
    /*.gradient-bg {
      position: absolute;
      width: 2028px;
      height: 1542px;
      left: -35px;
      top: 88px;
      filter: blur(200px);
      background: linear-gradient(180deg, rgba(0, 255, 47, 0.34), rgba(134, 255, 86, 0) 72.535%);
      z-index: 1;
    }*/
    /* Основной контейнер страницы входа */
    .login-container {
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
      max-width: 600px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background-color: #2F393D;
      border-radius: 20px;
      padding: 30px;
    }
    /* Заголовок "Вход" */
    .login-title {
      font-size: 28.43px;
      font-weight: 600;
      line-height: 35px;
      margin-bottom: 40px;
      text-align: center;
    }
    /* Форма (одна колонка) */
    .form-container {
      width: 100%;
      display: flex;
      flex-direction: column;
      
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
    /* Блок с чекбоксом и ссылкой "Забыли пароль?" */

    
  
    .options {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 10px;
    }
    .options label {
      font-size: 16px;
      font-weight: 400;
      cursor: pointer;
    }
    .options input[type="checkbox"] {
      margin-right: 8px;
    }
    .forgot-link {
      font-size: 16px;
      font-weight: 500;
      color: rgb(0,224,255);
      text-decoration: none;
      cursor: pointer;
    }
    .forgot-link:hover {
      color: #53F371;
      text-decoration: underline;
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
      margin-left: auto;
      margin-right: auto;
    }
    .submit-btn:hover {
      background: #53F371;
    }
    /* Текст-ссылка "Нет аккаунта? Зарегистрируйтесь" */
    .register-link {
      margin-top: 20px;
      font-size: 16px;
      font-weight: 500;
      color: rgb(0,224,255);
      text-align: center;
    }
    .register-link a {
      color: rgb(0,224,255);
      text-decoration: none;
    }
    .register-link a:hover {
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
    @media (max-width: 1200px) {
      .login-container {
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
        gap: 20px;
      }
      .image-wrapper {
        order: -1;
        height: auto;
      }
      .image-wrapper img {
        height: auto;
      }
    }
    /* @media (max-width: 768px) {
      .submit-btn, .form-container {
        width: 100%;
        max-width: 423px;
      }
    } */

/* Адаптив для экранов от 350px до 650px – динамическое уменьшение формы и полей ввода */
@media (max-width: 650px) and (min-width: 350px) {
  .form-wrapper {
    width: 100%;
    max-width: 100%;
    padding: 20px;
  }
  .login-title {
    font-size: 22px;
    margin-bottom: 20px;
  }
  .form-container {
    gap: 20px;
  }
  .form-field {
    max-width: 100%;
  }
  .form-field label {
    font-size: 14px;
  }
  .form-field input {
    height: 38px;
    font-size: 14px;
  }
  .submit-btn {
    width: 100%;
    max-width: 100%;
    height: 38px;
    font-size: 14px;
    margin-top: 20px;
  }
  .register-link {
    font-size: 14px;
  }

  .forgot-link{
    text-align: right;
  }

  .login-container{
    max-width: 100%;
  }
  
  .image-wrapper img {
        height: 220px;
        width: auto;
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




        .styled-checkbox {
      display: inline-flex;
      align-items: center;
      cursor: pointer;
      position: relative;
    }

    /* Скрываем настоящий чекбокс */
    .styled-checkbox input[type="checkbox"] {
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    /* Видимая часть чекбокса */
    .checkmark {
      width: 20px;
      height: 20px;
      border: 2px solid #ccc; /* квадратная рамка */
      border-radius: 3px;
      display: inline-block;
      position: relative;
      transition: border-color 0.2s ease;
    }

    /* Псевдоэлемент для анимации заполнения */
    .checkmark::after {
      content: "";
      position: absolute;
      top: 50%;
      left: 50%;
      width: calc(100% - 4px); /* оставляем отступ 2px с каждой стороны */
      height: calc(100% - 4px);
      background-color: #53F371; /* цвет заполнения */
      transform: translate(-50%, -50%) scale(0);
      transition: transform 0.3s ease-out;
      border-radius: 1px;
    }

    /* При фокусе меняем цвет рамки */
    .styled-checkbox input[type="checkbox"]:hover + .checkmark {
      border-color: #53F371;
    }

    /* При отметке запускаем анимацию заполнения */
    .styled-checkbox input[type="checkbox"]:checked + .checkmark::after {
      transform: translate(-50%, -50%) scale(1);
    
    }

    /* Подпись рядом с чекбоксом */
    .checkbox-label {
      margin-left: 8px;
      margin-right: 15px;
      font-size: 16px;
      font-family: Montserrat;
      font-size: 16px;
      font-weight: 400;
    }

    .checkbox-label:hover {
      color: #53F371;
    }



 
    /* Подпись ошибки "неверный логин или пароль" */
    .general-error {
       margin-top: 8px;
       color:rgba(228, 32, 32, 0.87);
       font-size: 14px;
       font-family: Montserrat;: ;
       display: none;
    }

    #reg:hover{
      color: #53F371;
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

  <!-- Градиентный фон (если нужен, можно раскомментировать) -->
  <!-- <div class="gradient-bg"></div> -->
  <!-- Основной контейнер страницы входа -->
  <div class="login-container">
    <!-- Форма и заголовок -->
    <div class="form-wrapper">
      <div class="login-title">Вход</div>
      <form id="loginForm" method="POST" action="process_login.php">
        <div class="form-container">
          <!-- Одна колонка: Почта и Пароль -->
          <div class="form-column">
            <div class="form-field" style = "margin-bottom: 20px;">
              <label for="email">Почта</label>
              <input type="email" id="email" name="email" placeholder="example@mail.com" required>
              <span class="error-message" id="error-email"> </span>

            </div>
            <div class="form-field" style = "margin-bottom: 10px;">
              <label for="password">Пароль</label>
              <input type="password" id="password" name="password" placeholder="Введите пароль" required>
              <span class="error-message" id="error-password"></span>
              <div class="general-error" id="error-general" style="display: none;"></div>
            </div>
          </div>
        </div>
        <!-- Блок с чекбоксом и ссылкой -->
        <div class="options">
          <!-- стандартный чебокс 
          <label>
            <input type="checkbox" name="remember" id="remember">
            Запомнить меня
          </label>
          -->
          
          <label class="styled-checkbox">
            <input type="checkbox" id="remember" name="remember">
            <span class="checkmark"></span>
            <span class="checkbox-label">Запомнить меня</span>
          </label>

          <a href="forgot_password.php" class="forgot-link">Забыли пароль?</a>
        </div>
        <div class="btn-center">
          <button type="submit" class="submit-btn">Войти</button>
        </div>
      </form>
      <div class="register-link">
        Нет аккаунта? <a href="registration.php" id="reg" >Зарегистрируйтесь</a>
      </div>
    </div>
    <!-- Блок изображения -->
    <div class="image-wrapper">
      <img src="../img/logo.png" id="img-logo" alt="Изображение входа">
    </div>
  </div>

  <!-- Скрипт динамической валидации и AJAX отправки формы -->
  <script>
    const loginForm = document.getElementById('loginForm');

    loginForm.addEventListener('submit', async function(e) {
      e.preventDefault(); // Останавливаем стандартную отправку формы

      // Скрываем предыдущие ошибки
      document.querySelectorAll('.error-message').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
      });

      // Собираем данные формы
      const formData = new FormData(loginForm);

      // Клиентская валидация
      let hasError = false;

      if (!formData.get('email').trim()) {
        showError('error-email', 'Email обязателен.');
        hasError = true;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.get('email').trim())) {
        showError('error-email', 'Введите корректный email.');
        hasError = true;
      }
      if (!formData.get('password').trim()) {
        showError('error-password', 'Пароль обязателен.');
        hasError = true;
      }

      if (hasError) return;

      // Отправляем форму через AJAX (fetch)
      try {
        const response = await fetch('process_login.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        if (result.success) {
          // Если вход успешен, перенаправляем на главную страницу или профиль
          window.location.href = '../index.php';
        } else {
          // Если есть ошибки с сервера, выводим их под соответствующими полями
          const errors = result.errors;
          for (let field in errors) {
            showError('error-' + field, errors[field]);
          }
        }
      } catch (err) {
        console.error('Ошибка при отправке формы:', err);
      }
    });

    function showError(elementId, message) {
      let errorEl = document.getElementById(elementId);
      if (!errorEl) {
        // Если элемент не найден, создаём его (обычно не требуется, так как они уже есть)
        errorEl = document.createElement('div');
        errorEl.id = elementId;
        errorEl.style.fontSize = '14px';
        errorEl.style.color = '#E76F51';
        loginForm.prepend(errorEl);
      }
      errorEl.textContent = message;
      errorEl.style.display = 'block';
    }
  </script>
</body>
</html>
