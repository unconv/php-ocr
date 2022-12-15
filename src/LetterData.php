<?php
class LetterData
{
    public const ACCURACY = 40;
    public const COLOR_ACCURACY = 80;
    private array $data;
    private static array $refernce_data;
    private GdImage $image;

    public function __construct( GdImage $image, bool $filter = true )
    {
        if( $filter ) {
            $image = Image::trim( $image );

            $image = Image::resize(
                image: $image,
                width: LetterData::ACCURACY,
            );
        }

        $this->image = $image;

        $this->data = [];

        $width = imagesx( $image );
        $height = imagesy( $image );

        for( $y = 0; $y < $height; $y++ ) {
            for( $x = 0; $x < $width; $x++ ) {
                $colors = imagecolorat( $image, $x, $y );
                $color = ($colors >> 16) & 0xFF;
                $this->data[] = $color;
            }
        }
    }

    public function compare( LetterData $letter ) {
        $this_data = $this->data;
        $this_image = $this->get_image();

        $this_height = imagesy( $this_image );
        $that_height = imagesy( $letter->get_image() );
        $height_diff = abs( $this_height - $that_height );
        $diff_percent = round( $height_diff / $this_height, 1 );

        if( $diff_percent <= 0.2 ) {
            $this_image = Image::resize(
                image: $this->get_image(),
                width: imagesx( $letter->get_image() ),
                height: $that_height
            );

            $this_letterdata = new LetterData( $this_image, false );
            $this_data = $this_letterdata->get_data();
        }

        $that_data = $letter->data;

        $total_pixels = count( $this_data ) + count( $that_data );

        // compare reference letter to read letter
        $different_pixels = $this->count_pixel_diff( $this_data, $that_data );

        // compare read letter to reference letter
        $different_pixels += $this->count_pixel_diff( $that_data, $this_data );

        $score = 100 - $different_pixels / $total_pixels * 100;

        return $score;
    }

    private function count_pixel_diff( array $data1, array $data2 ) {
        $different_pixels = 0;

        foreach( $data1 as $index => $color ) {
            if( ! isset( $data2[$index] ) ) {
                $different_pixels++;
                continue;
            }

            $data1_is_black = $color <= LetterData::COLOR_ACCURACY;
            $data2_is_black = $data2[$index] <= LetterData::COLOR_ACCURACY;

            if( $data1_is_black != $data2_is_black ) {
                $different_pixels++;
            }
        }

        return $different_pixels;
    }

    /**
     * Generate reference material
     *
     * @return LetterData[]
     */
    static function generate_reference_material( string $font_filename ): array {
        if( isset( static::$refernce_data[$font_filename] ) ) {
            return static::$refernce_data[$font_filename];
        }

        $all_letters = [];

        $generator = new CharacterImageGenerator();

        $characters = str_split( "QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0987654321?.,:/-" );

        foreach( $characters as $letter ) {
            $gdimage = $generator->generate( $letter, $font_filename );
            $letter_data = new LetterData( $gdimage );
            $all_letters[$letter] = $letter_data;
        }

        static::$refernce_data[$font_filename] = $all_letters;

        return $all_letters;
    }

    public function get_data(): array {
        return $this->data;
    }

    public function get_image(): GdImage {
        return $this->image;
    }

    public function has_black_line_through() {
        $image = $this->get_image();

        $width = imagesx( $image );
        $height = imagesy( $image );

        for( $y = 0; $y < $height; $y++ ) {
            $black_pixels = 0;
            for( $x = 0; $x < $width; $x++ ) {
                $color = Image::get_color( $image, $x, $y );
                $is_black = $color <= LetterData::COLOR_ACCURACY;
                if( $is_black ) {
                    $black_pixels++;
                }
            }
            if( $black_pixels / $width > 0.9 ) {
                return true;
            }
        }

        return false;
    }

    public function has_white_line_through() {
        $image = $this->get_image();

        $width = imagesx( $image );
        $height = imagesy( $image );

        for( $y = 0; $y < $height; $y++ ) {
            $white_pixels = 0;
            for( $x = 0; $x < $width; $x++ ) {
                $color = Image::get_color( $image, $x, $y );
                $is_white = $color > 140;
                if( $is_white ) {
                    $white_pixels++;
                }
            }
            if( $white_pixels === $width ) {
                return true;
            }
        }

        return false;
    }

    public function black_percentage() {
        $image = $this->get_image();

        $width = imagesx( $image );
        $height = imagesy( $image );

        $black_pixels = 0;

        for( $y = 0; $y < $height; $y++ ) {
            for( $x = 0; $x < $width; $x++ ) {
                $color = Image::get_color( $image, $x, $y );
                $is_black = $color <= LetterData::COLOR_ACCURACY;
                if( $is_black ) {
                    $black_pixels++;
                }
            }
        }

        return $black_pixels / ( $width * $height ) * 100;
    }

    public function has_top_serif() {
        $image = $this->get_image();

        $width = imagesx( $image );
        $height = imagesy( $image );

        if( $height < 5 ) {
            return false;
        }

        for( $y = 0; $y < 5; $y++ ) {
            $black_pixels = 0;
            for( $x = 0; $x < $width; $x++ ) {
                $color = Image::get_color( $image, $x, $y );
                $is_black = $color <= LetterData::COLOR_ACCURACY;
                if( $is_black ) {
                    $black_pixels++;
                }
            }
            if( $black_pixels / $width > 0.4 ) {
                return true;
            }
        }

        return false;
    }

    public function has_left_vertical_line() {
        $image = $this->get_image();

        $height = imagesy( $image );

        for( $y = 0; $y < $height; $y++ ) {
            $black_pixels = 0;
            $color = Image::get_color( $image, 0, $y );
            $is_black = $color <= LetterData::COLOR_ACCURACY;
            if( $is_black ) {
                $black_pixels++;
            }
        }

        if( $black_pixels / $height > 0.9 ) {
            return true;
        }

        return false;
    }
}