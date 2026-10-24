<?php
$lines = file(__DIR__ . '/../assets/css/pronto-modern-frontend.css');
for ($i = 2615; $i <= 2680; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ': ' . trim($lines[$i]) . "\n";
    }
}
