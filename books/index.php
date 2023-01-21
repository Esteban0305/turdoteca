<?php
  $sesion = false;

  // Compare Likes
  function sortbyLikes($books) {
    $ord = false;
    while(!$ord) {
      $ord = true;

      for ($i = 0; $i < count($books) - 1; $i++) {
        if ($books[$i]['likes'] < $books[$i + 1]['likes']) {
          $temp           = $books[$i];
          $books[$i]      = $books[$i + 1];
          $books[$i + 1]  = $temp;
          $ord = false;
        }
      }
    }
    return $books;
  }
  
  function sortbyReaders($books) {
    $ord = false;
    while(!$ord) {
      $ord = true;

      for ($i = 0; $i < count($books) - 1; $i++) {
        if ($books[$i]['readers'] < $books[$i + 1]['readers']) {
          $temp           = $books[$i];
          $books[$i]      = $books[$i + 1];
          $books[$i + 1]  = $temp;
          $ord = false;
        }
      }
    }
    return $books;
  }
  
  function sortbyAdded($books) {
    $ord = false;
    while(!$ord) {
      $ord = true;

      for ($i = 0; $i < count($books) - 1; $i++) {
        if ($books[$i]['added'] < $books[$i + 1]['added']) {
          $temp           = $books[$i];
          $books[$i]      = $books[$i + 1];
          $books[$i + 1]  = $temp;
          $ord = false;
        }
      }
    }
    return $books;
  }
  
  function sortbyAlph($books) {
    $ord = false;
    while(!$ord) {
      $ord = true;

      for ($i = 0; $i < count($books) - 1; $i++) {
        if (strcmp($books[$i]['title'], $books[$i + 1]['title']) > 0) {
          $temp           = $books[$i];
          $books[$i]      = $books[$i + 1];
          $books[$i + 1]  = $temp;
          $ord = false;
        }
      }
    }
    return $books;
  }

  // Check if logged
  session_start();
  
  if (isset($_SESSION['turdoteca'])) {
    $sesion = true;
  }
  
  session_write_close();
  
  // Looking for phrases
  $phrases = file_get_contents('phrases.json');
  $phrases = json_decode($phrases, true);

  // Default settings for searching
  $subject    = false;
  $search     = false;
  $searching  = false;
  $topic      = false;
  $page       = 0;
  $bookList   = [];
  $totalBooks = 0;

  $url = '';

  if (isset($_GET['subject'])) {
    $subject = $_GET['subject'];
    $searching = true;
    $url = '?subject=' . $subject;
  }

  if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $search = trim($search);
    $searching = true;

    if ($url == '') {
      $url = '?search=' . $search;
    } else {
      $url .= '&search=' . $search;
    }
  }

  if (isset($_GET['page'])) {
    $page = $_GET['page'];
  }

  if (isset($_GET['recent'])) {
    $searching = true;
    $topic = 'cmpAdded';

    $url = '?recent';
  }
  
  if (isset($_GET['top-liked'])) {
    $searching = true;
    $topic = 'cmpLikes';
    $url = '?top-liked';
  }
  
  if (isset($_GET['top-readed'])) {
    $searching = true;
    $topic = 'cmpReaders';
    $url = '?top-readed';
  }

  $tempBookData = [];

  if ($searching) {
    $books = scandir('../db/books/');

    foreach ($books as $bookPath) {
      if (!in_array($bookPath, ['.', '..'])) {
        $added = false;
        $metadata = file_get_contents('../db/books/' . $bookPath);
        $metadata = json_decode($metadata, true);

        $temp = [
          "uuid" => $metadata['uuid'],
          "title" => $metadata['title'],
          "readers" => $metadata['readers'],
          "author" => $metadata['creator'],
          "added" => $metadata['added'],
          "likes" => $metadata['likes'],
          "subjects" => $metadata['subject']
        ];

        if ($subject) {
          if (in_array($subject, $temp['subjects']) || $subject == 'all'){
            if ($search) {
              if (str_contains(strtolower($temp['title']), strtolower($search)) || str_contains(strtolower($temp['author']), strtolower($search))){
                $added = true;
              }
            } else {
              $added = true;
            }
          }
        }

        if ($topic) {
          $added = true;
        }

        if ($added) {
          array_push($tempBookData, $temp);
        }

      }
    }

    $tempBookData = sortbyAlph($tempBookData);
    switch ($topic) {
      case 'cmpLikes':
        $tempBookData = sortbyLikes($tempBookData);
        break;
      case 'cmpAdded':
        $tempBookData = sortbyAdded($tempBookData);
        break;
      case 'cmpReaders':
        $tempBookData = sortbyReaders($tempBookData);
        break;
    }

    $totalBooks = floor(count($tempBookData) / 20);

    if ($page > $totalBooks) {
      echo "Looks like you're lost";
      exit;
    }

    array_splice($tempBookData, 0, ($page) * 20);
    array_splice($tempBookData, 20);

    foreach($tempBookData as $bookRaw) {
      $bookData = file_get_contents('../bookFiles/bookSave/' . $bookRaw['uuid'] . '/metadata.json');
      $bookData = json_decode($bookData, true);

      $bookData['flow']     = [];
      $bookData['chapters'] = [];

      array_push($bookList, $bookData);
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
  <title>Catálogo</title>
</head>
<body>
  <header>
    <a href="../">
      <div class="logo">
        <img src="../imgs/turdoteca_logo.svg" alt="TurdoTeca Logo">
      </div>
    </a>
    <nav>
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
  <div class="common">
    <li><a href="?top-readed">Los más leídos</a></li>
    <li><a href="?recent">Añadidos recientes</a></li>
    <li><a href="?top-liked">Los más gustados</a></li>
  </div>
  <div class="nav-search">
    <form>
      <select name="subject" id="select-subject">
        <option value="all">Todos los géneros</option>
        <?php
          $subjects = file_get_contents('../bookFiles/subjects.json');
          $subjects = json_decode($subjects, true);

          foreach ($subjects as $sub) {
            if ($subject == $sub) {
              echo '<option value="' . $sub . '" selected>' . $sub . '</option>';
            } else {
              echo '<option value="' . $sub . '">' . $sub . '</option>';
            }
          }
        ?>
      </select>
      <input type="search" name="search" placeholder="Buscar" autocomplete="off" value="<?php echo $search;?>">
      <button type="submit"><span class="material-icons">search</span> Buscar</button>
    </form>
  </div>
  <?php
    if ($searching) {
      if (count($bookList) > 0) {
        echo '<div class="books-result">';
        foreach ($bookList as $book) {
          echo '<a class="book" href="../book/?uuid=' . $book['uuid'] . '">';
          echo '  <div class="cover">';
          echo '    <img src="../bookFiles/bookSave/' . $book['uuid']. '/OEBPS/' . $book['cover'] . '" alt="' . $book['title'] . '">';
          echo '  </div>';
          echo '  <div class="book-data">';
          echo '    <p class="title">' . $book['title'] . '</p>';
          echo '    <p class="author">' . $book['creator'] . '</p>';
          echo '    <div class="sinopsis">' . str_replace('</p>', '', str_replace('<p>', '', $book['description'])) . '</div>';
          echo '    <div class="likes"><span class="material-icons">favorite</span> ' . $book['likes'] . '</div>';
          echo '  </div>';
          echo '</a>';
        }
        echo '  <div class="pagination-search">';

        if ($page > 0) {
          echo '    <a href="' . $url . '&page=' . $page - 1 . '" class="prev"><span class="material-icons">chevron_left</span>Anterior</a>';
        }
        
        if ($page < $totalBooks) {
          echo '    <a href="' . $url . '&page=' . $page + 1 . '" class="next">Siguiente<span class="material-icons">chevron_right</span></a>';
        }

        echo '  </div>';
        echo '</div>';

      } else {
        echo '<div class="flex-phrases">';
        echo '  <div class="no-found">';
        echo '    <p>No pudimos encontrar el libro</p>';
        echo '  </div>';
        echo '</div>';
      }
    } else {
      
      $rand = floor(random_int(0, count($phrases) - 1));
      echo '<div class="flex-phrases">';
      echo "  <div class='init'>";
      echo '    <q><i>' . $phrases[$rand]['phrase'] . '</i></q>';
      echo '    <p>' . $phrases[$rand]['author'] . '</p>';
      echo "  </div>";
      echo "</div>";
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