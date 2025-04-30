<?php
  $books = scandir('./epubs/');

  $notABook = ['.', '..', 'index.php', 'style.css', 'books.json', 'extracted', 'bookSave'];

  $addedBooks = [];

  if (!is_dir('bookSave/')) {
    mkdir('bookSave/');
  }

  if (!is_dir('../db/books/')) {
    mkdir('../db/books/');
  }

  foreach ($books as $epubName) {
    if (!in_array($epubName, $notABook)) {
      // Path to Raw Extracted
      $pathToExtractedRaw = './extracted/' . $epubName . '/';

      // Extract EPub
      $epubExtr = new ZipArchive();
      $epubExtr->open('./epubs/' . $epubName);
      $epubExtr->extractTo($pathToExtractedRaw);
      $epubExtr->close();
      
      // Read Content
      $content = simplexml_load_file($pathToExtractedRaw . 'OEBPS/content.opf');
      
      // Read Metadata
      $metadata = $content->metadata->children('dc', true);

      // Get Cover
      $cover = '';
      $coverHref = '';

      foreach ($content->metadata->meta as $maybeCover) {
        if ($maybeCover['name'][0] == 'cover') {
          $cover = '' . $maybeCover['content'][0];
        }
      }

      foreach ($content->manifest->item as $item) {
        if ($item["id"] == $cover) {
          $coverHref = $item['href'] . '';
        }
      }

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

      // Get Raw Index
      $rawIndex = simplexml_load_file($pathToExtractedRaw . 'OEBPS/toc.ncx');

      // No need an complex index
      // $indexFormatted = recursiveIndex($rawIndex->navMap);
      $linearIndex = recursiveLinearIndex($rawIndex->navMap, []);

      $subjects = $metadata->subject;

      if (count($subjects) > 1) {
        $tempSubjects = [];
        foreach ($subjects as $sub) {
          array_push($tempSubjects, ucwords(trim($sub)));
        }
        $subjects = $tempSubjects;
      } else {
        if (str_contains($subjects . '', ',')) {
          $subjects = explode(',', $subjects . '');
        }

        $tempSubjects = [];
        foreach ($subjects as $sub) {
          array_push($tempSubjects, ucwords(trim($sub)));
        }
        $subjects = $tempSubjects;
      }

      $subjectsJSON = file_get_contents('subjects.json');
      $subjectsJSON = json_decode($subjectsJSON, true);

      foreach ($subjects as $sub) {
        if (!in_array($sub, $subjectsJSON)) {
          array_push($subjectsJSON, $sub);
        }
      }

      file_put_contents('subjects.json', json_encode($subjectsJSON, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));

      // Metadata to JSON
      $metadataJSON = [
        "title"       => $metadata->title . '',
        "uuid"        => $uuid . '',
        "subject"     => $subjects ?? [],
        "readers"     => 0,
        "added"       => date('U'),
        "creator"     => $metadata->creator . '',
        "description" => $metadata->description . '',
        // "chapters"    => $indexFormatted,
        "flow"        => $linearIndex,
        "likes"       => 0,
        "comments"    => [],
        "cover"       => $coverHref
      ];

      // echo '<img src="bookSave/' . $uuid . '/OEBPS/' . $coverHref . '">';

      // Save JSON Metadata
      $booksExtracted = file_get_contents('./books.json');
      $booksExtracted = json_decode($booksExtracted, true);

      if (!in_array($uuid, $booksExtracted)) {
        mkdir('bookSave/' . $uuid);
        moveBook($epubName, $uuid);
        rename('epubs/' . $epubName, 'epubs/' . $uuid . '.epub');
        unlink('bookSave/' . $uuid . '/OEBPS/content.opf');
        unlink('bookSave/' . $uuid . '/OEBPS/toc.ncx');
        array_push($booksExtracted, $uuid);
        
        file_put_contents('./books.json', json_encode($booksExtracted, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
        file_put_contents('./bookSave/' . $uuid . '/' . 'metadata.json', json_encode($metadataJSON, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
        $metadataJSON['chapters'] = [];
        $metadataJSON['flow'] = [];
        $metadataJSON['comments'] = 0;
        file_put_contents('../db/books/' . $uuid . '.json', json_encode($metadataJSON, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));

        array_push($addedBooks, $metadata->title);
      }
      deleteDir('./extracted/' . $epubName . '/', 0);
      // Delete epub file ??
    }
  }

  function moveBook($rawPath, $uuid) {
    $files = scandir('extracted/' . $rawPath);

    foreach ($files as $file) {
      if(!in_array($file, ['.', '..', 'mimetype', 'META-INF'])) {
        rename('extracted/' . $rawPath . '/' . $file, 'bookSave/' . $uuid . '/' . $file);
      }
    }
  }

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

  function recursiveIndex($index) {
    $index2return = [];
    foreach ($index->navPoint as $item) {
      $idChapter    = $item['id'] . '';
      $chapterTitle = $item->navLabel->text . '';
      $chapterSrc   = $item->content['src'] . '';

      $index2return[$idChapter] = [
        'id'    => $idChapter,
        'title' => $chapterTitle,
        'src'   => $chapterSrc
      ];

      if (isset($item->navPoint)) {
        $index2return[$idChapter]['subs'] = recursiveIndex($item);
      }
    }
    return $index2return;
  }

  function recursiveLinearIndex($index, $linear = []) {
    $index2returnLinear = $linear;
    foreach ($index->navPoint as $item) {
      $idChapter    = $item['id'] . '';
      $chapterTitle = $item->navLabel->text . '';
      $chapterSrc   = $item->content['src'] . '';

      $index2returnLinear[$idChapter] = [
        'id'    => $idChapter,
        'title' => $chapterTitle,
        'src'   => $chapterSrc
      ];

      if (isset($item->navPoint)) {
        $index2returnLinear = recursiveLinearIndex($item, $index2returnLinear);
      }
    }
    return $index2returnLinear;
  }

  if (isset($_GET['added'])) {
    foreach ($addedBooks as $add) {
      echo '<p>' . $add . '</p>';
    }
    echo '<a href="dashboard/?goldenapple">Regresar</a>';
  }
?>