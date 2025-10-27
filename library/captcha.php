<?php 
session_start(); 

// 1. GENERATE THE RANDOM TEXT
$text = rand(10000, 99999); 
$_SESSION["vercode"] = $text; 

// 2. SET UP IMAGE DIMENSIONS AND COLORS
$height = 30; // Slightly taller
$width = 90;  // Wider to accommodate distortion
$image_p = imagecreate($width, $height); 

// Allocate background and text colors
$background_color = imagecolorallocate($image_p, 240, 240, 240); // Light Gray Background
$black = imagecolorallocate($image_p, 0, 0, 0); 
$text_color = imagecolorallocate($image_p, 60, 60, 60); // Dark Gray text

// Fill the background
imagefill($image_p, 0, 0, $background_color); 

// 3. ADD DISTORTION (Noise and Lines)
// Add random dots (noise)
for ($i = 0; $i < 300; $i++) {
    $dot_color = imagecolorallocate($image_p, rand(100, 200), rand(100, 200), rand(100, 200));
    imagesetpixel($image_p, rand(0, $width), rand(0, $height), $dot_color);
}

// Add random lines (distortion)
for ($i = 0; $i < 3; $i++) {
    $line_color = imagecolorallocate($image_p, rand(150, 250), rand(150, 250), rand(150, 250)); // Light, distracting lines
    imageline($image_p, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// 4. DRAW THE TEXT WITH SLIGHT VARIATION
$font_size = 5; // GD font size (1-5)

// Write the text using a slightly darker, contrasting color
imagestring($image_p, $font_size, 15, 7, $text, $text_color); 

// 5. SEND IMAGE TO BROWSER AND CLEAN UP
header('Content-Type: image/jpeg');
imagejpeg($image_p, null, 90); // Use quality 90
imagedestroy($image_p); 
?>