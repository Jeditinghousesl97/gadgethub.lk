<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
header('Cache-Control: public, max-age=60');

echo json_encode([
    'whatsapp'  => getSetting('store_whatsapp', '94777237962'),
    'phone'     => getSetting('store_phone',    '+94 77 723 7962'),
    'email'     => getSetting('store_email',    'genecoretech@gmail.com'),
    'address'   => getSetting('store_address',  'Lenabatuwa, Kamburupitiya, Sri Lanka - 81100'),
    'hours'     => getSetting('store_hours',    'Mon - Sat: 8:00 AM - 7:00 PM'),
    'facebook'  => getSetting('facebook_url',   'https://web.facebook.com/genecoretech'),
    'instagram' => getSetting('instagram_url',  ''),
    'youtube'   => getSetting('youtube_url',    ''),
    'tiktok'    => getSetting('tiktok_url',     ''),
    'announcements' => [
        ['icon' => getSetting('ann_icon_1', 'fas fa-tag'),        'text' => getSetting('ann_text_1', 'Free delivery on orders over Rs. 10,000'),                 'link' => getSetting('ann_link_1', '')],
        ['icon' => getSetting('ann_icon_2', 'fas fa-boxes'),      'text' => getSetting('ann_text_2', 'Wholesale prices available - Contact us today'),          'link' => getSetting('ann_link_2', 'wholesale.php')],
        ['icon' => getSetting('ann_icon_3', 'fas fa-shield-alt'), 'text' => getSetting('ann_text_3', '100% Genuine products with manufacturer warranty'),        'link' => getSetting('ann_link_3', '')],
        ['icon' => getSetting('ann_icon_4', 'fas fa-headset'),    'text' => getSetting('ann_text_4', '24/7 Customer support'),                                  'link' => getSetting('ann_link_4', 'contact.php')],
        ['icon' => getSetting('ann_icon_5', 'fas fa-truck'),      'text' => getSetting('ann_text_5', 'Fast island-wide delivery'),                             'link' => getSetting('ann_link_5', 'shipping.php')],
        ['icon' => getSetting('ann_icon_6', 'fas fa-star'),       'text' => getSetting('ann_text_6', 'Best prices guaranteed - Retail & Wholesale'),          'link' => getSetting('ann_link_6', 'shop.php')],
    ],
    'theme'     => [
        'primary'     => getSetting('theme_primary', '#d4920a'),
        'primary_lt'  => getSetting('theme_primary_lt', '#f0a820'),
        'accent'      => getSetting('theme_accent', '#ff6b00'),
        'green'       => getSetting('theme_green', '#16a34a'),
        'wa'          => getSetting('theme_wa', '#25d366'),
        'bg'          => getSetting('theme_bg', '#141414'),
        'bg2'         => getSetting('theme_bg2', '#191919'),
        'bg3'         => getSetting('theme_bg3', '#1e1e1e'),
        'bg4'         => getSetting('theme_bg4', '#252525'),
        'card'        => getSetting('theme_card', '#1d1d1d'),
        'card_hover'  => getSetting('theme_card_hover', '#242424'),
        'border'      => getSetting('theme_border', '#303030'),
        'border_lt'   => getSetting('theme_border_lt', '#3d3d3d'),
        'text'        => getSetting('theme_text', '#ffffff'),
        'text2'       => getSetting('theme_text2', '#e8e8e8'),
        'text_muted'  => getSetting('theme_text_muted', '#999999'),
        'text_dim'    => getSetting('theme_text_dim', '#505050'),
    ],
]);
