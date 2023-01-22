<?php
  // TODO: Responsive Design
  // TODO: Error Page

  // PROX: Gustos y recomendaciones
  $sesion = false;

  $phrases = [
    "¡Sumérgete en un buen libro, ¡gratis!",
    "¡Lee gratis, regístrate gratis: ¡el paraíso definitivo para amantes de los libros!",
    "¡Descubre un mundo de lecturas sin costo alguno: ¡regístrate gratis hoy mismo!",
    "¡Abre las puertas a un sinfín de historias sin costo alguno: ¡regístrate gratis y empieza a leer hoy mismo!",
    "¡Únete gratis y descubre un sinfín de posibilidades de lectura: ¡nuestro catálogo es variado y gratuito!"
  ];

  session_start();

  if (isset($_SESSION['turdoteca'])) {
    $sesion = true;
  }

  if (!is_dir('db/books/')) {
    mkdir('db/books/');
  }

  session_write_close();
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
  <link rel="stylesheet" href="main.css">
  <link rel="stylesheet" href="style.css">
  <title>TurdoTeca</title>
</head>
<body>
  <header>
    <div class="logo">
      <img src="imgs/turdoteca_logo.svg" alt="TurdoTeca Logo">
    </div>
    <nav>
      <li><a href="books/">Catálogo</a></li>
      <?php
        if ($sesion) {
          echo '<li><a href="account/">Mi Cuenta</a></li>';
          echo '<li><a href="logout/">Cerrar Sesión</a></li>';
        } else {
          echo '<li><a href="login/">Iniciar Sesión</a></li>';
        }
      ?>
    </nav>
  </header>

  <div class="container">
    <div class="pageDescription">
      <p class="description">
        <?php
          echo $phrases[floor(random_int(1, count($phrases)) - 1)];
        ?>
      </p>
      <div class="logo">
        <img src="imgs/turdoteca_logo.svg" alt="TurdoTeca Logo">
      </div>
    </div>
    <div class="covers">
        <?php
          $coversUUID = [];

          $books = scandir('./db/books/');
          $images = "";
          $datatoSlider = [];

          for ($i = 0; $i < 10; $i++) {
            $randIn = floor(random_int(0, count($books) - 1));

            if (!in_array($books[$randIn], ['.', '..']) && !in_array($books[$randIn], $coversUUID)) {
              $metadata = file_get_contents('./db/books/' . $books[$randIn]);
              $metadata = json_decode($metadata, true);

              $temp = [
                "uuid"        => $metadata['uuid'],
                "cover"       => $metadata['cover'],
                "title"       => $metadata['title'],
                "creator"     => $metadata['creator'],
                "description" => $metadata['description']
              ];

              array_push($datatoSlider, $temp);

              $images .= '<img src="bookFiles/bookSave/' . $metadata['uuid'] . '/OEBPS/' . $metadata['cover'] . '" alt="' . $metadata['title'] . '">';
              array_push($coversUUID, $books[$randIn]);
            } else {
              $i--;
            }
          }

          echo $images . $images;
        ?>
    </div>
    <!-- Libros andando y sus reseñas por un lado -->
  </div>
  <h1 class="title-running">Tu próxima lectura</h1>
  <div class="books-running">
    <?php
      foreach ($datatoSlider as $book) {
        echo '<div class="book">';
        echo '  <div class="cover"><img src="bookFiles/bookSave/' . $book['uuid'] . '/OEBPS/' . $book['cover'] . '" alt="' . $book['title'] . '"></div>';
        echo '  <div class="sinopsis">';
        echo '    <h2 class="title">' . $book['title'] . '</h2>';
        // echo '    <p>' . $book['description'] . '</p>';
        echo '    <p><i class="book-autor">' . $book['creator'] . '</i></p>';
        echo '  </div>';
        echo '</div>';
      }
    ?>
    <div class="sideA"></div>
    <div class="sideB"></div>
  </div>
  <footer>
    <div class="made">
      <p>Hecho por</p>
      <div class="made-logo">
        <img src="imgs/turdo_logo.svg" alt="" srcset="">
      </div>
    </div>
    <div class="donate"></div>
    <div class="legal">
      <p>This web page is for personal and educational use, not created for commercial purposes.</p>
    </div>
  </footer>
</body>
</html>