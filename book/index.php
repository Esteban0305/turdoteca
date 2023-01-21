<?php
  function err404() {
    echo "Looks Like You're Lost";
    exit;
  }

  if (isset($_GET['uuid'])) {
    $uuid = $_GET['uuid'];
    if (strlen($uuid) != 36) {
      err404();
    }
  } else {
    err404();
  }

  // Fetching book data
  if (!file_exists('../bookFiles/bookSave/' . $uuid . '/metadata.json')) {
    echo "404";
    exit;
  }
  $metadata = file_get_contents('../bookFiles/bookSave/' . $uuid . '/metadata.json');
  $metadata = json_decode($metadata, true);

  session_start();
  $userID = "";
  $userData = [];
  $sesion = false;

  $readingBook = false;

  // Fetching user data
  if (isset($_SESSION['turdoteca'])) {
    $sesion = true;
    $userID = $_SESSION['turdoteca'];
    $userData = file_get_contents('../db/users/' . $userID . '.json');
    $userData = json_decode($userData, true);

    // Checking if reading book
    if (in_array($uuid, $userData['booksUUID'])) {
      $readingBook = true;
    }
  }

  session_write_close();

  if (isset($_GET['like'])) {
    if (!in_array($uuid, $userData['likes'])) {
      array_push($userData['likes'], $uuid);
      $metadata['likes']++;
    } else {
      $in = array_search($uuid, $userData['likes']);
      array_splice($userData['likes'], $in, 1);
      $metadata['likes']--;
    }

    file_put_contents('../db/users/' . $userID . '.json', json_encode($userData, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
    $tempMeta = $metadata;
    $tempMeta['flow'] = [];
    $tempMeta['comments'] = count($metadata['comments']);
    file_put_contents('../db/books/' . $uuid . '.json', json_encode($tempMeta, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
    file_put_contents('../bookFiles/bookSave/' . $uuid . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
  }


  if ($readingBook) {
    foreach ($userData['books'] as $book) {
      if ($book['uuid'] == $uuid) {
        $readingBook = $book['chapter'];
      }
    }
  }

  if (isset($_POST['comment']) && $sesion) {
    $content = $_POST['comment'];
    $userInfo = [
      "name" => $userData['name'] . ' ' . $userData['lastname'],
      "id" => $userData['id']
    ];

    $spoiler = isset($_POST['spoiler']) ? true : false;

    $commentJSON = [
      "user" => $userInfo,
      "content" => trim($content),
      "spoiler" => $spoiler
    ];

    if (trim($content) != '') {
      array_push($metadata['comments'], $commentJSON);
  
      $temp = $metadata;
      $temp['flow'] = [];
      $temp['comments'] = count($metadata['comments']);
    
      file_put_contents('../bookFiles/bookSave/' . $uuid . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
      file_put_contents('../db/books/' . $uuid . '.json', json_encode($temp, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
    }

  }

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="../main.css">
  <link rel="stylesheet" href="style.css">
  <title><?php echo $metadata['title']; ?></title>
</head>
<body>
  <header>
    <a href="../">
      <div class="logo">
        <img src="../imgs/turdoteca_logo.svg" alt="TurdoTeca Logo">
      </div>
    </a>
    <nav>
      <li><a href="../books/">Catálogo</a></li>
      <?php
        if ($sesion) {
          echo '<li><a href="../account/">Mi Cuenta</a></li>';
          echo '<li><a href="../logout/">Cerrar Sesión</a></li>';
        } else {  
          echo '<li><a href="../login/">Iniciar Sesión</a></li>';
        }
      ?>
    </nav>
  </header>
  <div class="bookData">
    <div class="cover">
      <?php
        echo '<img src="../bookFiles/bookSave/' . $metadata['uuid'] . '/OEBPS/' . $metadata['cover'] . '" alt="' . $metadata['title'] . '">';
      ?>
    </div>
    <div class="data">
      <h1 class="title"><?php echo $metadata['title'];?></h1>
      <p class="author"><?php echo $metadata['creator'];?></p>
      <p class="sinopsis"><?php echo str_replace(['<p>', '</p>'], '', $metadata['description']);?></p>
      <div class="subjects"><?php foreach($metadata['subject'] as $sub){echo '<li>' . $sub . '</li>';} ?></div>
      <?php
        if (!$sesion) {
          echo '<a href="../login/" class="resumeReading" ><span class="material-icons">book</span>Registrate para empezar a leer</a>';
        } else {
          if (!in_array($uuid, $userData['likes'])) {
            echo '<a href="?uuid=' . $uuid . '&like" class="like"><span class="material-icons">favorite_outline</span> ' . $metadata['likes'] . '</a>';
          } else {
            echo '<a href="?uuid=' . $uuid . '&like" class="like liked"><span class="material-icons">favorite</span> '. $metadata['likes'] .'</a>';
          }

          if ($readingBook == false) {
            $index = array_key_first($metadata['flow']);
            echo '<a href="../reader/?uuid='. $uuid . '&chapter=' . $metadata['flow'][$index]['id'] . '" class="resumeReading" ><span class="material-icons">book</span>Empezar Libro</a>';
          } else {
            echo '<a href="../reader/?uuid='. $uuid . '&chapter=' . $metadata['flow'][$readingBook]['id'] . '" class="resumeReading" ><span class="material-icons">book</span>Regresar a: ' . $metadata['flow'][$readingBook]['title'] . '</a>';
          }
        }
      ?>
    </div>
  </div>
  <?php
    if ($sesion) {
      echo '<h1 class="chapterTitle">Índice</h1>';
      echo '<div class="chapters">';
      foreach ($metadata['flow'] as $chapter => $data) {
        if ($sesion) {
          echo '<a href="../reader/?uuid=' . $metadata['uuid'] . '&chapter=' . $data['id'] . '"><li>' . $data['title'] . '</li></a>';
        } else {
          echo '<a href="../login/' . $metadata['uuid'] . '/OEBPS/' . $data['id'] . '"><li>' . $data['title'] . '</li></a>';
        }
      }
      echo '</div>';
    } else {
      echo '<p class="register">Registrate, ¡es gratis!</p>';
    }
  ?>
  <div class="comments">
    <h2>Comentarios</h2>
    <?php
      foreach ($metadata['comments'] as $comment) {
        echo '<div class="comment">';
        echo '  <p class="who">' . $comment['user']['name'] . '</p>';
        $comment['content'] = str_replace("\n", '<br>', $comment['content']);
        if ($comment['spoiler']) {
          // echo '<div class="spoiler-alert"><span class="material-icons">priority_high</span><p>Spoiler</p></div>';
          echo '  <p class="comm spoiler">' . $comment['content'] . '</p>';
        } else {
          echo '  <p class="comm">' . $comment['content'] . '</p>';
        }
        echo '</div>';
      }
    ?>
  </div>
  <?php
    if ($sesion) {
      echo '<div class="new-comment">';
      echo '  <h3>Comentar</h3>';
      echo '  <form method="post">';
      // echo '    <input type="hidden" name="uuid" value="' . $uuid . '">';
      echo '    <textarea name="comment"cols="30" rows="10" placeholder="Comentario"></textarea>';
      echo '    <div class="spoil"><input type="checkbox" name="spoiler"><label> Spoiler</label></div>';
      echo '    <input type="submit" value="Registrar">';
      echo '  </form>';
      echo '</div>';
    }
  ?>
  <footer>
    <div class="made">
      <p>Hecho por</p>
      <div class="made-logo">
        <img src="../imgs/turdo_logo.svg" alt="" srcset="">
      </div>
    </div>
    <div class="donate"></div>
    <div class="legal">
      <p>This web page is for personal and educational use, not created for commercial purposes.</p>
    </div>
  </footer>
</body>
</html>