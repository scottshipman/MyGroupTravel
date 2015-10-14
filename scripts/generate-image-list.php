<?php
/**
 * Created by PhpStorm.
 * User: scottshipman
 * Date: 10/13/15
 * Time: 9:39 AM
 */

echo "Starting...\n";
$file = 'institutions.csv';
if(!file_exists($file) || !is_readable($file)) {
  echo "ERROR: Cant find file \n";
  echo getcwd() . $file . "\n";
  return FALSE;
}

if (($handle = fopen($file, "r")) !== FALSE) {
  $head = fgetcsv($handle);
  $imageFile = fopen("image-urls.txt", 'w');

  while(($row = fgetcsv($handle)) !== FALSE) {

    $row = array_combine($head, $row);

    $url = 'https://edwindoran.mytourquote.com/tetimages/lemur/images/';
    if($row['fileName'] !== 'NULL')  {
      $url .= $row['fileID']. '.' . strtolower($row['fileType']);
      //echo $url . "\n";
      fwrite($imageFile, $url . "\n");
    }
  }
  fclose($imageFile);
}

echo "Done.\n";