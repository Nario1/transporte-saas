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

    // Regex to find input tags and add validations if they match certain names
    $content = preg_replace_callback('/<input([^>]*?)name=["\'](telefono|celular)["\']([^>]*?)>/i', function($m) {
        $tag = $m[0];
        if (strpos($tag, 'pattern=') === false && strpos($tag, 'type="hidden"') === false) {
            return str_replace('>', ' pattern="[0-9]{9}" title="Debe contener 9 dígitos numéricos"' . '>', $tag);
        }
        return $tag;
    }, $content);

    $content = preg_replace_callback('/<input([^>]*?)name=["\'](dni)["\']([^>]*?)>/i', function($m) {
        $tag = $m[0];
        if (strpos($tag, 'pattern=') === false && strpos($tag, 'type="hidden"') === false) {
            return str_replace('>', ' pattern="[0-9]{8}" title="Debe contener 8 dígitos numéricos"' . '>', $tag);
        }
        return $tag;
    }, $content);

    $content = preg_replace_callback('/<input([^>]*?)name=["\'](placa)["\']([^>]*?)>/i', function($m) {
        $tag = $m[0];
        if (strpos($tag, 'pattern=') === false && strpos($tag, 'type="hidden"') === false) {
            return str_replace('>', ' pattern="[A-Za-z0-9-]{6,8}" title="Formato válido: ABC-123"' . '>', $tag);
        }
        return $tag;
    }, $content);

    $content = preg_replace_callback('/<input([^>]*?)name=["\'](nombre|apellidos)["\']([^>]*?)>/i', function($m) {
        $tag = $m[0];
        if (strpos($tag, 'pattern=') === false && strpos($tag, 'type="hidden"') === false) {
            return str_replace('>', ' pattern="[A-Za-zÀ-ÿ\s]{2,60}" title="Solo letras y espacios permitidos"' . '>', $tag);
        }
        return $tag;
    }, $content);

    if ($content !== $original) {
        file_put_contents($file, $content);
        $count++;
    }
}
echo "Validations added to $count files.\n";
