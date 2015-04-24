<?php

namespace OCR;

require_once(BASE_PATH . 'lib/TesseractOCR.php');

function numbersForFileNamed($fileName) {
	$tesseract = new \TesseractOCR(BASE_PATH . 'inbox/' . $fileName);
	
	$tesseract->setWhitelist(range(0, 9));
	
	return preg_split('/[ \n]/',  $tesseract->recognize());
}

?>