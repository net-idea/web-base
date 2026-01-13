<?php
declare(strict_types=1);

return [
  'index' => [
    'title' => 'Home - My Website',
    'description' => 'Short tagline or summary of your website or product.',
    'keywords' => 'keyword1, keyword2, keyword3',
    'canonical' => '/',
    'robots' => 'index,follow',
    'og_image' => '/assets/og/home.jpg',
    'cms' => false,
    'nav' => true,
    'nav_label' => 'Home',
    'nav_order' => 10,
  ],
  'imprint' => [
    'title' => 'Imprint - My Website',
    'description' => 'Legal notice and company information.',
    'keywords' => 'impressum, legal notice',
    'canonical' => '/imprint',
    'robots' => 'index,follow',
    'og_image' => '/assets/og/imprint.jpg',
    'cms' => true,
    'nav' => false,
    'nav_label' => 'Impressum',
    'nav_order' => 90,
  ],
  'privacy' => [
    'title' => 'Privacy - My Website',
    'description' => 'Information about processing of personal data (GDPR/DSGVO).',
    'keywords' => 'privacy, dsgvo, privacy policy',
    'canonical' => '/privacy',
    'robots' => 'index,follow',
    'og_image' => '/assets/og/privacy.jpg',
    'cms' => true,
    'nav' => false,
    'nav_label' => 'privacy',
    'nav_order' => 100,
  ],
];
