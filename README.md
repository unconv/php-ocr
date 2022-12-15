# PHP OCR / Text Recognition

This is a crude text recognition library built for my YouTube coding challenge. You can use it or improve it if you want, or comment on it to let me know what I'm doing wrong.

It's not perfect by any means and you shouldn't use it in production or expect it to work in your specific use case.

You can watch me coding this on my YouTube channel:
- Video 1: https://www.youtube.com/watch?v=TPBwv85Bxr4
- Video 2: https://www.youtube.com/watch?v=NHVJpx7OuWk

## How to use
To read text automatically from an image you can use the following code. It will read all fonts in the ```fonts``` folder and try each one to see which one will generate the best match for given image.
```php
require_once __DIR__ . "/autoload.php";

// initialize
$ocr = new OCR();

// read text automatically
$data = $ocr->read( "example-text/text-ubuntu.png" );

// get text lines as an array
echo $data['text'];
```

If you know what font the text is made in, you can use the following code, which will also be much faster.
```php
require_once __DIR__ . "/autoload.php";

// initialize
$ocr = new OCR();

// read text from image with given font
$data = $ocr->read_by_font(
    filename: "example-text/text-ubuntu.png",
    font_filename: "fonts/Ubuntu-Regular.ttf",
);

// get raw text lines as an array
echo $data['output'];

// get "autocorrected" text lines as an array
echo $data['autocorrected'];
```
