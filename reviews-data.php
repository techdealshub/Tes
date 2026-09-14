<?php
declare(strict_types=1);

$data = array (
  'reviews' => 
  array (
    0 => 
    array (
      'brand' => 'Apple',
      'title' => 'iPhone 17 Pro Review',
      'score' => 9.4,
      'description' => 'The iPhone 17 Pro combines a compact premium design with a powerful A19 Pro chip, advanced cameras, a bright ProMotion display, and strong all-day battery life.',
      'category' => 'Flagship Smartphone',
      'image' => 'https://techdealshub.online/apple/img/iphone17proreview_hero_3_2.webp',
      'url' => 'reviews/iphone-17-pro-reviews.html',
    ),
  ),
  'brands' => 
  array (
    0 => 'Apple',
  ),
);

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

return $data;
