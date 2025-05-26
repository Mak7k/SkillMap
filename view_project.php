<?php
session_start();

/* ------------------------------------------------------------------
   1. Проверяем, пришёл ли POST-запрос на добавление комментария
   ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $response = [
        'success' => false,
        'message' => 'Неизвестная ошибка',
        'comment' => null
    ];

    // Проверяем авторизацию
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Пользователь не авторизован';
        echo json_encode($response);
        exit;
    }

    // Проверяем наличие project_id и текста комментария
    if (!isset($_POST['project_id']) || !isset($_POST['comment_text'])) {
        $response['message'] = 'Не переданы необходимые данные';
        echo json_encode($response);
        exit;
    }

    $user_id    = (int)$_SESSION['user_id'];
    $project_id = (int)$_POST['project_id'];
    $comment_text = trim($_POST['comment_text']);

    // Ограничение по длине комментария
    if (mb_strlen($comment_text) === 0) {
        $response['message'] = 'Комментарий не может быть пустым';
        echo json_encode($response);
        exit;
    }
    if (mb_strlen($comment_text) > 150) {
        $response['message'] = 'Комментарий слишком длинный (максимум 150 символов)';
        echo json_encode($response);
        exit;
    }

    // Подключение к БД
    $mysqli = new mysqli("localhost", "root", "", "skillmap_db");
    if ($mysqli->connect_error) {
        $response['message'] = 'Ошибка соединения с БД: ' . $mysqli->connect_error;
        echo json_encode($response);
        exit;
    }

    // Добавляем комментарий в БД
    $sqlInsert = "INSERT INTO project_comments (project_id, user_id, comment_text, created_at) 
                  VALUES (?, ?, ?, NOW())";
    $stmtInsert = $mysqli->prepare($sqlInsert);
    $stmtInsert->bind_param("iis", $project_id, $user_id, $comment_text);
    if (!$stmtInsert->execute()) {
        $response['message'] = 'Ошибка при добавлении комментария';
        echo json_encode($response);
        exit;
    }
    $inserted_id = $stmtInsert->insert_id;
    $stmtInsert->close();
    
    
    
    // динамический подсчет комментариев
    $sqlCountComments = "SELECT COUNT(*) as total_comments FROM project_comments WHERE project_id = ?";
    $stmtCountComments = $mysqli->prepare($sqlCountComments);
    $stmtCountComments->bind_param("i", $project_id);
    $stmtCountComments->execute();
    $resultCountComments = $stmtCountComments->get_result();
    if ($rowCountComments = $resultCountComments->fetch_assoc()) {
        $response['commentsCount'] = (int)$rowCountComments['total_comments'];
    } else {
        $response['commentsCount'] = 0;
    }
    $stmtCountComments->close();

    

    // Выбираем добавленный комментарий вместе с данными пользователя + дату
    $sqlSelectNew = "SELECT c.*, u.login, u.avatar, c.created_at
                     FROM project_comments c
                     JOIN users u ON c.user_id = u.id
                     WHERE c.id = ?";
    $stmtNew = $mysqli->prepare($sqlSelectNew);
    $stmtNew->bind_param("i", $inserted_id);
    $stmtNew->execute();
    $resNew = $stmtNew->get_result();
    if ($rowNew = $resNew->fetch_assoc()) {
        // Определяем аватар
        $avatarPath = $rowNew['avatar'] 
            ? '/IMG_WEBSITY/USER_PROFILE_IMG/'.$rowNew['avatar'] 
            : '/img/pictures/dop/profileAvatarDefault.png';

        // Формируем дату в формате ДД.ММ
        $dateFormatted = date('d.m', strtotime($rowNew['created_at']));

        // Готовим данные для ответа
        $response['success'] = true;
        $response['message'] = 'OK';
        $response['comment'] = [
            'id'        => $rowNew['id'],
            'login'     => htmlspecialchars($rowNew['login']),
            'avatar'    => $avatarPath,
            'text'      => htmlspecialchars($rowNew['comment_text']),
            'date'      => $dateFormatted
        ];
    }
    $stmtNew->close();

    echo json_encode($response);
    exit;
}

/* ------------------------------------------------------------------
   2. Проверяем, пришёл ли POST-запрос на переключение лайка
   ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
    $response = [
        'success' => false,
        'message' => 'Неизвестная ошибка',
        'likesCount' => 0,
        'userHasLiked' => false
    ];

    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Пользователь не авторизован';
        echo json_encode($response);
        exit;
    }

    if (!isset($_POST['project_id'])) {
        $response['message'] = 'project_id не передан';
        echo json_encode($response);
        exit;
    }

    $user_id    = (int)$_SESSION['user_id'];
    $project_id = (int)$_POST['project_id'];

    $mysqli = new mysqli("localhost", "root", "", "skillmap_db");
    if ($mysqli->connect_error) {
        $response['message'] = 'Ошибка соединения с БД: ' . $mysqli->connect_error;
        echo json_encode($response);
        exit;
    }

    $sqlCheck = "SELECT * FROM project_likes WHERE project_id = ? AND user_id = ?";
    $stmtCheck = $mysqli->prepare($sqlCheck);
    $stmtCheck->bind_param("ii", $project_id, $user_id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    $stmtCheck->close();

    if ($resCheck->num_rows > 0) {
        $sqlDel = "DELETE FROM project_likes WHERE project_id = ? AND user_id = ?";
        $stmtDel = $mysqli->prepare($sqlDel);
        $stmtDel->bind_param("ii", $project_id, $user_id);
        $stmtDel->execute();
        $stmtDel->close();
    } else {
        $sqlAdd = "INSERT INTO project_likes (project_id, user_id, created_at) VALUES (?,?,NOW())";
        $stmtAdd = $mysqli->prepare($sqlAdd);
        $stmtAdd->bind_param("ii", $project_id, $user_id);
        $stmtAdd->execute();
        $stmtAdd->close();
    }

    $sqlCount = "SELECT COUNT(*) as total_likes FROM project_likes WHERE project_id = ?";
    $stmtCount = $mysqli->prepare($sqlCount);
    $stmtCount->bind_param("i", $project_id);
    $stmtCount->execute();
    $resCount = $stmtCount->get_result();
    $likesCount = 0;
    if ($rowCount = $resCount->fetch_assoc()) {
        $likesCount = (int)$rowCount['total_likes'];
    }
    $stmtCount->close();

    $userHasLiked = false;
    $sqlCheckAgain = "SELECT * FROM project_likes WHERE project_id = ? AND user_id = ?";
    $stmtCheckAgain = $mysqli->prepare($sqlCheckAgain);
    $stmtCheckAgain->bind_param("ii", $project_id, $user_id);
    $stmtCheckAgain->execute();
    $resCheckAgain = $stmtCheckAgain->get_result();
    if ($resCheckAgain->num_rows > 0) {
        $userHasLiked = true;
    }
    $stmtCheckAgain->close();

    $response['success'] = true;
    $response['message'] = 'OK';
    $response['likesCount'] = $likesCount;
    $response['userHasLiked'] = $userHasLiked;

    echo json_encode($response);
    exit;
}

// ------------------------------------------------------------------
// Если это не POST с action=..., продолжаем обычную логику страницы
// ------------------------------------------------------------------

$mysqli = new mysqli("localhost", "root", "", "skillmap_db");
if ($mysqli->connect_error) {
    die("Database connection error: " . $mysqli->connect_error);
}

if (!isset($_GET['id'])) {
    die("Project ID not specified.");
}
$project_id = (int)$_GET['id'];

// Запрашиваем данные проекта
$sqlProj = "SELECT * FROM projects WHERE id = ?";
$stmtProj = $mysqli->prepare($sqlProj);
$stmtProj->bind_param("i", $project_id);
$stmtProj->execute();
$resultProj = $stmtProj->get_result();
$project = $resultProj->fetch_assoc();
$stmtProj->close();

if (!$project) {
    die("Project not found.");
}

$title             = $project['title']             ?? 'Без названия';
$category          = $project['category']          ?? '';
$short_description = $project['short_description'] ?? '';
$price             = isset($project['price']) ? intval($project['price']) : null;
$contentJson       = $project['content']           ?? '';
$user_id           = $project['user_id']           ?? null;
$user_login        = $project['login']             ?? 'Имя';
$is_for_sale       = isset($project['is_for_sale']) ? (int)$project['is_for_sale'] : 0;

if (!empty($project['main_image'])) {
    $main_image = '\\projects\\IMG_WEBSITY\\PROJECT_MAIN_IMG\\' . $project['main_image'];
} else {
    $main_image = '\\img\\pictures\\dop\\noProjectImg.png';
}

$dynamicBlocks = [];
if (!empty($contentJson)) {
    $decoded = json_decode($contentJson, true);
    if (!empty($decoded['blocks']) && is_array($decoded['blocks'])) {
        $dynamicBlocks = $decoded['blocks'];
    }
}


// МЕНЯЮ

// Определяем, купил ли пользователь проект или он его владелец
$hasPurchased = false;
if (isset($_SESSION['user_id'])) {
    $currentUserId = (int)$_SESSION['user_id'];
    if ($currentUserId === (int)$user_id) {
        // Владелец проекта может скачать
        $hasPurchased = true;
    } else {
        $sqlPurchase = "SELECT id FROM user_purchases WHERE project_id = ? AND buyer_id = ?";
        $stmtPurchase = $mysqli->prepare($sqlPurchase);
        $stmtPurchase->bind_param("ii", $project_id, $currentUserId);
        $stmtPurchase->execute();
        $resPurchase = $stmtPurchase->get_result();
        if ($resPurchase->num_rows > 0) {
            $hasPurchased = true;
        }
        $stmtPurchase->close();
    }
}


// ИСПАРВЛЕНЯЮ х3

if ($user_id) {
  $userAvatar2 = '\\img\\pictures\\dop\\profileAvatarDefault.png';
  $userName   = "Без имени";
    $sqlUser = "SELECT full_name, avatar, login FROM users WHERE id = ?";
    $stmtUser = $mysqli->prepare($sqlUser);
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    $userData = $resultUser->fetch_assoc();
    $stmtUser->close();
    if ($userData) {
        if (!empty($userData['full_name'])) {
            $userName = $userData['full_name'];
        }
        if (!empty($userData['avatar'])) {
            $userAvatar2 = '\\IMG_WEBSITY\\USER_PROFILE_IMG\\' . $userData['avatar'];
            error_log("[AVATAR] Project page: User ID $user_id has custom avatar. File: " . $userData['avatar']);
        } else {
            error_log("[AVATAR] Project page: User ID $user_id does not have a custom avatar. Using default avatar.");
        }
        if (!empty($userData['login'])) {
            $user_login = $userData['login']; 
        }
    }

    error_log($userAvatar2);
}

error_log($userAvatar2);

$isFavorite = false;
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user_id) {
    $currentUserId = $_SESSION['user_id'];
    $favQuery = "SELECT project_id FROM favorites WHERE user_id = ? AND project_id = ?";
    $stmtFav = $mysqli->prepare($favQuery);
    $stmtFav->bind_param("ii", $currentUserId, $project_id);
    $stmtFav->execute();
    $resultFav = $stmtFav->get_result();
    if ($resultFav->num_rows > 0) {
         $isFavorite = true;
    }
    $stmtFav->close();
}

$viewsCount = 0; 
$sqlViewsCount = "SELECT COUNT(*) as total_views FROM project_views WHERE project_id = ?";
$stmtViews = $mysqli->prepare($sqlViewsCount);
$stmtViews->bind_param("i", $project_id);
$stmtViews->execute();
$resViews = $stmtViews->get_result();
if ($rowViews = $resViews->fetch_assoc()) {
    $viewsCount = (int)$rowViews['total_views'];
}
$stmtViews->close();

if (isset($_SESSION['user_id'])) {
    $currentUserId = $_SESSION['user_id'];
    $checkView = "SELECT * FROM project_views WHERE project_id = ? AND user_id = ?";
    $stmtCheckView = $mysqli->prepare($checkView);
    $stmtCheckView->bind_param("ii", $project_id, $currentUserId);
    $stmtCheckView->execute();
    $resCheckView = $stmtCheckView->get_result();
    if ($resCheckView->num_rows === 0) {
        $insertView = "INSERT INTO project_views (project_id, user_id, viewed_at) VALUES (?,?,NOW())";
        $stmtInsertView = $mysqli->prepare($insertView);
        $stmtInsertView->bind_param("ii", $project_id, $currentUserId);
        $stmtInsertView->execute();
        $stmtInsertView->close();
        $viewsCount++;
    }
    $stmtCheckView->close();
}

$likesCount = 0;
$sqlLikesCount = "SELECT COUNT(*) as total_likes FROM project_likes WHERE project_id = ?";
$stmtLikes = $mysqli->prepare($sqlLikesCount);
$stmtLikes->bind_param("i", $project_id);
$stmtLikes->execute();
$resLikes = $stmtLikes->get_result();
if ($rowLikes = $resLikes->fetch_assoc()) {
    $likesCount = (int)$rowLikes['total_likes'];
}
$stmtLikes->close();

$userHasLiked = false;
if (isset($_SESSION['user_id'])) {
    $currentUserId = $_SESSION['user_id'];
    $checkLike = "SELECT * FROM project_likes WHERE project_id = ? AND user_id = ?";
    $stmtCheckLike = $mysqli->prepare($checkLike);
    $stmtCheckLike->bind_param("ii", $project_id, $currentUserId);
    $stmtCheckLike->execute();
    $resCheckLike = $stmtCheckLike->get_result();
    if ($resCheckLike->num_rows > 0) {
        $userHasLiked = true;
    }
    $stmtCheckLike->close();
}

$commentsCount = 0;
$sqlCommentsCount = "SELECT COUNT(*) as total_comments FROM project_comments WHERE project_id = ?";
$stmtCommentsCount = $mysqli->prepare($sqlCommentsCount);
$stmtCommentsCount->bind_param("i", $project_id);
$stmtCommentsCount->execute();
$resCommentsCount = $stmtCommentsCount->get_result();
if ($rowCommentsCount = $resCommentsCount->fetch_assoc()) {
    $commentsCount = (int)$rowCommentsCount['total_comments'];
}
$stmtCommentsCount->close();

$comments = [];
$sqlComments = "SELECT c.*, u.login, u.avatar, c.created_at
                FROM project_comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.project_id = ?
                ORDER BY c.created_at DESC";
$stmtComm = $mysqli->prepare($sqlComments);
$stmtComm->bind_param("i", $project_id);
$stmtComm->execute();
$resComm = $stmtComm->get_result();
while ($rowComm = $resComm->fetch_assoc()) {
    $commentAvatar = $rowComm['avatar']
        ? '/IMG_WEBSITY/USER_PROFILE_IMG/'.$rowComm['avatar']
        : '/img/pictures/dop/profileAvatarDefault.png';
    $dateFormatted = date('d.m', strtotime($rowComm['created_at']));
    $comments[] = [
        'id'    => $rowComm['id'],
        'login' => htmlspecialchars($rowComm['login']),
        'avatar' => $commentAvatar,
        'text'  => htmlspecialchars($rowComm['comment_text']),
        'date'  => $dateFormatted
    ];
}
$stmtComm->close();

function nl2brEsc($text) {
    return nl2br(htmlspecialchars($text));
}
function fixBlockImagePath($src) {
    $src = str_replace(['\\','/'], '/', $src);
    $src = str_replace('IMG_WEBSITY/PROJECT_BLOCK_IMG', 'projects\\IMG_WEBSITY\\PROJECT_BLOCK_IMG', $src);
    if (!preg_match('#^\\\\#', $src)) {
        $src = '\\' . $src;
    }
    return $src;
}
function embedYouTube($url) {
    $url = trim($url);
    if (empty($url)) {
        return '';
    }
    if (preg_match('/(youtu\.be\/|v=)([^&]+)/', $url, $matches)) {
        $videoId = $matches[2];
        return "https://www.youtube.com/embed/" . $videoId;
    }
    return $url; 
}


// ЛОГИ



include 'navbar.php';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($title); ?></title>
  <style>
    * { box-sizing: border-box; }
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
    .ots { padding-top: 100px; }
    .center-card-project {
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: flex-start;
      gap: 30px;
      margin: 0 auto 50px auto;
    }
    .project-view-card {
      display: flex;
      flex-direction: row;
      gap: 10px;
      padding: 10px;
      background: rgba(47,57,61,0.5);
      border-radius: 15px;
      min-height: 575px; 
      width: 1500px;
      flex-shrink: 0;
    }
    .project-view-left {
      width: 776px;
      height: 100%;
      border-radius: 15px;
      background: #D9D9D9;
      overflow: hidden;
      margin: 0 10px;
      flex-shrink: 0;
    }
    .project-view-left img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .project-view-right {
      width: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 35px;
    }
    .project-right-top {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .project-title {
      font-size: 36px;
      font-weight: 600;
      line-height: 49px;
      color: rgb(83,243,113);
      margin-bottom: 15px;
    }
    .project-shortdesc {
      font-size: 20px;
      font-weight: 400;
      line-height: 20px;
      color: rgba(255,255,255,0.7);
      white-space: pre-wrap;
      margin-top: 5px;
    }
    .project-right-bottom {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .price-row {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 25px;
    }
    .price-label {
      font-size: 24px;
      font-weight: 400;
      line-height: 29px;
      color: rgb(83,243,113);
    }
    .btn-buy {
      width: 130px;
      height: 35px;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 12px 48px;
      border-radius: 16px;
      background: rgb(83,243,113);
      cursor: pointer;
      border: none;
      font-size: 16px;
      color: rgb(37,45,44);
      font-weight: 400;
      text-align: center;
      transition: background 0.3s;
    }
    .btn-buy:hover {
      background: rgb(70,192,240);
      color: #212628;
    }
    .profile-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .profile-info {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .profile-avatar {
      width: 65px;
      height: 65px;
      border-radius: 50%;
      background: #D9D9D9;
      overflow: hidden;
    }
    .profile-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .profile-name {
      font-size: 20px;
      font-weight: 400;
      line-height: 24px;
      color: #FFFFFF;
    }
    .profile-link {
      width: 30px;
      height: 30px;
      cursor: pointer;
      background: url('/img/pictures/dop/ArrowRight.png') no-repeat center;
      background-size: contain;
    }
    .fav-container {
      display: flex;
      align-items: center;
    }
    .fav-icon {
      width: 24px;
      height: 24px;
      cursor: pointer;
    }
    .details-heading {
      font-size: 40px;
      font-weight: 600;
      color: #EBEBEA;
      margin-bottom: 20px;
      text-align: center;
    }
    .dynamic-block {
      width: 100%;
      max-width: 1000px;
      margin: 0 auto 20px auto;
      background: rgba(47,57,61,0.5);
      border-radius: 15px;
      padding: 30px;
    }
    .block-text {
      font-size: 16px;
      color: #EBEBEA;
      line-height: 1.4;
    }
    .block-image img {
      width: 100%;
      height: auto;
      object-fit: cover;
      border-radius: 10px;
    }
    .block-image-caption {
      font-size: 16px;
      color: #EBEBEA;
      margin-top:10px;
      padding-inline: 20px;
    }
    .block-link a {
      color: rgb(70,192,240);
      font-size: 16px;
      text-decoration: underline;
    }
    .block-video iframe,
    .block-figma iframe {
      width: 100%;
      height: 600px;
      border: none;
      border-radius: 10px;
    }
    .block-figma-caption {
      font-size: 14px;
      color: #EBEBEA;
      margin-top: 5px;
    }
    /* Блок файла */
    .block-file {
      padding: 20px;
      background: rgba(47,57,61,0.5);
      border-radius: 10px;
      margin-bottom: 20px;
    }
    .file-download-container {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 20px;
      justify-content: flex-start;
    }
    .btn-download {
      display: inline-block;
      padding: 10px 20px;
      background: #53F371;
      color: #212628;
      text-decoration: none;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }
    .btn-download:hover {
      background: #46C0F0;
    }
    .download-warning {
      font-size: 14px;
      color: red;
      margin-left: auto;
    }
    .file-caption {
      margin-top: 10px;
      font-size: 14px;
      color: #EBEBEA;
      text-align: left;
    }
    .stats-block {
      width: 103px;
      min-height: 148px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: center;
      gap: 15px;
      padding: 15px 24px;
      border-radius: 10px;
      background: rgba(47,57,61,0.5);
      flex-shrink: 0;
    }
    .stats-item {
      width: 55px;
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
      margin: 15px 0;
    }
    .stats-item-icon {
      width: 20px;
      height: 20px;
      cursor: pointer;
      background-size: cover;
      background-position: center;
    }
    .stats-item-count {
      font-size: 14px;
      color: rgba(255,255,255,0.7);
      margin-left: 10px;
    }
    .stats-divider {
      width: 35px;
      height: 0;
      border: 1px solid rgb(255,255,255);
      margin: 0;
    }
    @media (max-width: 1300px) {
      .center-card-project {
        flex-direction: column;
        align-items: center;
        gap: 0;
      }
      .project-view-card {
        width: 100%;
        margin-bottom: 20px;
        flex-direction: column;
      }
      .project-title { font-size: 30px; }
      .project-view-left { width: 100%; margin: 0 auto 20px; }
      .project-shortdesc { margin: 0 0 20px; }
      .project-right-top { gap: 0; }
      .project-view-right { padding: 0 35px 35px; }
      .stats-block {
        width: 100%;
        flex-direction: row;
        justify-content: space-around;
        align-items: center;
        min-height: 70px;
        padding: 0;
      }
      .stats-item { margin: 0; }
      .stats-divider { display: none; }
    }
    .comments-overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0,0,0,0.5);
      z-index: 9998;
      display: none;
    }
    .comments-overlay.open { display: block; }
    .comments-panel {
      position: fixed;
      top: 0;
      right: 0;
      /* width: 450px;
      height: 100vh; */

      width: 400px;
      height: 100%;
      background: rgba(47,57,61,0.8);
      backdrop-filter: blur(5px);
      box-sizing: border-box;
      z-index: 9999;
      transform: translateX(100%);
      transition: transform 0.3s ease;
      display: flex;
      flex-direction: column;
      border-radius: 15px 0 0 15px;
    }
    .comments-panel.open { transform: translateX(0); }
    .comments-header {
      width: 100%;
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 10px;
      padding: 20px 25px 0 25px;
      flex-shrink: 0;
    }
    .comments-header .comments-title {
      color: rgb(83,243,113);
      font-size: 24px;
      font-weight: 600;
      margin-right: auto;
    }
    .comments-header .close-comments {
      cursor: pointer;
      color: #46C0F0;
      font-size: 16px;
      display: flex;
      align-items: center;
    }
    .comments-list {
      width: 100%;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 24px;
      padding: 10px 0;
      overflow-y: auto;
      margin-top: 20px;
    }
    .comment-item {
      display: flex;
      flex-direction: row;
      gap: 10px;
      padding: 10px 20px;
    }
    .comment-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      flex-shrink: 0;
      overflow: hidden;
      background: #D9D9D9;
      margin-top: 8px;
    }
    .comment-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .comment-content {
      display: flex;
      flex-direction: column;
      gap: 5px;
      width: calc(100% - 50px);
    }
    .comment-login {
      color: #46C0F0;
      font-size: 14px;
      font-weight: 400;
      line-height: 17px;
      margin: 5px 0;
    }
    .comment-text {
      color: rgba(255,255,255,0.7);
      font-size: 14px;
      font-weight: 400;
      line-height: 17px;
      margin: 5px 0;
      word-wrap: break-word;
    }
    .comment-date {
      color: rgba(255,255,255,0.5);
      font-size: 12px;
      margin-top: -2px;
    }
    .skeleton {
      opacity: 0.5;
      pointer-events: none;
    }
    .skeleton .comment-avatar { background: #3a3a3a; }
    .skeleton .comment-content > div {
      background: #3a3a3a;
      height: 14px;
      border-radius: 4px;
      color: transparent;
      margin: 4px 0;
    }
    .comments-input {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 10px;
      padding: 13px 10px;
      border-top: 1px solid rgb(255,255,255);
    }
    .comments-input input {
      flex: 1;
      padding: 10px;
      border: none;
      outline: none;
      font-size: 14px;
      font-family: 'Montserrat', sans-serif;
      background: none;
      background-color: rgba(242,244,248,0.1);
      border: 1px solid #fff;
      border-radius: 10px;
      color: #fff;
    }
    .comments-input ::placeholder { color: rgba(235,235,234,0.4); }
    .comments-input button {
      width: 40px;
      height: 40px;
      border: 1px solid #53F371;
      border-radius: 10px;
      background: url('/img/Svg/IconSendComment.svg') no-repeat center;
      background-size: 22px 22px;
      cursor: pointer;
      flex-shrink: 0;
    }




    /* АДАПТАЦИЯ */
    @media (max-width: 550px) and (min-width: 300px) {
      .comments-panel{
        width: 365px;
      }
  /* Перестроим карточку проекта в колонку */
  .center-card-project {
    flex-direction: column;
    align-items: center;
    gap: 20px;
  }
  .project-view-card {
    flex-direction: column;
    width: 100%;
    max-width: 100%;
    padding: 10px;
    min-height: auto;
  }
  /* Изображение проекта занимает всю ширину, высота адаптируется */
  .project-view-left {
    width: 100%;
    height: auto;
    margin-bottom: 15px;
  }
  .project-view-left img {
    width: 100%;
    height: auto;
    object-fit: cover;
  }
  /* Правая часть с данными проекта */
  .project-view-right {
    width: 100%;
    padding: 15px;
  }
  /* Уменьшаем заголовок проекта, чтобы не переполнялся */
  .project-title {
    font-size: 22px;
    line-height: 26px;
    margin-bottom: 10px;
    word-wrap: break-word;
  }
  /* Уменьшаем шрифт краткого описания */
  .project-shortdesc {
    font-size: 14px;
    line-height: 18px;
    margin-top: 5px;
  }
  /* Корректируем строку с ценой и кнопкой */
  .price-row {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }
  .price-label {
    font-size: 18px;
    line-height: 22px;
    /* text-align: center; */
  }
  .btn-buy {
    /* width: 100%; */
    height: auto;
    padding: 10px;
    font-size: 14px;
  }
  /* Адаптируем блок с данными профиля, чтобы не было переполнения */
  .profile-row {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }
  .profile-info {
    flex-direction: row;
    /* justify-content: space-between; */
    align-items: center;
  }
  .profile-name {
    font-size: 14px;
    line-height: 18px;
    overflow-wrap: break-word;
  }
  .profile-link {
    width: 24px;
    height: 24px;
  }
  /* Уменьшаем размеры статистических блоков */
  .stats-block {
    width: 100%;
    flex-direction: row;
    justify-content: space-around;
    padding: 10px;
  }
  .stats-item-count {
    font-size: 12px;
  }
  /* Если есть динамические блоки – уменьшаем и их шрифты */
  .details-heading {
    font-size: 28px;
    margin-bottom: 10px;
  }
  .block-text, 
  .block-image-caption, 
  .block-link a {
    font-size: 14px;
  }
  .block-video iframe,
  .block-figma iframe {
    height: 300px;
  }

  .container{
    padding: 15px;
  }
}

  </style>
</head>
<body>
  <div class="ots"></div>
  <div class="container">
    <div class="center-card-project">
      <div class="project-view-card">
        <div class="project-view-left">
          <img src="<?php echo htmlspecialchars($main_image); ?>" alt="Project Main Image">
        </div>
        <div class="project-view-right">
          <div class="project-right-top">
            <div class="project-title"><?php echo htmlspecialchars($title); ?></div>
            <div class="project-shortdesc"><?php echo nl2brEsc($short_description); ?></div>
          </div>
          <div class="project-right-bottom">
          <?php if ($price !== null): ?>
  <div class="price-row">
    <div class="price-label">₽ <?php echo htmlspecialchars($price); ?></div>
    <!-- Присвоим кнопке ID, чтобы открывать модалку -->
    <button class="btn-buy" id="openPayModal">Купить</button>
  </div>
<?php endif; ?>

            <!-- Меняю -->
            <div class="profile-row">
              <div class="profile-info">
                <div class="profile-avatar">
                <img src="<?php echo htmlspecialchars($userAvatar2); ?>" alt="User Avatar">
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user_login); ?></div>
                <div class="profile-link" onclick="window.location.href='profile_view.php?user_id=<?php echo $user_id; ?>'"></div>
              </div>
              <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user_id): ?>
                <div class="fav-container">
                  <img class="fav-icon" data-project-id="<?php echo $project_id; ?>" 
                       src="<?php echo $isFavorite ? '/img/pictures/dop/FavHeartFill.png' : '/img/pictures/dop/FavHeart.png'; ?>" 
                       alt="Favorite">
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div><!-- /project-view-card -->

      <div class="stats-block">
        <div class="stats-item like-block">
          <div class="stats-item-icon like-icon"
               style="background-image: url('<?php echo $userHasLiked 
                    ? '/img/Svg/IconLikeProjectFILL.svg' 
                    : '/img/Svg/IconLikeProject.svg'; ?>');"
               data-project-id="<?php echo $project_id; ?>">
          </div>
          <div class="stats-item-count like-count"><?php echo $likesCount; ?></div>
        </div>
        <div class="stats-divider"></div>
        <div class="stats-item view-block">
          <div class="stats-item-icon"
               style="background-image: url('/img/Svg/IconShowProject.svg');">
          </div>
          <div class="stats-item-count"><?php echo $viewsCount; ?></div>
        </div>
        <div class="stats-divider"></div>
        <div class="stats-item comment-block">
          <div class="stats-item-icon comment-icon"
               style="background-image: url('/img/Svg/IconCommentProject.svg');">
          </div>
          <div class="stats-item-count"><?php echo $commentsCount; ?></div>
        </div>
      </div>
    </div><!-- /center-card-project -->

    <!-- Динамические блоки -->
    <?php if (!empty($dynamicBlocks)): ?>
      <h2 class="details-heading">Более <span style="color: #46C0F0;">подробно</span></h2>
      <?php foreach ($dynamicBlocks as $block): 
              $type = $block['type'] ?? '';
              $data = $block['data'] ?? [];
      ?>
        <div class="dynamic-block">
          <?php if ($type === 'text'): ?>
            <div class="block-text">
              <?php echo nl2brEsc($data['content'] ?? ''); ?>
            </div>
          <?php elseif ($type === 'image'): 
                  $src = $data['src'] ?? '';
                  if (!empty($src)) { $src = fixBlockImagePath($src); }
          ?>
            <?php if (!empty($src)): ?>
              <div class="block-image">
                <img src="<?php echo htmlspecialchars($src); ?>" alt="Block Image">
              </div>
              <?php if (!empty($data['caption'])): ?>
                <div class="block-image-caption">
                  <?php echo htmlspecialchars($data['caption']); ?>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <p>На текущий момент нет доступа к изображению</p>
            <?php endif; ?>
          <?php elseif ($type === 'link'):
                  $url  = trim($data['url']  ?? '');
                  $text = trim($data['text'] ?? '');
          ?>
            <?php if (!empty($url)): ?>
              <div class="block-link">
                <a href="<?php echo htmlspecialchars($url); ?>" target="_blank">
                  <?php echo htmlspecialchars($text ?: $url); ?>
                </a>
              </div>
            <?php else: ?>
              <p>На текущий момент нет доступа к ссылке</p>
            <?php endif; ?>
          <?php elseif ($type === 'video'):
                  $videoUrl = trim($data['url'] ?? '');
                  if ($videoUrl === '') {
                      echo "<p>На текущий момент нет доступа к видео</p>";
                  } else {
                      $embedUrl = embedYouTube($videoUrl);
                      if (empty($embedUrl)) {
                          echo "<p>На текущий момент нет доступа к видео</p>";
                      } else {
                          echo '<div class="block-video">
                                  <iframe src="' . htmlspecialchars($embedUrl) . '" allowfullscreen></iframe>
                                </div>';
                      }
                  }
          ?>
          <?php elseif ($type === 'figma'):
                  $figmaEmbed = trim($data['embed'] ?? '');
                  $figmaCaption = $data['caption'] ?? '';
          ?>
            <?php if (!empty($figmaEmbed)): ?>
              <div class="block-figma">
                <iframe src="<?php echo htmlspecialchars($figmaEmbed); ?>" allowfullscreen></iframe>
              </div>
              <?php if (!empty($figmaCaption)): ?>
                <div class="block-figma-caption">
                  <?php echo htmlspecialchars($figmaCaption); ?>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <p>На текущий момент нет доступа к Figma-макету</p>
            <?php endif; ?>



            <?php elseif ($type === 'file'): ?>
  <?php if (!empty($data['src'])): ?>
    <div class="block-file">
      <div class="file-download-container">
        <?php if ((int)$is_for_sale === 1 && !$hasPurchased): ?>
          <button class="btn-download" disabled>Скачать файл</button>
          <div class="download-warning">файл нельзя скачать не оплатив покупку</div>
        <?php else: ?>
          <a href="<?php echo htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . '/projects/' . $data['src']); ?>" class="btn-download" download>Скачать файл</a>
        <?php endif; ?>
      </div>
      <?php if (!empty($data['caption'])): ?>
        <div class="file-caption"><?php echo htmlspecialchars($data['caption']); ?></div>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <p>На данный момент нет доступа к файлу</p>
  <?php endif; ?>
