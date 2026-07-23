<?php
$directory = new RecursiveDirectoryIterator('c:/xampp/htdocs/transporte-saas/app/Http/Requests');
$iterator = new RecursiveIteratorIterator($directory);
$files = [];

foreach ($iterator as $info) {
    if ($info->isFile() && strpos($info->getFilename(), '.php') !== false) {
        $files[] = $info->getPathname();
    }
}

$count = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Check if the file has empty rules: return [ // ]; or similar
    if (preg_match('/public function rules\(\):\s*array\s*\{\s*return\s*\[\s*(?:\/\/)?\s*\];\s*\}/su', $content)) {
        
        $newRules = <<<PHP
public function rules(): array
    {
        // Dynamic catch-all for validated() behavior
        \$keys = array_keys(\$this->all());
        \$rules = [];
        foreach (\$keys as \$key) {
            if (!in_array(\$key, ['_token', '_method'])) {
                \$rules[\$key] = 'nullable';
            }
        }
        return \$rules;
    }
PHP;

        $content = preg_replace('/public function rules\(\):\s*array\s*\{\s*return\s*\[\s*(?:\/\/)?\s*\];\s*\}/su', $newRules, $content);
        
        file_put_contents($file, $content);
        $count++;
        echo "Updated: $file\n";
    }
}
echo "Total FormRequests updated with catch-all rules: $count\n";
