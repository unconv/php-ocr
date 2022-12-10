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
                $which = LetterData::which( $letter_data );
                $output .= $space.$which;
            }
            $output .= PHP_EOL;
        }

        return rtrim( $output );
    }
}
