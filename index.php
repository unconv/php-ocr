<?php
require_once __DIR__ . "/autoload.php";

$ocr = new OCR();
$data = $ocr->read( "example-text/text-ubuntu.png" );

print_r( $data );