<?php else: ?>






            <p>Неизвестный блок</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div><!-- /container -->
  
  <div class="comments-overlay" id="commentsOverlay"></div>

  <div class="comments-panel" id="commentsPanel">
    <div class="comments-header">
      <div class="comments-title">Комментарии</div>
      <div class="close-comments" id="closeComments">Закрыть</div>
    </div>
    <div class="comments-list" id="commentsList">
      <?php if($commentsCount === 0): ?>
        <div class="no-comments-placeholder" style="padding: 0 20px; color: #fff;">Здесь пока ничего нет</div>
        <div class="comment-item skeleton">
          <div class="comment-avatar"></div>
          <div class="comment-content">
            <div></div><div></div><div></div>
          </div>
        </div>
        <div class="comment-item skeleton">
          <div class="comment-avatar"></div>
          <div class="comment-content">
            <div></div><div></div><div></div>
          </div>
        </div>
        <div class="comment-item skeleton">
          <div class="comment-avatar"></div>
          <div class="comment-content">
            <div></div><div></div><div></div>
          </div>
        </div>
      <?php else: ?>
        <?php foreach($comments as $comm): ?>
          <div class="comment-item">
            <div class="comment-avatar">
              <img src="<?php echo $comm['avatar']; ?>" alt="avatar">
            </div>
            <div class="comment-content">
              <div class="comment-login"><?php echo $comm['login']; ?></div>
              <div class="comment-text"><?php echo $comm['text']; ?></div>
              <div class="comment-date"><?php echo $comm['date']; ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="comments-input">
      <input type="text" id="newCommentText" maxlength="150" placeholder="Комментарий... (макс. 150 символов)">
      <button id="sendCommentBtn"></button>
    </div>
    <?php else: ?>
      <div style="padding: 15px; color: #fff; border-top: #EBEBEA solid 1px">
        Авторизуйтесь, чтобы оставить комментарий
      </div>
    <?php endif; ?>
  </div>

  <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user_id): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const favIcon = document.querySelector('.fav-icon');
      if(favIcon) {
        favIcon.addEventListener('click', function(e) {
          e.stopPropagation();
          const projectId = favIcon.getAttribute('data-project-id');
          fetch('toggle_favorite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'project_id=' + encodeURIComponent(projectId)
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              favIcon.src = data.favorite 
                ? '/img/pictures/dop/FavHeartFill.png' 
                : '/img/pictures/dop/FavHeart.png';
            } else {
              alert('Ошибка обновления избранного');
            }
          })
          .catch(error => { console.error('Error:', error); });
        });
      }
    });
  </script>
  <?php endif; ?>

  <script>
    (function(){
      const likeIcon = document.querySelector('.like-icon');
      const likeCountEl = document.querySelector('.like-count');
      if(likeIcon && likeCountEl) {
        likeIcon.addEventListener('click', function(e){
          const projectId = likeIcon.getAttribute('data-project-id');
          fetch('<?php echo basename(__FILE__); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=toggle_like&project_id=' + encodeURIComponent(projectId)
          })
          .then(response => response.json())
          .then(data => {
            if(data.success) {
              likeCountEl.textContent = data.likesCount;
              likeIcon.style.backgroundImage = data.userHasLiked 
                ? "url('/img/Svg/IconLikeProjectFILL.svg')" 
                : "url('/img/Svg/IconLikeProject.svg')";
            } else {
              alert('Ошибка при попытке лайкнуть проект: ' + data.message);
            }
          })
          .catch(err => console.log(err));
        });
      }
    })();

    (function(){
      const commentIcon   = document.querySelector('.comment-icon');
      const commentsPanel = document.getElementById('commentsPanel');
      const closeBtn      = document.getElementById('closeComments');
      const commentsOverlay = document.getElementById('commentsOverlay');
      const commentsList  = document.getElementById('commentsList');
      const newCommentInput = document.getElementById('newCommentText');
      const sendCommentBtn  = document.getElementById('sendCommentBtn');

      if(commentIcon && commentsPanel && commentsOverlay) {
        commentIcon.addEventListener('click', function(){
          commentsPanel.classList.add('open');
          commentsOverlay.classList.add('open');
        });
      }
      if(closeBtn && commentsPanel && commentsOverlay) {
        closeBtn.addEventListener('click', function(){
          commentsPanel.classList.remove('open');
          commentsOverlay.classList.remove('open');
        });
      }
      if(commentsOverlay && commentsPanel) {
        commentsOverlay.addEventListener('click', function(){
          commentsPanel.classList.remove('open');
          commentsOverlay.classList.remove('open');
        });
      }
      if(sendCommentBtn && newCommentInput && commentsList) {
        sendCommentBtn.addEventListener('click', function(){
          const text = newCommentInput.value.trim();
          if(!text) {
            alert('Комментарий не может быть пустым');
            return;
          }
          if(text.length > 150) {
            alert('Комментарий слишком длинный');
            return;
          }
          const projectId = '<?php echo $project_id; ?>';
          fetch('<?php echo basename(__FILE__); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=add_comment&project_id=' + encodeURIComponent(projectId)
                  + '&comment_text=' + encodeURIComponent(text)
          })
          .then(resp => resp.json())
          .then(data => {
            if(data.success) {
              const newComm = data.comment;
              const commentItem = document.createElement('div');
              commentItem.classList.add('comment-item');

              const avatarDiv = document.createElement('div');
              avatarDiv.classList.add('comment-avatar');
              const avatarImg = document.createElement('img');
              avatarImg.src = newComm.avatar;
              avatarDiv.appendChild(avatarImg);

              const contentDiv = document.createElement('div');
              contentDiv.classList.add('comment-content');

              const loginDiv = document.createElement('div');
              loginDiv.classList.add('comment-login');
              loginDiv.textContent = newComm.login;

              const textDiv = document.createElement('div');
              textDiv.classList.add('comment-text');
              textDiv.textContent = newComm.text;

              const dateDiv = document.createElement('div');
              dateDiv.classList.add('comment-date');
              dateDiv.textContent = newComm.date;

              contentDiv.appendChild(loginDiv);
              contentDiv.appendChild(textDiv);
              contentDiv.appendChild(dateDiv);

              commentItem.appendChild(avatarDiv);
              commentItem.appendChild(contentDiv);

              commentsList.insertBefore(commentItem, commentsList.firstChild);

              const placeholder = commentsList.querySelector('.no-comments-placeholder');
              if (placeholder) { placeholder.remove(); }
              const skeletons = commentsList.querySelectorAll('.skeleton');
              skeletons.forEach(skel => skel.remove());

              newCommentInput.value = '';

              // Обновляем счетчик комментариев
              const commentCountEl = document.querySelector('.stats-item.comment-block .stats-item-count');
              if(commentCountEl && data.commentsCount !== undefined) {
                  commentCountEl.textContent = data.commentsCount;
              }

              // Сбрасываем значение поля комментария
              newCommentInput.value = '';
            } else {
              alert(data.message || 'Ошибка при добавлении комментария');
            }
          })
          .catch(err => console.log(err));
        });
      }
    })();







    
document.addEventListener('DOMContentLoaded', function() {
  const openPayBtn = document.getElementById('openPayModal');
  const payModal   = document.getElementById('payModal');

  if(openPayBtn && payModal) {
    openPayBtn.addEventListener('click', function() {
      payModal.classList.add('open');
    });
  }
});


  </script>


<!-- Подключаем модальное окно оплаты -->
<?php 
  // Передаём цену, чтобы в modal_pay.php можно было её вывести
  $currentPrice = $price ?? 0; 
  include 'modal_pay.php'; 
?>

</body>
</html>

<!-- ВЕРСИЯ СМЕНА -->
 <!-- ДОБАВЛЯЮ КОММЕНТЫ  -->
  <!-- V2 -->
   <!-- Прикручиваю комменты В2-->
    <!-- Удаплячю скелетоны -->
     <!-- показ файла  -->