<?php

require 'classes/File.class.php';

$uploaddir = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;

$extension = pathinfo($_FILES["userfile"]["name"], PATHINFO_EXTENSION);
$mimeType = $_FILES["userfile"]["type"];	

if($extension == 'txt' && $mimeType == 'text/plain') {

	$_FILES['userfile']['name'] = 'file';
	$uploadfile = $uploaddir . $_FILES['userfile']['name'] . '.' . $extension;

	if(file_exists($uploaddir.''."file.txt")) unlink($uploaddir.''."file.txt");

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		$uploadedFile = new File($uploaddir.''.$_FILES['userfile']['name']. '.' . $extension);
		$uploadedFile->extractPolygonCoordinates();
		$polygon = $uploadedFile->getPolygonCoordinates();
		echo json_encode($polygon);
	}

}

?>