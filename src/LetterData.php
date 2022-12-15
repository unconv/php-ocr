<?php
class LetterData
{
    public const ACCURACY = 20;
    public const COLOR_ACCURACY = 80;
    private array $data;
    private static array $refernce_data;
    private GdImage $image;

    public function __construct( GdImage $image, bool $filter = true )
    {
        if( $filter ) {
            imagefilter($image, IMG_FILTER_GRAYSCALE);
            imagefilter($image, IMG_FILTER_CONTRAST, -100);

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

        $characters = str_split( "QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0987654321?.," );

        foreach( $characters as $letter ) {
            $gdimage = $generator->generate( $letter, $font_filename );
            ob_start();
            imagepng( $gdimage );
            $image_source = ob_get_clean();
            file_put_contents( "letters/".$letter.".png", $image_source );
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
}