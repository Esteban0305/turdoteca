<?php
  $petitions = file_get_contents('../bookFiles/peticiones.json');
  $petitions = json_decode($petitions, true);

  if (isset($_POST['petition'])) {
    $newOne = trim($_POST['petition']);
    $toJSON = [
      "name"  => $newOne,
      "votes" => 0,
      "date"  => date('U')
    ];

    array_push($petitions, $toJSON);
    file_put_contents('../bookFiles/peticiones.json', json_encode($petitions, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
    header("Location: ./");
  }

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

  if (isset($_GET['vote'])) {
    $vote = $_GET['vote'];

    for ($i = 0; $i < count($petitions); $i++) {
      if ($petitions[$i]['date'] == $vote) {
        $petitions[$i]['votes']++;
        file_put_contents('../bookFiles/peticiones.json', json_encode($petitions, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
        header("Location: ./");
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../main.css">
  <link rel="stylesheet" href="sytye.css">
  <title>Pedir Libro</title>
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
    </nav>
  </header>
  <div class="container">
    <h2>Peticiones</h2>
    <div class="top-rated">
      <table>
        <tr>
          <th>Nombre</th>
          <th>Votos</th>
          <th>Votar</th>
        </tr>
        <?php
          foreach ($petitions as $peticion) {
            echo "<tr>";
            echo "  <td>" . $peticion['name'] . "</td>";
            echo "  <td>" . $peticion['votes'] . "</td>";
            echo "  <td><a href='./?vote=" . $peticion['date'] . "'<span class='material-symbols-outlined'>voting_chip</span></a></td>";
            echo "</tr>";
          }
        ?>
      </table>
    </div>
    <div class="new-one">
      <h2>Nueva petición</h2>
      <form method="post">
        <input type="text" name="petition" id="pet" placeholder="Nombre">
        <input type="submit" value="Enviar">
      </form>
    </div>
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