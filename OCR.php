<?php
class OCR
{
    public function read( string $filename ) {
        $fonts = glob( "fonts/*.ttf" );

        $best_font = null;
        $best_score = 0;

        foreach( $fonts as $font_filename ) {
            $data = $this->read_by_font( $filename, $font_filename );

            if( $data['score'] > $best_score ) {
                $best_score = $data['score'];
                $best_font = basename( $font_filename );
                $best_text = $data['output'];
            }
        }

        return [
            "score" => $best_score,
            "font" => $best_font,
            "text" => $best_text,
        ];
    }

    public function read_by_font( string $filename, string $font_filename ): array {
        $read_this = imagecreatefrompng( $filename );

        $reader = new TextReader();
        $lines = $reader->lines_to_images( $read_this );

        $output = "";

        $score = 0;
        $letter_count = 0;

        foreach( $lines as $line ) {
            $letters = $reader->line_to_letters( $line );
            foreach( $letters as $letter ) {
                $letter_image = $letter['image'];
                $space = $letter['space'];
                $letter_data = new LetterData( $letter_image );
                $which = OCR::which( $letter_data, $font_filename );
                $output .= $space.$which['letter'];

                $score += $which['score'];
                $letter_count++;
            }
            $output .= PHP_EOL;
        }

        $output = rtrim( $output );

        return [
            "output" => $output,
            "score" => $score / $letter_count,
        ];
    }

    public static function which( LetterData $letter, string $font_filename ): array {
        $refernce_data = LetterData::generate_reference_material( $font_filename );

        $best_guess = null;
        $best_score = 0;

        foreach( $refernce_data as $letter_name => $reference_letter ) {
            $score = $reference_letter->compare( $letter );
            if( $score > $best_score ) {
                $best_guess = $letter_name;
                $best_score = $score;
            }
        }

        return [
            "letter" => $best_guess,
            "score" => $best_score,
        ];
    }
}
