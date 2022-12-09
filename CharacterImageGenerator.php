<?php
class CharacterImageGenerator
{
    public function generate( string $letter ): GdImage {
        // initialize image
        $im = imagecreatetruecolor( 30, 30 );

        // initialize colors
        $white = imagecolorallocate( $im, 255, 255, 255 );
        $black = imagecolorallocate( $im, 0, 0, 0 );
        imagefilledrectangle( $im, 0, 0, 30, 30, $white );

        // Replace path by your own font path
        $font = './arial.ttf';

        // add letter to image
        imagettftext( $im, 29, 0, 0, 29, $black, $font, $letter );

        return $im;
    }
}
