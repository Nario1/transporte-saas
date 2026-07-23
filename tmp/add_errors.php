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
    if (strpos($content, '<div class="form-group"') !== false && strpos($content, '<form') !== false) {
        
        $newContent = preg_replace_callback('/(<div class="form-group"[^>]*>)(.*?)(<\/div>)/s', function($matches) {
            $divOpen = $matches[1];
            $inner = $matches[2];
            $divClose = $matches[3];
            
            if (strpos($inner, '@error') !== false) {
                return $matches[0];
            }
            
            // For checkbox arrays like rutas[] we still want to show the error once, maybe skip or use cleanName
            if (preg_match('/name="([^"]+)"/', $inner, $nameMatches)) {
                $name = $nameMatches[1];
                $cleanName = str_replace('[]', '', $name);
                
                $errorHtml = "\n                        @error('{$cleanName}')\n                            <span style=\"color: #dc2626; font-size: 0.8rem; margin-top: 4px; display: block;\">{{ \$message }}</span>\n                        @enderror\n                    ";
                
                return $divOpen . $inner . $errorHtml . $divClose;
            }
            
            return $matches[0];
        }, $content);
        
        if ($newContent !== $content) {
            file_put_contents($file, $newContent);
            echo "Updated: $file\n";
            $count++;
        }
    }
}
echo "Total updated: $count\n";
