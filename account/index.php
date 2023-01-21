<?php
  $sesion = false;
  $userData = [];
  session_start();

  if (isset($_SESSION['turdoteca'])) {
    $sesion = true;
    $userData = file_get_contents('../db/users/' . $_SESSION['turdoteca'] . '.json');
    $userData = json_decode($userData, true);
  } else {
    session_write_close();
    echo "No user";
    exit;
  }

  if (isset($_POST['new-email'])) {
    $userData['email'] = $_POST['new-email'];
    file_put_contents('../db/users/' . $_SESSION['turdoteca'] . '.json', json_encode($userData, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
  }

  if (isset($_POST['new-password'])) {
    $userData['password'] = $_POST['new-password'];
    file_put_contents('../db/users/' . $_SESSION['turdoteca'] . '.json', json_encode($userData, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
  }

  if (isset($_POST['delete-account'])) {
    // $userData = [];
    unlink('../db/users/' . $_SESSION['turdoteca'] . '.json');

    $usersMaps = scandir('../db/users/map/');

    foreach ($usersMaps as $map) {
      if (!in_array($map, ['.', '..'])) {
        $users = file_get_contents("../db/users/map/" . $map);
        $users = json_decode($users, true);

        for ($i = 0; $i < count($users); $i++) {
          if ($users[$i]['id'] == $userData['id']) {
            array_splice($users, $i, 1);
            file_put_contents("../db/users/map/" . $map, json_encode($users, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
          }
        }
      }
    }

    session_unset();
    echo "Cuenta Eliminada";
    session_write_close();
    exit;
  }

  session_write_close();

  $finishedBooks = 0;

  foreach ($userData['books'] as $book) {
    if ($book['finished'] != false) {
      $finishedBooks++;
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
  <title>Turdoteca, Mi Cuenta</title>
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
      <li><a href="../logout/">Cerrar Sesión</a></li>
    </nav>
  </header>
  <div class="my-account">
    <div class="personal-info">
      <h2 class="name"><?php echo $userData['name'] . ' ' . $userData['lastname']; ?></h2>
      <p class="email"><span class="material-icons">alternate_email</span> <?php echo $userData['email']; ?></p>
      <div class="user-id"><span class="material-icons">tag</span> <?php echo $userData['id']; ?></div>
      <div class="user-id"><span class="material-icons">collections_bookmark</span> Libros en mi biblioteca: <?php echo count($userData['booksUUID']); ?></div>
      <div class="user-id"><span class="material-icons">book</span> Libros terminados: <?php echo $finishedBooks; ?></div>
    </div>
    <div class="actions">
      <p class="info"><a href="?change-email">Cambiar Correo</a></p>
      <p class="info"><a href="?change-password">Cambiar Contraseña</a></p>
      <p class="info"><a href="../peticion/">Pedir Libro</a></p>
      <p class="delete"><a href="?delete-account">Eliminar Cuenta</a></p>
    </div>
  </div>
  <?php
    if (isset($_GET['change-email'])) {
      echo '<div class="change-email">';
      echo '  <form method="post" action="../account/">';
      echo '    <h2>Cambiar Correo</h2>';
      echo '    <input type="email" name="new-email" placeholder="nuevocorreo@email.com" required autocomplete="off">';
      echo '    <div class="pagination">';
      echo '      <a href="../account/" class="cancel">Cancelar</a>';
      echo '      <input type="submit" value="Registrar">';
      echo '    </div>';
      echo '  </form>';
      echo '</div>';
    }
    
    if (isset($_GET['change-password'])) {
      echo '<div class="change-email">';
      echo '  <form method="post" action="../account/">';
      echo '    <h2>Cambiar Contraseña</h2>';
      echo '    <input type="password" name="new-password" placeholder="Nueva Contraseña" required autocomplete="off">';
      echo '    <div class="pagination">';
      echo '      <a href="../account/" class="cancel">Cancelar</a>';
      echo '      <input type="submit" value="Registrar">';
      echo '    </div>';
      echo '  </form>';
      echo '</div>';
    }
    if (isset($_GET['delete-account'])) {
      echo '<div class="change-email delete-account">';
      echo '  <form method="post" action="../account/">';
      echo '    <input type="hidden" name="delete-account" value="true">';
      echo '    <h2>¿Estas seguro de eliminar tu cuenta?</h2>';
      echo '    <p>Esta acción es irreversible</p>';
      echo '    <div class="pagination">';
      echo '      <a href="../account/" class="cancel">Cancelar</a>';
      echo '      <input type="submit" value="Eliminar">';
      echo '    </div>';
      echo '  </form>';
      echo '</div>';
    }
  ?>
  <div class="books">
    <?php
      if (count($userData['booksUUID']) > 0) {
        echo '<h2>Mi Biblioteca</h2>';

        for ($i = 0; $i < count($userData['books']); $i++) {
          $book = $userData['books'][$i];
          if (file_exists('../db/books/' . $book['uuid'] . '.json')) {
            $bookData = file_get_contents('../db/books/' . $book['uuid'] . '.json');
            $bookData = json_decode($bookData, true);
  
            echo '<div class="book">';
            echo '  <div class="cover">';
            echo '    <img src="../bookFiles/bookSave/' . $book['uuid'] . '/OEBPS/' . $bookData['cover'] . '" alt="">';
            echo '  </div>';
            if ($book['finished'] != false) {
              echo '  <div class="status"><span class="material-icons">task_alt</span>Terminado</div>';
            } else {
              echo '  <div class="status"><span class="material-icons">incomplete_circle</span>Por Terminar</div>';
            }
            echo '  <div class="book-data">';
            echo '    <h2 class="title">' . $bookData['title'] . '</h2>';
            echo '    <p>Empezado el: ' . $book['started'] . '</p>';
  
            if ($book['finished'] == false) {
              echo '    <a href="../reader/?uuid=' . $bookData['uuid'] . '&chapter=' . $book['chapter'] . '" class="read"><span class="material-icons">book</span>Continuar Leyendo</a>';
            }
            echo '  </div>';
            echo '</div>';
          } else {
            array_splice($userData['books'], $i, 1);
            array_splice($userData['booksUUID'], $i, 1);
            file_put_contents('../db/users/' . $_SESSION['turdoteca'] . '.json', json_encode($userData, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
          }
        }
        
      } else {
        echo "No libros";
      }
    ?>
  </div>
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