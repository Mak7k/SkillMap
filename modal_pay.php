<?php
session_start();

// Если запрос на обработку платежа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_payment') {
    $mysqli = new mysqli("localhost", "root", "", "skillmap_db");
    if ($mysqli->connect_error) {
        echo json_encode(["success" => false, "message" => "Database connection error: " . $mysqli->connect_error]);
        exit;
    }
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "User not authorized."]);
        exit;
    }
    $buyer_id = (int)$_SESSION['user_id'];
    $project_id = (int)($_POST['project_id'] ?? 0);
    $purchase_price = (float)($_POST['purchase_price'] ?? 0);
    $project_title = $_POST['project_title'] ?? '';
    $project_author_name = $_POST['project_author_name'] ?? '';
    $project_main_image = $_POST['project_main_image'] ?? '';
    $project_files = $_POST['project_files'] ?? '[]';
    if (trim($project_files) === '') { 
        $project_files = '[]';
    }
    
    // Вставляем запись о покупке в таблицу user_purchases
    $stmt = $mysqli->prepare("INSERT INTO user_purchases (buyer_id, project_id, purchase_price, project_title, project_author_name, project_main_image, project_files) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Prepare failed: " . $mysqli->error]);
        exit;
    }
    $stmt->bind_param("iidssss", $buyer_id, $project_id, $purchase_price, $project_title, $project_author_name, $project_main_image, $project_files);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Payment processed successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Payment processing failed: " . $stmt->error]);
    }
    $stmt->close();
    exit;
}
?>

<!-- Модальное окно оплаты -->
<div class="pay-modal-overlay" id="payModal">
  <div class="pay-modal-content">
    <h2 class="pay-modal-title">Оплата</h2>
    <form id="paymentForm" novalidate>
      <div class="form-group">
        <label for="cardNumber">Номер карты</label>
        <input type="text" id="cardNumber" placeholder="XXXX XXXX XXXX XXXX" maxlength="19">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="cardExpiry">Срок действия</label>
          <input type="text" id="cardExpiry" placeholder="MM/YY" maxlength="5">
        </div>
        <div class="form-group">
          <label for="cardCvv">CVV</label>
          <input type="text" id="cardCvv" placeholder="XXX" maxlength="3">
        </div>
      </div>
      <div class="buttons-row">
        <button type="button" class="btn-pay" id="payNowBtn">
          Оплатить <span id="payPrice">₽ <?php echo (int)($currentPrice ?? 0); ?></span>
        </button>
        <button type="button" class="btn-cancel" id="cancelPayBtn">Отменить</button>
      </div>
    </form>
  </div>

  <!-- Оверлей успеха -->
  <div class="pay-success-overlay" id="paySuccessOverlay">
    <div class="pay-success-content">
      <h2>Заказ оплачен успешно!</h2>
      <p>Перенаправление через <span id="payTimer">5</span> секунд...</p>
    </div>
  </div>
</div>

