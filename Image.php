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
}