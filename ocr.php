<?php
spl_autoload_register( function( $class ) {
    require __DIR__ . "/" . $class . ".php";
} );

$ocr = new OCR();
echo $ocr->read( "more_text.png" );
