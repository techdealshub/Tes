<?php
declare(strict_types=1);

$data = array (
  'reviews' => 
  array (
    0 => 
    array (
      'brand' => 'Apple',
      'title' => 'iPhone 17 Pro Max',
      'score' => 9.6,
      'camera_score' => 9.7,
      'gaming_score' => 9.7,
      'description' => 'The iPhone 17 Pro Max combines a premium design, exceptional display, powerful performance, advanced cameras, and excellent battery life.',
      'category' => 'Flagship',
      'image' => 'https://techdealshub.online/apple/img/iphone17promax_hero_3_2.webp',
      'url' => 'reviews/iphone-17-pro-max-reviews.html',
    ),
    1 => 
    array (
      'brand' => 'Google',
      'title' => 'Google Pixel 10a',
      'score' => 8.8,
      'camera_score' => 9,
      'gaming_score' => 8,
      'description' => 'The Google Pixel 10a is a refined mid-range smartphone built around Google\'s Tensor G4 chip, a smooth 6.3-inch 120Hz pOLED display, dependable dual cameras, a 5,100mAh battery, wireless charging, and seven years of software and security updates. Its hardware changes over the Pixel 9a are modest, but the combination of clean Pixel software, strong computational photography, long-term support, and reliable everyday performance makes it a capable choice in the $500 class.',
      'category' => 'Mid-Range',
      'image' => 'https://techdealshub.online/google/img/pixel10a_hero_3_2.webp',
      'url' => 'reviews/google-pixel-10a-reviews.html',
      'price' => '$499',
      'reviews' => '128',
      'display' => '6.3 inches pOLED Actua display',
      'processor' => 'Google Tensor G4',
      'camera' => '48MP Quad PD Dual Pixel, f/1.7',
      'battery' => '5,100 mAh',
      'badge' => 'Best Software',
    ),
    2 => 
    array (
      'brand' => 'Samsung',
      'title' => 'Samsung Galaxy S26 Ultra',
      'score' => 9.5,
      'camera_score' => 9.7,
      'gaming_score' => 9.7,
      'description' => 'The Samsung Galaxy S26 Ultra is a premium flagship smartphone built around the Snapdragon 8 Elite Gen 5 for Galaxy, a 6.9-inch QHD+ Dynamic AMOLED 2X display with a 120Hz refresh rate and built-in Privacy Display, a versatile 200MP quad-camera system, a 5,000mAh battery, fast 60W wired charging, S Pen support, and Samsung\'s latest Galaxy AI features. Its combination of flagship performance, advanced cameras, large high-resolution display, long software support, and productivity features makes it a complete premium Android flagship.',
      'category' => 'Flagship',
      'image' => 'https://techdealshub.online/samsung/img/galaxys26ultra_hero_3_2.webp',
      'url' => 'reviews/samsung-galaxy-s26-ultra-reviews.html',
      'price' => '$1,299',
      'reviews' => '214',
      'display' => '6.9 inches Dynamic AMOLED 2X',
      'processor' => 'Snapdragon 8 Elite Gen 5 for Galaxy',
      'camera' => '200MP wide, f/1.4',
      'battery' => '5,000 mAh',
      'badge' => 'Best Display',
    ),
    3 => 
    array (
      'brand' => 'Xiaomi',
      'title' => 'POCO F9 Ultra',
      'score' => 9.3,
      'camera_score' => 9.1,
      'gaming_score' => 9.8,
      'description' => 'The POCO F9 Ultra is a flagship-performance smartphone built around Qualcomm\'s Snapdragon 8 Elite Gen 5 platform, a dedicated VisionBoost D8 graphics chipset, a 6.9-inch 185Hz HyperRGB AMOLED display, a 200MP main camera, a 50MP periscope telephoto camera, a 50MP ultrawide camera, and a massive 8,050mAh battery. Its combination of high-end performance, advanced gaming features, fast 100W charging, 50W wireless charging, and a large high-refresh-rate display makes it one of POCO\'s most ambitious flagship-class smartphones.',
      'category' => 'Flagship',
      'image' => 'https://techdealshub.online/xiaomi/img/pocof9ultra_hero_3_2.webp',
      'url' => 'reviews/poco-f9-ultra-reviews.html',
      'price' => '$799',
      'reviews' => '186',
      'display' => '6.9 inches POCO HyperRGB AMOLED display',
      'processor' => 'Qualcomm Snapdragon 8 Elite Gen 5',
      'camera' => '200MP ultra-clear main camera, f/1.68, OIS',
      'battery' => '8,050 mAh',
      'badge' => 'Best Performance',
    ),
  ),
  'brands' => 
  array (
    0 => 'Apple',
    1 => 'Google',
    2 => 'Samsung',
    3 => 'Xiaomi',
  ),
);

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

return $data;
