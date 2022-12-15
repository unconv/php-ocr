<?php
class Image
{
    public const TRIM_COLOR_THRESHOLD = 180;

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

    public static function trim( GdImage $image ): GdImage {
        $width = imagesx( $image );
        $height = imagesy( $image );

        $start_x = null;
        $start_y = null;
        $end_x = null;
        $end_y = null;

        for( $y = 0; $y < $height; $y++ ) {
            for( $x = 0; $x < $width; $x++ ) {
                $color = Image::get_color( $image, $x, $y );
                if( $color <= Image::TRIM_COLOR_THRESHOLD ) {
                    if( $start_y === null ) {
                        $start_y = $y;
                    }

                    $end_y = $y+1;
                }
            }
        }

        for( $x = 0; $x < $width; $x++ ) {
            for( $y = 0; $y < $height; $y++ ) {
                $color = Image::get_color( $image, $x, $y );
                if( $color <= Image::TRIM_COLOR_THRESHOLD ) {
                    if( $start_x === null ) {
                        $start_x = $x;
                    }

                    $end_x = $x+1;
                }
            }
        }

        return imagecrop( $image, [
            "x" => $start_x,
            "y" => $start_y,
            "width" => $end_x - $start_x,
            "height" => $end_y - $start_y
        ] );

    }
}