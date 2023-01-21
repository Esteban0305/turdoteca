<link rel="stylesheet" href="style.css">
<?php
  echo '<pre>';
  echo '<p class="load">Loading</p>';
  if (!isset($_FILES['book']['name'])) {
    echo "No book";
    exit;
  }
  echo '<p class="correct">File founded ' . $_FILES['book']['name'] . '</p>';

  // $name = $_FILES['book']['full_path'];
  $name     = $_FILES['book']['name'];
  $type     = $_FILES['book']['type'];
  $tmp_name = $_FILES['book']['tmp_name'];

  if (!is_dir('epub/')) {
    mkdir('epub/');
    echo '<p class="load">Directory <b>epub/</b> created</p>';
  }
  
  if (!is_dir('data/')) {
    mkdir('data/');
    echo '<p class="load">Directory <b>data/</b> created</p>';
  }
  
  if ($type != 'application/epub+zip') {
    echo '<p class="error">Incorrect file type</p>';
    exit;
  }
  echo '<p class="correct">Correct file type</p>';
  
  // Save File
  if (!move_uploaded_file($tmp_name, 'epub/' . $name)) {
    echo '<p class="error">Failed to move the file</p>';
    exit;
  }
  
  // Extract File
  $epubFileZip = new ZipArchive();
  $epubFileZip->open('epub/' . $name);
  echo '<p class="load">Extracting file</p>';
  if (!$epubFileZip->extractTo('data/')) {
    echo '<p class="error">Failed to extract the file</p>';
    exit;
  }
  $epubFileZip->close();
  
  $files = scandir('data/');
  echo '<p class="load">Scanning directory</p>';
  
  $requiredDirs   = ['OEBPS'];
  $requiredFiles  = ['OEBPS/content.opf', 'OEBPS/toc.ncx'];
  
  // Verify All Data
  foreach ($requiredDirs as $dirR) {
    if (!is_dir('data/' . $dirR . '/')) {
      echo '<p class="error">Directory not found: ' . $dirR . '</p>';
      deleteDir('epub/', 0);
      deleteDir('data/', 0);
      exit;
    }
  }
  
  foreach ($requiredFiles as $fileR) {
    if (!file_exists('data/' . $fileR)) {
      echo '<p class="error">File not found: ' . $fileRR . '</p>';
      deleteDir('epub/', 0);
      deleteDir('data/', 0);
      exit;
    }
  }

  echo '<p class="correct">All files found</p>';
  
  // Verify UUID
  // Read Content
  $content = simplexml_load_file('data/OEBPS/content.opf');
  echo '<p class="load">Loading metadata</p>';

  // Read Metadata
  $metadata = $content->metadata->children('dc', true);

  $uuid = "";

  // Get UUID
  if (count($metadata->identifier) > 1) {
    foreach ($metadata->identifier as $identifier) {
      if (str_contains($identifier, 'uuid')) {
        $uuid = $identifier;
      }
    }
  } else {
    $uuid = $metadata->identifier[0];
  }

  $uuid = str_replace('urn:uuid:', '', $uuid);
  $uuid = strtolower($uuid);

  $uuid = str_replace('-', '', $uuid);

  if (!ctype_xdigit($uuid)) {
    echo '<p class="error">UUID Invalide</p>';
    echo '<p class="load">Deleting data</p>';
    deleteDir('epub/', 0);
    deleteDir('data/', 0);
    echo '<p class="error">Data Deleted</p>';
    exit;
  }

  echo '<p class="load">Moving epub file</p>';
  rename('epub/' . $name, '../../epubs/' . $name);
  echo '<p class="load">Deleting temporal files</p>';
  deleteDir('epub/', 0);
  deleteDir('data/', 0);

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
  echo '<p class="correct">Book ready to register</p>';
  echo "</pre>";
?>

<a href="../../?added">Ingresar Libro</a>