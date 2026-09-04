<?php
$uri = '';
if ($modx->resource) {
    $uri = ltrim((string)$modx->resource->get('uri'), '/');
}

$active = 'home';
$map = [
    'about' => 'about',
    'vacancy' => 'vacancy',
    'rekrutment' => 'rekrutment',
    'regionalnyy-podbor' => 'regional',
    'autstaffing' => 'outstaffing',
    'executive-search' => 'executive',
    'khedkhanting' => 'headhunting',
    'domashnij-personal' => 'domestic',
    'trainings-and-webinars' => 'trainings',
    'contacts' => 'contacts',
    'blog' => 'blog',
    'zapros-na-podbor-personala' => 'rekrutment',
];
foreach ($map as $prefix => $key) {
    if ($uri === $prefix || strpos($uri, $prefix . '/') === 0) {
        $active = $key;
        break;
    }
}
if ($uri === '' || $uri === 'home' || strpos($uri, 'home/') === 0) {
    $active = 'home';
}

function tzA($s, $a) {
    return $s === $a ? ' is-active' : '';
}

$items = [
    ['home', '/', 'ГЛАВНАЯ'],
    ['about', '/about', 'О КОМПАНИИ'],
    ['vacancy', '/vacancy', 'ВАКАНСИИ'],
    ['rekrutment', '/rekrutment', 'РЕКРУТМЕНТ'],
    ['regional', '/regionalnyy-podbor', 'РЕГИОНАЛЬНЫЙ ПОДБОР'],
    ['outstaffing', '/autstaffing', 'АУТСТАФФИНГ'],
    ['executive', '/executive-search', 'EXECUTIVE SEARCH'],
    ['headhunting', '/khedkhanting', 'ХЕДХАНТИНГ'],
    ['domestic', '/domashnij-personal', 'ДОМАШНИЙ ПЕРСОНАЛ'],
    ['trainings', '/trainings-and-webinars', 'ОБУЧЕНИЕ'],
    ['contacts', '/contacts', 'КОНТАКТЫ'],
    ['blog', '/blog', 'НОВОСТИ'],
];

$out = '<ul class="tz-nav__list">';
foreach ($items as $it) {
    [$key, $href, $label] = $it;
    $out .= '<li class="tz-nav__item' . tzA($key, $active) . '"><a href="' . $href . '">' . $label . '</a></li>';
}
$out .= '</ul>';
return $out;
