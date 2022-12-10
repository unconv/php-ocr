<?php
spl_autoload_register( function( $class ) {
    require __DIR__ . "/" . $class . ".php";
} );

$generator = new CharacterImageGenerator();

$read_this = imagecreatefrompng( "read_this.png" );
//$read_this = $generator->trim( $read_this );

$reader = new TextReader();
$lines = $reader->lines_to_images( $read_this );

$num = 0;

foreach( $lines as $line ) {
    $letters = $reader->line_to_letters( $line );
    foreach( $letters as $letter ) {
        Image::save_to_file( $letter, "letter-images/".$num.".png" );
        $num++;
    }
}
