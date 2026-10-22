<?php
// tools/generate_dark_logo.php
$srcPath = __DIR__ . '/../assets/uploads/logo.png';
$dstPath = __DIR__ . '/../assets/uploads/logo-dark.png';

if (!file_exists($srcPath)) {
    echo "Erro: logo.png não encontrado\n";
    exit(1);
}

$im = imagecreatefrompng($srcPath);
imagealphablending($im, false);
imagesavealpha($im, true);

$w = imagesx($im);
$h = imagesy($im);

$out = imagecreatetruecolor($w, $h);
imagealphablending($out, false);
imagesavealpha($out, true);
$transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
imagefilledrectangle($out, 0, 0, $w, $h, $transparent);

// Cor alvo para os textos e formas brancas: Teal Escuro Hospitalar Pronto Saúde #0a6b74
$targetR = 0x0A;
$targetG = 0x6B;
$targetB = 0x74;

for ($x = 0; $x < $w; $x++) {
    for ($y = 0; $y < $h; $y++) {
        $rgba = imagecolorat($im, $x, $y);
        $alpha = ($rgba & 0x7F000000) >> 24;
        
        if ($alpha >= 127) {
            imagesetpixel($out, $x, $y, $transparent);
            continue;
        }

        $r = ($rgba >> 16) & 0xFF;
        $g = ($rgba >> 8) & 0xFF;
        $b = $rgba & 0xFF;

        // Se for um pixel claro/branco (luminosidade alta), substitui pela cor Teal escura #0a6b74
        // mantendo a proporção de brilho e o canal alfa intacto
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b);

        // Se for o verde menta (#87cca4) ou próximo a tons verdes distintivos, preserva
        $isMint = ($g > 150 && $r < 180 && $b > 120 && $g > $r && $g > $b);

        if ($isMint) {
            // Preserva elemento verde distintivo da marca
            $color = imagecolorallocatealpha($out, $r, $g, $b, $alpha);
        } elseif ($luminance > 160) {
            // É branco/cinza claro do texto -> transforma em Teal #0a6b74 ponderado pela luminosidade
            $factor = $luminance / 255.0;
            $newR = (int)round($targetR * $factor);
            $newG = (int)round($targetG * $factor);
            $newB = (int)round($targetB * $factor);
            $color = imagecolorallocatealpha($out, $newR, $newG, $newB, $alpha);
        } else {
            // Pixel já escuro ou detalhe, preserva
            $color = imagecolorallocatealpha($out, $r, $g, $b, $alpha);
        }

        imagesetpixel($out, $x, $y, $color);
    }
}

imagepng($out, $dstPath);
imagedestroy($im);
imagedestroy($out);

echo "logo-dark.png gerado com sucesso em: $dstPath (" . filesize($dstPath) . " bytes)\n";
