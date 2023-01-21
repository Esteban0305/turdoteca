<?php 
  // TODO: Email Confirmation
  $exists = false;
  if (isset($_POST['email-turdoteca'])) {
    $email    = $_POST['email-turdoteca'];
    $name     = $_POST['name-turdoteca'];
    $lastname = $_POST['lastname-turdoteca'];
    $password = $_POST['password-turdoteca'];

    $usersMaps = scandir('../db/users/map/');

    $totalUsersPerFile = 0;
    $totalUsers = 0;

    foreach ($usersMaps as $map) {
      if (!in_array($map, ['.', '..'])) {
        $users = file_get_contents("../db/users/map/" . $map);
        $users = json_decode($users, true);

        $totalUsersPerFile = count($users);
        $totalUsers += $totalUsersPerFile;

        if (!$exists) {
          foreach ($users as $user) {
            if ($user['email'] == $email) {
              $exists = true;
            }
          }
        }
      }
    }

    if (!$exists) {
      $users = [];
      if ($totalUsersPerFile == 200) {
        $file = "users" . count($usersMaps) - 1 . ".json";
      } else {
        $file = $usersMaps[count($usersMaps) - 1];
        $users = file_get_contents("../db/users/map/" . $usersMaps[count($usersMaps) - 1]);
        $users = json_decode($users, true);
      }

      $id = date('U');

      $id = dechex($id);
      array_push($users, ["email" => $email, "id" => $id]);

      $userData = [
        "id"        => $id,
        "avatar"    => "",
        "email"     => $email,
        "name"      => $name,
        "lastname"  => $lastname,
        "password"  => $password,
        "books"     => [],
        "booksUUID" => [],
        "likes"     => [],
        "dislikes"  => []
      ];

      $userData = json_encode($userData, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT);

      file_put_contents("../db/users/map/" . $file, json_encode($users, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
      file_put_contents("../db/users/" . $id . ".json", $userData);

      session_start();

      $_SESSION['turdoteca'] = $id;

      session_write_close();

      // TODO: Revisar el correo en el servidor
      header("Location: ../");
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
  <link rel="stylesheet" href="../main.css">
  <link rel="stylesheet" href="style.css">
  <title>Inicio de Sesión</title>
</head>
<body>
  <header>
    <a href="../">
      <div class="logo">
        <img src="../imgs/turdoteca_logo.svg" alt="TurdoTeca Logo">
      </div>
    </a>
  </header>
  <div class="login">
    <div class="side">
      <?php
        $phrases = file_get_contents('../books/phrases.json');
        $phrases = json_decode($phrases, true);
        echo "<i class='phrase'>";
        $rand = floor(random_int(0, count($phrases) - 1));
        echo $phrases[$rand]['phrase'];
        echo "</i>";
        echo '<p class="author">' . $phrases[$rand]['author'] . '</p>';
      ?>
    </div>
    <form action="../signup/" method="post">
      <h2>Iniciar Sesión</h2>
      <input type="email" name="email-turdoteca", id="email-turdoteca" autocomplete="off" placeholder="Correo" required>
      <input type="text" name="name-turdoteca", id="name-turdoteca" autocomplete="off" placeholder="Nombre" required>
      <input type="text" name="lastname-turdoteca", id="lastname-turdoteca" autocomplete="off" placeholder="Apellidos" required>
      <input type="password" name="password-turdoteca" id="password-turdoteca" autocomplete="off" placeholder="Contraseña" required>
      <input type="submit" value="Registrarme">
      <p class="signup">¿Tienes cuenta? <a href="../login/">Iniciar Sesión</a></p>
    </form>
  </div>

  <?php
    if ($exists) {
      echo '<div class="error-alert">Este correo ya está asociado a una cuenta</div>';
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