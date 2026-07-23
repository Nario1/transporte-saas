<?php
$directory = new RecursiveDirectoryIterator('c:/xampp/htdocs/transporte-saas/resources/views');
$iterator = new RecursiveIteratorIterator($directory);
$files = [];

foreach ($iterator as $info) {
    if ($info->isFile() && strpos($info->getFilename(), '.blade.php') !== false) {
        $files[] = $info->getPathname();
    }
}

$count = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;

    $replacements = [
        '- pattern="[0-9]{9}" title="Debe contener 9 dígitos numéricos">' => '->',
        '- pattern="[0-9]{8}" title="Debe contener 8 dígitos numéricos">' => '->',
        '- pattern="[A-Za-z0-9-]{6,8}" title="Formato válido: ABC-123">' => '->',
        '- pattern="[A-Za-zÀ-ÿ\s]{2,60}" title="Solo letras y espacios permitidos">' => '->'
    ];

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content !== $original) {
        file_put_contents($file, $content);
        $count++;
        echo "Fixed blade properties in: $file\n";
    }
}
echo "Total files fixed: $count\n";
