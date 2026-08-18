<?php
// Script to load all items from XML files into the database

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../resources/php/config.php');

$app = require_once(__DIR__ . '/../bootstrap/app.php');
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\items;
use Illuminate\Support\Facades\DB;

$assetsPath = __DIR__ . '/../../Assets/Items';
$itemCount = 0;
$errorCount = 0;

function parseItemXml($xmlPath) {
    try {
        $xml = simplexml_load_file($xmlPath);
        if (!$xml) return null;

        $attrs = $xml->attributes();

        return [
            'id' => (string)$attrs->id,
            'name' => (string)$attrs->name,
            'desc' => (string)$attrs->desc,
            'icon' => (string)$attrs->icon,
            'tags' => (string)$attrs->tags,
            'price' => (int)($attrs->price ?? 0),
            'tokens' => (int)($attrs->tokens ?? 0),
            'cid' => (string)($attrs->cid ?? 'com.smallworlds.entity.item.SpriteItem'),
        ];
    } catch (Exception $e) {
        return null;
    }
}

function getFilesRecursive($dir, $ext = '*.xml') {
    $files = [];
    if (!is_dir($dir)) return $files;

    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') continue;

        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            $files = array_merge($files, getFilesRecursive($path, $ext));
        } elseif (fnmatch($ext, $file)) {
            $files[] = $path;
        }
    }
    return $files;
}

echo "Loading items from: $assetsPath\n";
echo "Starting...\n\n";

$xmlFiles = getFilesRecursive($assetsPath);
echo "Found " . count($xmlFiles) . " XML files\n\n";

foreach ($xmlFiles as $xmlFile) {
    $itemData = parseItemXml($xmlFile);

    if (!$itemData || !$itemData['id']) {
        $errorCount++;
        continue;
    }

    $relativePath = str_replace(__DIR__ . '/../../', '', $xmlFile);

    try {
        // Use updateOrCreate to avoid duplicates
        items::updateOrCreate(
            ['model_id' => $itemData['id']],
            [
                'model_cid' => $itemData['cid'],
                'model_icon' => $itemData['icon'],
                'model_tags' => $itemData['tags'],
                'model_source' => $relativePath,
                'model_desc' => $itemData['desc'],
                'model_details' => '',
                'model_price_gold' => $itemData['price'],
                'model_price_tokens' => $itemData['tokens'],
            ]
        );
        $itemCount++;

        if ($itemCount % 100 === 0) {
            echo "Loaded $itemCount items...\n";
        }
    } catch (Exception $e) {
        $errorCount++;
        echo "Error loading item {$itemData['id']}: " . $e->getMessage() . "\n";
    }
}

echo "\n================================\n";
echo "Completed!\n";
echo "Items loaded: $itemCount\n";
echo "Errors: $errorCount\n";
echo "================================\n";
