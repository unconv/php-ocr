<?php
class CharacterImageGenerator
{
    public function generate( string $letter, int $size = 30 ): GdImage {
        // initialize image
        $im = imagecreatetruecolor( $size, $size );

        // initialize colors
        $white = imagecolorallocate( $im, 255, 255, 255 );
        $black = imagecolorallocate( $im, 0, 0, 0 );
        imagefilledrectangle( $im, 0, 0, $size, $size, $white );

        // Replace path by your own font path
        $font = './arial.ttf';

        // add letter to image
        imagettftext( $im, $size-1, 0, 0, $size-1, $black, $font, $letter );

        return $this->trim( $im );
    }

    public function trim( GdImage $im ): GdImage {
        // get blob of image
        $image = Image::get_blob( $im );

        // trim white borders from image
        $im = new Imagick();
        $im->readImageBlob( $image );
        $im->trimImage( 0 );

        // write image to file
        $filename = tempnam( "/tmp", "image" );
        $im->writeImage( $filename );

        // read image from file
        $output = imagecreatefrompng( $filename );

        unlink( $filename );

        return $output;
    }
}
