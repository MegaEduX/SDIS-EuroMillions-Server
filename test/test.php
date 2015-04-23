<?php

require_once('TesseractOCR.php');

$tesseract = new TesseractOCR('IMG_0562.JPG');

$tesseract->setWhitelist(range(0, 9));

echo $tesseract->recognize();

?>