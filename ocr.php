<?php
spl_autoload_register( function( $class ) {
    require __DIR__ . "/" . $class . ".php";
} );

$generator = new CharacterImageGenerator();

$f = imagecreatefrompng( "r_letter.png" );
$f = $generator->trim( $f );
$a = new LetterData( $f );

echo LetterData::which( $a ).PHP_EOL;
