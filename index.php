<?php
/**
 * DABA MAGIC - Authentic Indian Cuisine
 * Main Entry Point (index.php)
 * Paragon-Inspired Modern Website Architecture
 */

// Auto-Asset Synchronizer for Local XAMPP Environment
$targetImagesDir = __DIR__ . '/assets/images';
if (!file_exists($targetImagesDir)) {
    @mkdir($targetImagesDir, 0777, true);
}

$srcBrainDir = 'C:/Users/sojin/.gemini/antigravity-ide/brain/5b7f554e-224a-494c-a13c-9ebc3ca88f0f';
$assetMap = [
    'media__1786418698548.png'      => 'logo.png',
    'hero_biryani_1786418979544.png'  => 'hero_biryani.png',
    'tandoori_kebab_1786418994909.png'=> 'tandoori_kebab.png',
    'butter_chicken_1786419157863.png'=> 'butter_chicken.png',
    'crisp_dosa_1786419174333.png'    => 'crisp_dosa.png',
    'ambiance_1786419194398.png'      => 'ambiance.png'
];

foreach ($assetMap as $srcFile => $destFile) {
    $srcPath = $srcBrainDir . '/' . $srcFile;
    $destPath = $targetImagesDir . '/' . $destFile;
    if (file_exists($srcPath) && (!file_exists($destPath) || filesize($destPath) === 0)) {
        @copy($srcPath, $destPath);
    }
}

// 1. Include HTML Head & Header Bar
include_once __DIR__ . '/includes/header.php';

// 2. Preloader Animation Screen
include_once __DIR__ . '/components/loader.php';

// 3. Hero Section
include_once __DIR__ . '/components/hero.php';

// 4. "OUR MENU" Specials Section (Placing directly after Hero as in Paragon site)
include_once __DIR__ . '/components/specials.php';

// 5. About / Heritage & Philosophy Section
include_once __DIR__ . '/components/about.php';

// 6. Culinary Craft & A Heart-To-Heart Experience Section
include_once __DIR__ . '/components/experience.php';

// 7. 3D Cover Flow Gallery Section
include_once __DIR__ . '/components/gallery.php';

// 9. Online Table Reservation & Contact Section
include_once __DIR__ . '/components/contact.php';

// 10. Site Footer
include_once __DIR__ . '/includes/footer.php';
