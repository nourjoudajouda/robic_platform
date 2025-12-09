<?php
header("Content-Type:text/css");
function checkHexColor($color)
{
    return preg_match('/^#[a-f0-9]{6}$/i', $color);
}
$color = null;
$secondColor = null;

if (isset($_GET['color']) and $_GET['color'] != '') {
    $colorValue = str_replace('#', '', $_GET['color']);
    $color = "#" . $colorValue;
}
if (!$color or !checkHexColor($color)) {
    $color = "#81C104";
}
if (isset($_GET['secondColor']) and $_GET['secondColor'] != '') {
    $secondColorValue = str_replace('#', '', $_GET['secondColor']);
    $secondColor = "#" . $secondColorValue;
}
if (!$secondColor or !checkHexColor($secondColor)) {
    $secondColor = "#AFFA19";
}

function hexToHsl($hex)
{
    $hex   = str_replace('#', '', $hex);
    $red   = hexdec(substr($hex, 0, 2)) / 255;
    $green = hexdec(substr($hex, 2, 2)) / 255;
    $blue  = hexdec(substr($hex, 4, 2)) / 255;
    $cmin  = min($red, $green, $blue);
    $cmax  = max($red, $green, $blue);
    $delta = $cmax - $cmin;
    if ($delta == 0) {
        $hue = 0;
    } elseif ($cmax === $red) {
        $hue = (($green - $blue) / $delta);
    } elseif ($cmax === $green) {
        $hue = ($blue - $red) / $delta + 2;
    } else {
        $hue = ($red - $green) / $delta + 4;
    }
    $hue = round($hue * 60);
    if ($hue < 0) {
        $hue += 360;
    }
    $lightness  = (($cmax + $cmin) / 2);
    $saturation = $delta === 0 ? 0 : ($delta / (1 - abs(2 * $lightness - 1)));
    if ($saturation < 0) {
        $saturation += 1;
    }
    $lightness  = round($lightness * 100);
    $saturation = round($saturation * 100);
    $hsl['h']   = $hue;
    $hsl['s']   = $saturation;
    $hsl['l']   = $lightness;
    return $hsl;
}
?>

:root{
--base-h: <?php echo hexToHsl($color)['h']; ?>;
--base-s: <?php echo hexToHsl($color)['s']; ?>%;
--base-l: <?php echo hexToHsl($color)['l']; ?>%;
--base-two-h: <?php echo hexToHsl($secondColor)['h']; ?>;
--base-two-s: <?php echo hexToHsl($secondColor)['s']; ?>%;
--base-two-l: <?php echo hexToHsl($secondColor)['l']; ?>%;
}

/* Light Mode - اللون الأخضر الغامق #06303E */
:root [data-theme=light] {
--base-h: 195;
--base-s: 82%;
--base-l: 13%;
--base-two-h: 195;
--base-two-s: 82%;
--base-two-l: 20%;
}