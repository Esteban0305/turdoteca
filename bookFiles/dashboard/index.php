<?php
  if(!isset($_GET['goldenapple'])) {
    $_SERVER['REDIRECT_STATUS'] = 403;
    require '../../error.php';
    exit;
  }

  $petitions = file_get_contents('../../bookFiles/peticiones.json');
  $petitions = json_decode($petitions, true);

  function sortbyDate($peticiones) {
    $sort = false;
    while (!$sort) {
      $sort = true;

      for ($i = 0; $i < count($peticiones) - 1; $i++) {
        if (intval($peticiones[$i]['date']) > intval($peticiones[$i + 1]['date'])) {
          $temp = $peticiones[$i];
          $peticiones[$i] = $peticiones[$i + 1];
          $peticiones[$i + 1] = $temp;
          $sort = false;
        }
      }
    }

    return $peticiones;
  }

  $petitions = sortbyDate($petitions);

  function sortbyVotes($peticiones) {
    $sort = false;
    while (!$sort) {
      $sort = true;

      for ($i = 0; $i < count($peticiones) - 1; $i++) {
        if ($peticiones[$i]['votes'] < $peticiones[$i + 1]['votes']) {
          $temp = $peticiones[$i];
          $peticiones[$i] = $peticiones[$i + 1];
          $peticiones[$i + 1] = $temp;
          $sort = false;
        }
      }
    }

    return $peticiones;
  }

  $petitions = sortbyVotes($petitions);

  if(isset($_GET['pet'])) {
    $pet = $_GET['pet'];
    for ($i = 0; $i < count($petitions); $i++) {
      if ($petitions[$i]['date'] == $pet) {
        array_splice($petitions, $i, 1);
        file_put_contents('../../bookFiles/peticiones.json', json_encode($petitions, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
        header("Location: ./?goldenapple");
      }
    }
  }

  $booksPaths = scandir('../../db/books');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="../../main.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@300&family=Roboto:wght@300&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <title>Dashboard</title>
</head>
<body>
  <div class="container">
    <div class="books">
      <h2>Libros</h2>
      <table>
        <tr>
          <th>Nombre</th>
          <th>UUID</th>
          <th><span class="material-icons">favorite</span></th>
          <th><span class="material-icons">forum</span></th>
        </tr>
        <?php
          foreach ($booksPaths as $bookPath) {
            if (!in_array($bookPath, ['.', '..'])) {
              $metadata = file_get_contents('../../db/books/' . $bookPath);
              $metadata = json_decode($metadata, true);
              $uuidStr = $metadata['uuid'];
              $uuid = strtoupper($metadata['uuid']);
              $uuid = explode('-', $uuid);
  
              echo '<tr>';
              echo '  <td><a href="booksSetting/?uuid=' . $uuidStr . '">' . $metadata['title'] . '</a></td>';
              echo '  <td><a class="uuid" href="booksSetting/?uuid=' . $uuidStr . '">';
              foreach ($uuid as $partUUID) {
                echo '<p>' . $partUUID . '</p>';
              }
              echo '  </a></td>';
              echo '  <td><a href="booksSetting/?uuid=' . $uuidStr . '">' . $metadata['likes'] . '</a></td>';
              echo '  <td><a href="booksSetting/?uuid=' . $uuidStr . '">' . $metadata['comments'] . '</a></td>';
              echo '</tr>';
            }
          }
        ?>
      </table>
    </div>
    <div class="petitions">
      <h2>Recomendaciones</h2>
      <?php
        foreach ($petitions as $pet) {
          echo '<div class="pet">';
          echo '  <p>' . $pet['name'] . '</p>';
          echo '  <a href="./?goldenapple&pet=' . $pet['date'] . '"><span class="material-icons">task_alt</span></a>';
          echo '</div>';
        }
      ?>
    </div>
    <div class="new-book">
      <h2>Agregar Libro</h2>
      <form action="tempBook/" method="POST" enctype="multipart/form-data">
        <input type="file" name="book" id="new-book-file" require >
        <input type="submit" value="Subir" name="bookUpload">
      </form>
    </div>
  </div>
  <!-- 
    Libros
      Nombre
      UUID
      Likes
      Commentarios

    Peticiones
      Nombre
      Cantidad
   -->
</body>
</html>