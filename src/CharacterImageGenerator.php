<?php
class CharacterImageGenerator
{
    public function generate( string $letter, string $font_filename, int $size = LetterData::ACCURACY ): GdImage {
        // initialize image
        $im = imagecreatetruecolor( $size*3, $size*3 );

        // initialize colors
        $white = imagecolorallocate( $im, 255, 255, 255 );
        $black = imagecolorallocate( $im, 0, 0, 0 );
        imagefilledrectangle( $im, 0, 0, $size*3, $size*3, $white );

        // add letter to image
        imagettftext( $im, $size-1, 0, 0, $size-1, $black, $font_filename, $letter );

        imagefilter( $im, IMG_FILTER_GRAYSCALE );
        imagefilter( $im, IMG_FILTER_CONTRAST, -100 );

        $im = Image::trim( $im );

        return $im;
    }
}
