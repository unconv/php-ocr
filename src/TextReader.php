<?php
class TextReader
{
    private int $color_accuracy = 80;

    public function get_lines( GdImage $image ): array {
        $width = imagesx( $image );
        $height = imagesy( $image );

        $line_start = null;
        $line_end = null;

        $lines = [];

        for( $y = 0; $y < $height; $y++ ) {
            $has_text = false;
            for( $x = 0; $x < $width; $x++ ) {
                $color = Image::get_color( $image, $x, $y );
                if( $color <= $this->color_accuracy ) {
                    $has_text = true;
                    break;
                }
            }

            if( $has_text ) {
                if( $line_start === null ) {
                    $line_start = $y;
                }
            } else {
                if( $line_start !== null ) {
                    $line_end = $y;
                    $lines[] = [
                        "start" => $line_start,
                        "end" => $line_end,
                    ];
                    $line_start = null;
                }
            }
        }

        return $lines;
    }

    public function get_letters( GdImage $image ): array {
        $width = imagesx( $image );
        $height = imagesy( $image );

        $letter_start = null;
        $letter_end = null;

        $letters = [];

        for( $x = 0; $x < $width; $x++ ) {
            $has_text = false;
            for( $y = 0; $y < $height; $y++ ) {
                $color = Image::get_color( $image, $x, $y );
                if( $color <= $this->color_accuracy ) {
                    $has_text = true;
                    break;
                }
            }

            if( $has_text ) {
                if( $letter_start === null ) {
                    $letter_start = $x;
                }
            } else {
                if( $letter_start !== null ) {
                    $letter_end = $x;
                    $letters[] = [
                        "start" => $letter_start,
                        "end" => $letter_end,
                    ];
                    $letter_start = null;
                }
            }
        }

        // read last letter
        if( $letter_start !== null ) {
            $letter_end = $x;
            $letters[] = [
                "start" => $letter_start,
                "end" => $letter_end,
            ];
            $letter_start = null;
        }

        return $letters;
    }

    /**
     * Turns image of text into images of lines
     *
     * @param GdImage $image
     * @return GdImage[]
     */
    public function lines_to_images( GdImage $image ): array {
        $lines = $this->get_lines( $image );

        $width = imagesx( $image );

        $line_images = [];

        foreach( $lines as $line_data ) {
            $height = $line_data['end'] - $line_data['start'];

            $line_image = imagecrop( $image, [
                "x" => 0,
                "y" => $line_data['start'],
                "width" => $width,
                "height" => $height
            ] );

            $line_images[] = $line_image;
        }

        return $line_images;
    }

    /**
     * Turns image of a line of text into images of letters
     *
     * @param GdImage $image
     * @return array
     */
    public function line_to_letters( GdImage $image ): array {
        $letters = $this->get_letters( $image );

        $height = imagesy( $image );

        $letter_images = [];

        foreach( $letters as $i => $letter_data ) {
            $width = $letter_data['end'] - $letter_data['start'];

            $previous = $letters[$i-1] ?? null;
            if( $previous ) {
                $space = $letter_data['start'] - $previous['end'];
            } else {
                $space = 0;
            }

            if( $space > $height/4 ) {
                $space = " ";
            } else {
                $space = "";
            }

            $too_wide = $height*1.3;

            // if letter is too wide
            if( $width > $too_wide ) {
                // cut letter in half
                $width = intval( $width / 2 );

                // cut first part of letter
                $letter_image = imagecrop( $image, [
                    "x" => $letter_data['start'],
                    "y" => 0,
                    "width" => $width,
                    "height" => $height
                ] );

                // add letter to output array
                $letter_images[] = [
                    "image" => $letter_image,
                    "space" => $space,
                ];

                // reset space
                $space = "";

                // move start position
                $letter_data['start'] += $width;
            }

            $letter_image = imagecrop( $image, [
                "x" => $letter_data['start'],
                "y" => 0,
                "width" => $width,
                "height" => $height
            ] );

            $letter_images[] = [
                "image" => $letter_image,
                "space" => $space,
            ];
        }

        return $letter_images;
    }
}
