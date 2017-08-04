<?php

class File {

	private $fileDirectory;
	private $coordinatesArray = array();

	function __construct($fileDirectory) {
		$this->fileDirectory = $fileDirectory;
	}
	
	public function extractPolygonCoordinates() {
		$file = fopen($this->fileDirectory, "r");
		if ($file) {
			while (!feof($file)) {
				$currentFileString = fgets($file);
				$this->coordinatesArray[] = trim($currentFileString);
			}
		}
		fclose($file);
	}

	public function getPolygonCoordinates() {
		return $this->coordinatesArray;
	}
}