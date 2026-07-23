<?php
$directory = new RecursiveDirectoryIterator('c:/xampp/htdocs/transporte-saas/app/Http/Requests');
$iterator = new RecursiveIteratorIterator($directory);
foreach ($iterator as $info) {
    if ($info->isFile() && strpos($info->getFilename(), '.php') !== false) {
        $file = $info->getPathname();
        $content = file_get_contents($file);
        $newContent = str_replace('return false;', 'return true;', $content);
        if ($newContent !== $content) {
            file_put_contents($file, $newContent);
            echo "Fixed: $file\n";
        }
    }
}
