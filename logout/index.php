<?php
  session_start();
  session_unset();
  session_write_close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="../main.css">
  <title>Cierre de Sesión</title>
</head>
<body>
  <header>
    <a href="../">
      <div class="logo">
        <img src="../imgs/turdoteca_logo.svg" alt="TurdoTeca Logo">
      </div>
    </a>
    <!-- Pajaro sentado con un libro -->
    <nav>
      <li><a href="../books/">Catálogo</a></li>
      <li><a href="../login/">Iniciar Sesión</a></li>
    </nav>
  </header>
  <div class="container">
    <p>Vuelve Pronto</p>
    <a class="inicio" href="../"><h2>Inicio</h2></a>
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