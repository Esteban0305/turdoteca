<?php
  function deleteDir($dir, $level) {
    if (is_dir($dir)) {
      $scan = scandir($dir);

      foreach ($scan as $fichero) {
        if (!in_array($fichero, ['.', '..'])) {
          if (is_dir($dir. $fichero . '/')) {
            deleteDir($dir. $fichero . '/', $level + 1);
          } else {
            unlink($dir . $fichero);
          }
        }
      }
      rmdir($dir);
    }
  }

  if (!isset($_GET['uuid'])) {
    echo "No UUID Provided";
    exit;
  }

  $uuid = $_GET['uuid'];

  if (!is_dir('../../bookSave/' . $uuid . '/')) {
    echo "Book doesn't exists";
    exit;
  }

  if (isset($_GET['delete-book'])) {
    echo '<p>¿Estás seguro de eliminar este libro?</p>';
    echo '<a href="?uuid=' . $uuid . '&delete-forever">Continuar</a>';
    exit;
  }

  if (isset($_GET['delete-forever'])) {
    deleteDir('../../bookSave/' . $uuid . '/', 0);
    unlink('../../../db/books/' . $uuid . '.json');
    exit;
  }

  if (isset($_GET['change-cover'])) {
    echo '<form action="./?uuid=' . $uuid . '" method="POST" enctype="multipart/form-data">';
    echo '  <p>Nueva Portada</p>';
    echo '  <input type="file" name="newCover" id="">';
    echo '  <br>';
    echo '  <br>';
    echo '  <input type="submit" value="Cambiar" name="coverChange">';
    echo '</form>';
    exit;
  }

  $metadata = file_get_contents('../../bookSave/' . $uuid . '/metadata.json');
  $metadata = json_decode($metadata, true);

  if (isset($_POST['coverChange'])) {
    if (!move_uploaded_file($_FILES['newCover']['tmp_name'], '../../bookSave/' . $uuid . '/OEBPS/' . $metadata['cover'])) {
      echo 'Error al subir el archivo';
      exit;
    }
    header("Location: ./?uuid=" . $uuid);
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../../main.css">
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@300&family=Roboto:wght@300&display=swap" rel="stylesheet">
  <title><?php echo $metadata['title'] ?></title>
</head>
<body>
  <div class="bookData">
    <div class="cover">
      <img src="../../bookSave/<?php echo $metadata['uuid'] . '/OEBPS/' . $metadata['cover']; ?>" alt="">
    </div>
    <div class="data">
      <h2 class="title"><?php echo $metadata['title']; ?></h2>
      <pre class="uuid"><?php echo $metadata['uuid']; ?></pre>
      <p class="readers">Lectores: <?php echo $metadata['readers']; ?></p>
      <p class="likes">Likes: <?php echo $metadata['likes']; ?></p>
      <p class="comments">Comentarios: <?php echo count($metadata['comments']); ?></p>
    </div>
    <div class="actions">
      <a href="?uuid=<?php echo $uuid; ?>&change-cover" class="change-cover">Cambiar Portada</a>
      <a href="?uuid=<?php echo $uuid; ?>&delete-book" class="delete">Eliminar libro</a>
    </div>
  </div>
</body>
</html>