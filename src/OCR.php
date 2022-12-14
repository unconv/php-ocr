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
                $best_text = $data['autocorrected'];
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
        imagefilter( $read_this, IMG_FILTER_GRAYSCALE );
        imagefilter( $read_this, IMG_FILTER_CONTRAST, -100 );

        $reader = new TextReader();
        $lines = $reader->lines_to_images( $read_this );

        $output = [];

        $score = 0;
        $letter_count = 0;

        foreach( $lines as $line ) {
            $letters = $reader->line_to_letters( $line );
            $line_text = "";
            foreach( $letters as $letter ) {
                $letter_image = $letter['image'];
                $space = $letter['space'];
                $letter_data = new LetterData( $letter_image );
                $which = OCR::which( $letter_data, $font_filename );
                $line_text .= $space.$which['letter'];

                $score += $which['score'];
                $letter_count++;
            }
            $output[] = $line_text;
        }

        $autocorrected = array_map( "OCR::autocorrect", $output );

        return [
            "output" => $output,
            "autocorrected" => $autocorrected,
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

        if( ! $letter->has_top_serif() ) {
            if( in_array( $best_guess, ["f"] ) ) {
                $best_guess = "t";
                $best_score *= 1.1;
            }
        }

        if( ! $letter->has_left_vertical_line() ) {
            if( in_array( $best_guess, ["R"] ) ) {
                $best_guess = "a";
                $best_score *= 1.1;
            }
        }

        if( $letter->has_black_line_through() ) {
            if( in_array( $best_guess, ["o", "O", "0", "c"] ) ) {
                $best_guess = "e";
                $best_score *= 1.1;
            }
        }

        if( $letter->has_white_line_through() ) {
            if( in_array( $best_guess, ["l", "I"] ) ) {
                $best_guess = "i";
                $best_score *= 1.1;
            }
        } else {
            if( in_array( $best_guess, ["i"] ) ) {
                $best_guess = "l";
                $best_score *= 1.1;
            }
        }

        if( $letter->black_percentage() > 59 ) {
            if( in_array( $best_guess, ["f", "1", "t"] ) ) {
                $best_guess = ",";
                $best_score *= 1.1;
            }
        }

        return [
            "letter" => $best_guess,
            "score" => $best_score,
        ];
    }

    public static function autocorrect( string $text ) {
        $words = explode( " ", $text );

        $output = [];

        foreach( $words as $word ) {
            $letters = str_split( $word );

            if( $word === "l" ) {
                $word = "I";
            }

            $lowercase_letter_count = 0;
            $uppercase_letter_count = 0;
            foreach( $letters as $letter ) {
                if( mb_strtolower( $letter ) === $letter && preg_replace( '/[0-9]/', '', $letter ) === $letter ) {
                    $lowercase_letter_count++;
                }
                if( mb_strtoupper( $letter ) === $letter && preg_replace( '/[0-9]/', '', $letter ) === $letter ) {
                    $uppercase_letter_count++;
                }
            }

            $is_ucfirst = ucfirst( $word ) === $word;

            $word_length = mb_strlen( $word );

            if( $lowercase_letter_count / $word_length >= 0.5 ) {
                $word = str_replace( [
                    "0",
                    "1",
                    "5",
                ], [
                    "o",
                    "l",
                    "s",
                ], $word );

                $is_ucfirst = ucfirst( $word ) === $word;

                $word = mb_strtolower( $word );
            }

            if( $uppercase_letter_count / $word_length >= 0.6 ) {
                $word = str_replace( [
                    "0",
                    "1",
                    "5",
                ], [
                    "O",
                    "I",
                    "S",
                ], $word );

                $word = mb_strtoupper( $word );
            }

            if( $is_ucfirst ) {
                $word = ucfirst( $word );
            }

            $output[] = $word;
        }

        return implode( " ", $output );
    }
}
