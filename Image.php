<?php
class Image
{
    public static function save_to_file( GdImage $image, string $filename ) {
        $image = Image::get_blob( $image );
        return file_put_contents( $filename, $image );
    }

    public static function get_blob( GdImage $image ) {
        ob_start();
        imagepng( $image );
        imagedestroy( $image );
        return ob_get_clean();
    }

    public static function get_color( GdImage $image, int $x, int $y, $color = "red" ){
        $color_index = imagecolorat( $image, $x, $y );
        $colors = imagecolorsforindex( $image, $color_index );
        return $colors[$color];
    }
}