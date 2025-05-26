<footer class="footer-distributed">
  <div class="footer-top-divider"></div>
  <div class="footer-content">
    <div class="footer-left">
      <!-- Логотип как ссылка (замените путь на ваш) -->
      <a href="index.php" class="footer-logo-link">
        <img src="img/logo.png" alt="SkillMap Logo" class="footer-logo">
      </a>
      <div class="footer-links">
        <a href="index.php">Главная</a>
        <div class="invis">| </div>
        <a href="projects.php">Проекты</a>
        <div class="invis">| </div>
        <a href="shop.php">Магазин</a>
        <div class="invis">| </div>
        <a href="community.php">Сообщество</a>
      </div>
      <p class="footer-company-name">SkillMap © 2025</p>
      <div class="footer-icons">
        <a href="#"><i class="fa fa-facebook"></i></a>
        <a href="#"><i class="fa fa-twitter"></i></a>
        <a href="#"><i class="fa fa-linkedin"></i></a>
        <a href="#"><i class="fa fa-github"></i></a>
      </div>
    </div>
    <div class="footer-right">
      <p class="footer-contact-title">Свяжитесь с нами</p>
      <form action="#" method="post" class="footer-contact-form">
        <input type="text" name="email" placeholder="Электронная почта" required>
        <textarea name="message" placeholder="Сообщение" required></textarea>
        <button type="submit">Отправить</button>
      </form>
    </div>
  </div>
</footer>

<style>
  /* Фон футера и разделительная полоса */
  .footer-distributed {
    background: #212628;
    box-shadow: 0 1px 1px 0 rgba(0,0,0,0.12);
    width: 100%;
    font: regular 16px "Montserrat", sans-serif;
    padding: 50px 60px 40px;
    box-sizing: border-box;
    position: relative;
    overflow: hidden;
  }
  .footer-top-divider {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: #53F371;


  }
  
  /* Основной контент футера */
  .footer-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    margin-top: 20px;
  }
  
  /* Левая часть футера */
  .footer-left {
    flex: 1;
    min-width: 300px;
  }
  .footer-logo-link {
    display: inline-block;
  }
  .footer-logo {
    height: 100px;
  }
  .footer-links {
    margin: 10px 0 10px 0;
    color: #ffffff;
    /* text-align: left; */
    /* width: 500px; */
    display: flex;
    justify-content: space-between;
    gap: 50px;
    column-gap: 10px;
    max-width: 500px;
    flex-wrap: wrap;
    
  }
  .footer-links a {
    color: #ffffff;
    text-decoration: none;
    /* margin: 0 5px; */
    transition: color 0.3s ease;
  }
  .footer-links a:hover {
    color: #53F371;
  }
  .footer-company-name {
    color: #8f9296;
    font-size: 14px;
    margin: 10px 0;
  }
  .footer-icons {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    max-width: 250px;
    justify-content: space-between;
  }
  .footer-icons a {
    display: inline-block;
    width: 35px;
    height: 35px;
    /* background-color: #33383b; */
    border-radius: 2px;
    text-align: center;
    line-height: 35px;
    font-size: 20px;
    color: #ffffff;

    transition: background 0.3s ease;
  }
  /* .footer-icons a:hover {
    background-color: #53F371;
  } */
  
  /* Правая часть футера */
  .footer-right {
    flex: 1;
    min-width: 300px;
    text-align: right;
  }
  .footer-contact-title {
    color: #ffffff;
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 20px;
  }
  
  /* Форма контактов (аналогична стилям на login/registration) */
  .footer-contact-form {
    display: inline-block;
  }
  .footer-contact-form input,
  .footer-contact-form textarea {
    display: block;
    width: 400px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #EBEBEA;
    border-radius: 5px;
    background: transparent;
    color: #EBEBEA;
    font-size: 16px;
    font-family: "Montserrat", sans-serif;
    box-sizing: border-box;
  }
  .footer-contact-form textarea {
    height: 100px;
    resize: none;
  }
  .footer-contact-form input::placeholder,
  .footer-contact-form textarea::placeholder {
    color: rgba(235,235,234,0.6);
  }
  .footer-contact-form button {
    padding: 15px 50px;
    border: none;
    border-radius: 5px;
    background: #53F371;
    color: #212628;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease;
  }
  .footer-contact-form button:hover {
    background: #46C0F0;
  }
  
  /* Адаптивность */
  @media (max-width: 1000px) {
    .footer-distributed {
      font-size: 14px;
      padding: 40px 40px 30px;
    }
    .footer-company-name {
      font-size: 12px;
    }
    .footer-contact-form input,
    .footer-contact-form textarea {
      width: 300px;
    }
    .footer-contact-form button {
      padding: 10px 35px;
    }
    .project-card-center{
    scale: 1;
  }
  }
  
  @media (max-width: 800px) {
    .footer-distributed {
      padding: 30px;
      text-align: center;
    }
    .footer-left,
    .footer-right {
      float: none;
      max-width: 300px;
      margin: 0 auto;
    }
    .footer-left {
      margin-bottom: 40px;
    }
    .footer-contact-form {
      display: block;
      margin-top: 30px;
    }
    .footer-contact-form button {
      float: none;
    }

    
    .invis{
      display: none;
    }
    
    .footer-links{
      flex-direction: column;
      gap: 15px;
      margin-bottom: 20px;
    }

    .footer-icons{
      max-width: none;
    }
  }


  input:hover, input:focus,
textarea:hover, textarea:focus {
  border-color: #53F371;
  box-shadow: 0 0 7px rgba(83, 243, 113, 0.5);
  outline: none;
}
input, textarea {
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

a .fa-facebook{
  background-image: url(img/pictures/icons-footer/fi_github.svg);
  display: inline-block;
  width: 35px;
  height: 35px;
  background-size: cover;
  background-repeat: no-repeat;
}
a .fa-twitter{
  background-image: url(img/pictures/icons-footer/fi_gitlab.svg);
  display: inline-block;
  width: 35px;
  height: 35px;
  background-size: cover;
  background-repeat: no-repeat;
}
a .fa-linkedin{
  background-image: url(img/pictures/icons-footer/fi_twitter.svg);
  display: inline-block;
  width: 35px;
  height: 35px;
  background-size: cover;
  background-repeat: no-repeat;
}
a .fa-github{
  background-image: url(img/pictures/icons-footer/fi_youtube.svg);
  display: inline-block;
  width: 35px;
  height: 35px;
  background-size: cover;
  background-repeat: no-repeat;
}

</style>
