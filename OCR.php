<?php
class OCR
{
    public function read( string $filename ) {
        $read_this = imagecreatefrompng( $filename );

        $reader = new TextReader();
        $lines = $reader->lines_to_images( $read_this );

        $output = "";

        foreach( $lines as $line ) {
            $letters = $reader->line_to_letters( $line );
            foreach( $letters as $letter ) {
                $letter_image = $letter['image'];
                $space = $letter['space'];
                $letter_data = new LetterData( $letter_image );
                $which = OCR::which( $letter_data );
                $output .= $space.$which;
            }
            $output .= PHP_EOL;
        }

        return rtrim( $output );
    }

    public static function which( LetterData $letter ): string|null {
        $refernce_data = LetterData::generate_reference_material();

        $best_guess = null;
        $best_score = 0;

        foreach( $refernce_data as $letter_name => $reference_letter ) {
            $score = $reference_letter->compare( $letter );
            if( $score > $best_score ) {
                $best_guess = $letter_name;
                $best_score = $score;
            }
        }

        return $best_guess;
    }
}
