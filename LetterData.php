<?php
class LetterData
{
    private int $accuracy = 30;
    private array $data;

    public function __construct( GdImage $image )
    {
        $small_image = $this->resize_image(
            image: $image,
            width: $this->accuracy,
            height: $this->accuracy
        );

        $this->data = [];

        for( $y = 0; $y < $this->accuracy; $y++ ) {
            for( $x = 0; $x < $this->accuracy; $x++ ) {
                $colors = imagecolorat( $small_image, $x, $y );
                $color = ($colors >> 16) & 0xFF;
                $this->data[] = $color;
            }
        }
    }

    private function resize_image( GdImage $image, int $width, int $height ): GdImage {
        // initialize small image
        $resized_image = imagecreatetruecolor( $width, $height );

        // get original image width and height
        $orig_width = imagesx( $image );
        $orig_height = imagesy( $image );

        // resize image
        imagecopyresized(
            dst_image: $resized_image,
            src_image: $image,
            dst_x: 0,
            dst_y: 0,
            src_x: 0,
            src_y: 0,
            dst_width: $width,
            dst_height: $height,
            src_width: $orig_width,
            src_height: $orig_height,
        );

        return $resized_image;
    }

    public function compare( LetterData $letter ) {
        $this_data = $this->data;
        $that_data = $letter->data;

        $total_error = 0;

        foreach( $this_data as $index => $color ) {
            $error = abs( $color - $that_data[$index] );
            $total_error += $error;
        }

        return $total_error;
    }

    public static function which( LetterData $letter ): string|null {
        $refernce_data = static::generate_reference_material();

        $best_guess = null;
        $best_error = 999999;

        foreach( $refernce_data as $letter_name => $reference_letter ) {
            $error = $reference_letter->compare( $letter );
            if( $error < $best_error ) {
                $best_guess = $letter_name;
                $best_error = $error;
            }
        }

        return $best_guess;
    }

    /**
     * Generate reference material
     *
     * @return LetterData[]
     */
    static function generate_reference_material(): array {
        $all_letters = [];

        $generator = new CharacterImageGenerator();

        for( $i = 65; $i < 65 + 26; $i++ ) {
            $letter = chr( $i );
            $gdimage = $generator->generate( $letter );
            ob_start();
            imagepng( $gdimage );
            $image_source = ob_get_clean();
            file_put_contents( "letters/".$letter.".png", $image_source );
            $letter_data = new LetterData( $gdimage );
            $all_letters[$letter] = $letter_data;
        }

        return $all_letters;
    }

    public function get_data(): array {
        return $this->data;
    }
}