<?php
class LetterData
{
    private int $accuracy = 30;
    private array $data;
    private static array $refernce_data;

    public function __construct( GdImage $image )
    {
        $small_image = Image::resize(
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
        if( isset( static::$refernce_data ) ) {
            return static::$refernce_data;
        }

        $all_letters = [];

        $generator = new CharacterImageGenerator();

        $characters = str_split( "QWERTYUIOPASDFGHJKLZXCVBNM0987654321" );

        foreach( $characters as $letter ) {
            $gdimage = $generator->generate( $letter );
            ob_start();
            imagepng( $gdimage );
            $image_source = ob_get_clean();
            file_put_contents( "letters/".$letter.".png", $image_source );
            $letter_data = new LetterData( $gdimage );
            $all_letters[$letter] = $letter_data;
        }

        static::$refernce_data = $all_letters;

        return $all_letters;
    }

    public function get_data(): array {
        return $this->data;
    }
}