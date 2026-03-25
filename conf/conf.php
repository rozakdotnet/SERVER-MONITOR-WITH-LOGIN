<?php
return [

  // user login, for username example is bossku
  'users' => [
    'bossku' => 'THE-HASH-PASSWORD-CODE' // Replace only with hash code
  ],
  
  'site' => [
    'url' => 'https://www.bossku.eu.org', //Homepage link
    'name' => 'HOMESERVER', // Website name
    'background' => 'img/backg.jpg', // replace image background
    'robots' => 'noindex,nofollow', // meta robots (index or noindex, follow or nofollow
    'og:image' => 'img/netwrks.png' // meta image
  ],

  'menu' => [
      'item' => [
          [
              'text' => 'Home', // Text for a menu
              'url' => 'https://www.bossku.eu.org/', // Link for the menu
              'target' => '_self'    // open in same tab
          ],
          [
              'text' => 'Services',
              'url' => 'services.php',
              'target' => '_self'
          ],
          [
              'text' => 'About',
              'url' => 'about.php',
              'target' => '_self'
          ],
          [
              'text' => 'External 1',
              'url' => 'https://example.com',
              'target' => '_blank'   // open in new tab
          ],
          [
              'text' => 'Logout',
              'url' => 'logout.php',
              'target' => '_self'
          ]
      ]
  ],

  'page' => [
    'index' => [
      'title' => 'DASHBOARD', // the meta title for homepage
      'desc' => 'Ini adalah homeserver kedua saya, bermula ketika saya memerlukan sistem muat turun harian data cadangan dari beberapa laman web yang saya uruskan.' // the meta descriptions for homepage
    ],
    'about' => [
      'title' => 'ABOUT', // the meta title for about.php 
      'desc' => 'This is my second homemeserver, it started when i needed a system to download daily backup data from several websites I manage.' // the meta description for for about.php
    ],
    'services' => [
      'title' => 'THE TITLE',
      'desc' => 'The description.'
    ]
    // can add more meta here, add a comma (,) after ]
  ],

  'adguard' => [
    'url' => 'http://192.168.0.3:3000', // can use custom domain without / in the end
    'user' => 'user@example', // replace this
    'pass' => 'thePasswd'
  ],

  'immich' => [
    'url' => 'http://192.168.0.3:2283', // can use custom domain without / in the end
    'key' => 'API-KEY' // Replace with your Immich api key
  ],


  // login session
  'session_timeout' => 1800 // 30 minutes

];
