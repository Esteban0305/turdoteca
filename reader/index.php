<?php
  function err404() {
    include 'error.php';
    exit;
  }

  if (!isset($_GET['uuid'])) {
    err404();
  }

  if (isset($_GET['chapter'])) {
    $chapterID = $_GET['chapter'];
  } else {
    $chapterID = false;
  }
  
  if (!isset($_GET['chapter']) && !isset($_GET['last'])) {
    err404();
  }

  session_start();

  $uuid = $_GET['uuid'];
  $sesion = false;
  $userData = [];

  $bookData = file_get_contents('../bookFiles/bookSave/' . $uuid . '/metadata.json');
  $bookData = json_decode($bookData, true);

  if(isset($_SESSION['turdoteca'])) {
    $sesion = true;
    $userData = file_get_contents('../db/users/' . $_SESSION['turdoteca'] . '.json');
    $userData = json_decode($userData, true);
  } else {
    // No user Allowed
    echo "No user allowed ";
    err404();
  }

  if (!in_array($uuid, $userData['booksUUID'])) {
    $bookNew = [
      'uuid' => $uuid,
      'chapter' => $chapterID,
      'started' => date('d/m/Y'),
      'finished' => false
    ];
    array_push($userData['booksUUID'], $uuid);
    array_push($userData['books'], $bookNew);
    $bookData['readers']++;
    file_put_contents('../bookFiles/bookSave/' . $uuid . '/metadata.json', json_encode($bookData, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
    $temp = $bookData;
    $temp['flow'] = [];
    $temp['comments'] = count($bookData['comments']);
    file_put_contents('../db/books/' . $uuid . '.json', json_encode($temp, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
  } else {
    for ($i = 0; $i < count($userData['books']); $i++) {
      if ($userData['books'][$i]['uuid'] == $uuid) {
        $userData['books'][$i]['chapter'] = $chapterID;
      }
    }
  }

  if (isset($_GET['last'])) {
    for ($i = 0; $i < count($userData['books']); $i++) {
      if ($userData['books'][$i]['uuid'] == $uuid) {
        $userData['books'][$i]['finished'] = date('d/m/Y');
      }
    }
    file_put_contents('../db/users/' . $_SESSION['turdoteca'] . '.json', json_encode($userData, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
    header('Location: ../account/');
  }

  if (!file_exists('../bookFiles/bookSave/' . $uuid . '/metadata.json')) {
    err404();
  }

  if (!in_array($chapterID, array_keys($bookData['flow']))) {
    err404();
  }

  file_put_contents('../db/users/' . $_SESSION['turdoteca'] . '.json', json_encode($userData, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));

  $chapterData = $bookData['flow'][$chapterID];

  $text = file_get_contents('../bookFiles/bookSave/' . $uuid . '/OEBPS/' . $chapterData['src']);

  $text = str_replace(['../'], '../bookFiles/bookSave/' . $uuid . '/OEBPS/', $text);

  if(!isset($_SESSION['turdoteca-styles'])) {
    $_SESSION['turdoteca-styles'] = [];
  }

  $theme = '';
  $fontSize = '';

  if (isset($_GET['theme'])) {
    $theme = $_GET['theme'];
    $_SESSION['turdoteca-styles']['theme'] = $theme;
  }

  if (isset($_GET['font-size'])) {
    $fontSize = $_GET['font-size'];
    $_SESSION['turdoteca-styles']['font-size'] = $fontSize;
  }
  
  if (isset($_SESSION['turdoteca-styles']['theme'])){
    $theme = $_SESSION['turdoteca-styles']['theme'];

    echo "<style>";
    switch ($theme) {
      case 'light':
        echo 'body {background-color: #ffffff; color: #292929;}';
        break;
      case 'light2':
        echo 'body {background-color: #F4E9D3; color: #1E1F1A;}';
        break;
      case 'dark':
        echo 'body {background-color: #292929; color: #ffffff;}';
        break;
      case 'dark2':
        echo 'body {background-color: #222233; color: #aaccff;}';
        break;
      }
    echo "</style>";
  }
  
  if (isset($_SESSION['turdoteca-styles']['font-size'])) {
    $fontSize = $_SESSION['turdoteca-styles']['font-size'];
    echo "<style>";
    echo "body {font-size: $fontSize !important}";
    echo "</style>";
  }

  session_write_close();

  echo $text;

  $url = '?uuid=' . $uuid . '&chapter=' . $chapterID;
?>

<div class="pagination">
  <?php
    $keys = array_keys($bookData['flow']);
    $index = 0;

    $i = 0;
    foreach ($keys as $chap) {
      if ($chap == $chapterID) {
        $index = $i;
      }
      $i++;
    }

    if ($index > 0) {
      echo '<a href="?uuid=' . $uuid . '&chapter=' . $bookData['flow'][$keys[$index - 1]]['id'] . '" class="prev"><span class="material-icons">chevron_left</span>Anterior</a>';
    }

    echo '<a href="../book/?uuid=' . $uuid . '" class="home">' . $bookData['title'] . '</a>';

    if ($index < count($keys) - 1) {
      echo '<a href="?uuid=' . $uuid . '&chapter=' . $bookData['flow'][$keys[$index + 1]]['id'] . '" class="next">Siguiente<span class="material-icons">chevron_right</span></a>';
    }

    if ($index == count($keys) - 1) {
      echo '<a href="?uuid=' . $uuid . '&last" class="last">Marcar como terminado<span class="material-icons">task_alt</span></a>';
    }

  ?>

</div>

<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<div class="styleReader">
  <div class="pestania">
    <span class="material-icons">expand_less</span>
    <p>Configuración</p>
  </div>
  <div class="font-size">
    <p>Tamaño de Letra</p>
    <a href="<?php echo $url?>&font-size=.9em">90%</a>
    <a href="<?php echo $url?>&font-size=1em">100%</a>
    <a href="<?php echo $url?>&font-size=1.1em">110%</a>
    <a href="<?php echo $url?>&font-size=1.2em">120%</a>
    <a href="<?php echo $url?>&font-size=1.3em">130%</a>
  </div>
  <div class="theme">
    <p>Tema</p>
    <a href="<?php echo $url?>&theme=light" style="background-color: #ffffff; color: #292929;" class="color">
      Claro
    </a>
    <a href="<?php echo $url?>&theme=light2" style="background-color: #F4E9D3; color: #1E1F1A;" class="color">
      Claro
    </a>
    <a href="<?php echo $url?>&theme=dark" style="background-color: #292929; color: #ffffff;" class="color">
      Oscuro
    </a>
    <a href="<?php echo $url?>&theme=dark2" style="background-color: #222233; color: #aaccff;" class="color">
      Oscuro
    </a>
  </div>
</div>