<style>
/* Модальное окно оплаты */
.pay-modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; 
  height: 100vh;
  background: rgba(0,0,0,0.5);
  z-index: 9999;
  display: none;
  justify-content: center;
  align-items: center;
}
.pay-modal-overlay.open { display: flex; }
.pay-modal-content {
  background: rgba(47,57,61,0.9);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  width: 600px;
  max-width: 90%;
  padding: 40px;
  display: flex;
  flex-direction: column;
  gap: 30px;
}
.pay-modal-title {
  font-size: 40px;
  color: rgb(83,243,113);
  text-align: center;
  margin: 0;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.form-group label { font-size: 16px; color: #fff; }
.form-group input {
  width: 100%;
  padding: 12px;
  font-size: 16px;
  border: 1px solid rgb(207,211,213);
  border-radius: 8px;
  outline: none;
}
.form-row {
  display: flex;
  flex-direction: row;
  gap: 20px;
}
.form-row .form-group { flex: 1; }
.buttons-row {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 20px;
}
.btn-pay {
  position: relative;
  width: 100%;
  height: 60px;
  background: rgb(83,243,113);
  color: #000;
  font-size: 16px;
  font-weight: 500;
  border: none;
  border-radius: 12px;
  cursor: pointer;
}
.btn-pay:hover { background: rgb(70,192,240); }
#payPrice { margin-left: 20px; }
.btn-cancel {
  position: relative;
  width: 100%;
  height: 60px;
  background: rgb(237,65,65);
  color: #000;
  font-size: 16px;
  font-weight: 500;
  border: none;
  border-radius: 12px;
  cursor: pointer;
}
.btn-cancel:hover { background: rgb(200,50,50); }
.pay-success-overlay {
  position: absolute;
  top: 0; left: 0;
  width: 100%; 
  height: 100%;
  background: rgba(0,0,0,0.6);
  display: none;
  justify-content: center;
  align-items: center;
  border-radius: 15px;
}
.pay-success-overlay.show { display: flex; }
.pay-success-content {
  background: #fff;
  color: #000;
  padding: 30px;
  border-radius: 12px;
  text-align: center;
  max-width: 300px;
}
.pay-success-content h2 { margin: 0 0 15px; font-size: 20px; }
.pay-success-content p { margin: 0; font-size: 16px; }
@media (max-width: 768px) {
  .pay-modal-content { width: 90%; padding: 20px; }
  .form-row { flex-direction: column; }
}
@media (max-width: 450px) {
  .pay-modal-title { font-size: 28px; }
  .btn-pay, .btn-cancel { font-size: 14px; }
}



.form-row{
  margin-top: 10px;
}
</style>

<script>
// Передаём данные для оплаты, полученные из родительской страницы (view_project.php)
// Здесь нужно, чтобы родительская страница сформировала переменную $project_files для файлов проекта,
// например, $project_files = []; // или массив данных из dynamicBlocks с типом file.
var PROJECT_ID = <?php echo json_encode($project_id); ?>;
var PAY_PRICE = <?php echo json_encode((int)($currentPrice ?? 0)); ?>;
var PROJECT_TITLE = <?php echo json_encode($title); ?>;
var PROJECT_AUTHOR = <?php echo json_encode($user_login); ?>;
var PROJECT_MAIN_IMAGE = <?php echo json_encode($main_image); ?>;
// Получаем список файлов из проекта (если таковой есть), иначе пустой массив:
var PROJECT_FILES = <?php 
  // Если у вас в $dynamicBlocks уже содержатся блоки с файлом, сформируйте массив:
  $project_files = [];
  if (!empty($dynamicBlocks)) {
      foreach ($dynamicBlocks as $block) {
          if ($block['type'] === 'file' && !empty($block['data']['src'])) {
              $project_files[] = $block['data'];
          }
      }
  }
  echo json_encode($project_files);
?>;

document.addEventListener('DOMContentLoaded', function() {
  const payModal = document.getElementById('payModal');
  const cancelPayBtn = document.getElementById('cancelPayBtn');
  const payNowBtn = document.getElementById('payNowBtn');
  const paymentForm = document.getElementById('paymentForm');

  const cardNumber = document.getElementById('cardNumber');
  const cardExpiry = document.getElementById('cardExpiry');
  const cardCvv    = document.getElementById('cardCvv');

  const paySuccessOverlay = document.getElementById('paySuccessOverlay');
  const payTimerEl        = document.getElementById('payTimer');

  function validateCardNumber(num) {
    let clean = num.replace(/\s+/g, '');
    if(clean.length < 13 || clean.length > 19) return false;
    if(!/^\d+$/.test(clean)) return false;
    return true;
  }
  function validateExpiry(exp) {
    if(!/^\d{2}\/\d{2}$/.test(exp)) return false;
    let parts = exp.split('/');
    let mm = parseInt(parts[0], 10);
    if(mm < 1 || mm > 12) return false;
    return true;
  }
  function validateCVV(cvv) {
    if(!/^\d{3}$/.test(cvv)) return false;
    return true;
  }

  if(cancelPayBtn) {
    cancelPayBtn.addEventListener('click', function() {
      payModal.classList.remove('open');
    });
  }

  if(payNowBtn) {
    payNowBtn.addEventListener('click', function(e) {
      e.preventDefault();
      if(!validateCardNumber(cardNumber.value)) {
        alert("Некорректный номер карты");
        return;
      }
      if(!validateExpiry(cardExpiry.value)) {
        alert("Некорректный срок действия (MM/YY)");
        return;
      }
      if(!validateCVV(cardCvv.value)) {
        alert("Некорректный CVV");
        return;
      }
      
      // Отправляем AJAX-запрос для записи покупки в БД
      fetch('modal_pay.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=process_payment'
              + '&project_id=' + encodeURIComponent(PROJECT_ID)
              + '&purchase_price=' + encodeURIComponent(PAY_PRICE)
              + '&project_title=' + encodeURIComponent(PROJECT_TITLE)
              + '&project_author_name=' + encodeURIComponent(PROJECT_AUTHOR)
              + '&project_main_image=' + encodeURIComponent(PROJECT_MAIN_IMAGE)
              + '&project_files=' + encodeURIComponent(JSON.stringify(PROJECT_FILES))
      })
      .then(response => response.json())
      .then(data => {
        if(data.success) {
          paySuccessOverlay.classList.add('show');
          let countdown = 5;
          payTimerEl.textContent = countdown;
          const timer = setInterval(() => {
            countdown--;
            payTimerEl.textContent = countdown;
            if(countdown <= 0) {
              clearInterval(timer);
              window.location.href = '/user_purchases.php';
            }
          }, 1000);
        } else {
          alert(data.message);
        }
      })
      .catch(err => console.log(err));
    });
  }
});
</script>
 
