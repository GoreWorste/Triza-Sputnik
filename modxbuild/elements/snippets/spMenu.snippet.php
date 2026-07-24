<?php
$uri = '';
if ($modx->resource) {
    $uri = ltrim((string)$modx->resource->get('uri'), '/');
}
$active = 'home';
if (strpos($uri, 'about') === 0) $active = 'about';
elseif (strpos($uri, 'vacancy') === 0) $active = 'vacancy';
elseif (strpos($uri, 'contacts') === 0) $active = 'contacts';
elseif (strpos($uri, 'blog') === 0) $active = 'blog';
elseif (strpos($uri, 'trainings-and-webinars') === 0) $active = 'trainings';

function tzA($s, $a) { return $s === $a ? ' is-active' : ''; }

$out  = '<ul class="tz-nav__list">';
$out .= '<li class="tz-nav__item tz-nav__item--drop' . tzA('home', $active) . '"><a href="/">Главная</a><ul class="tz-nav__drop"><li><a href="/home/sertifikat-sto">Сертификат СТО</a></li></ul></li>';
$out .= '<li class="tz-nav__item' . tzA('about', $active) . '"><a href="/about">О компании</a></li>';
$out .= '<li class="tz-nav__item tz-nav__item--drop' . tzA('trainings', $active) . '"><a href="/trainings-and-webinars">Тренинги</a><ul class="tz-nav__drop">';
$out .= '<li><a href="/trainings-and-webinars/podbor-personala-v-moskvepodbor-personala-v-moskve-treningi-i-vebinaryi">Подбор в Москве</a></li>';
$out .= '<li><a href="/trainings-and-webinars/programma-avtorskogo-modulnogo-treninga-olgi-shevelevoj">Тренинг Шевелевой</a></li>';
$out .= '<li><a href="/trainings-and-webinars/seminaryi-i-treningovyie-programmyi-dlya-biznesa">Семинары</a></li>';
$out .= '<li><a href="/trainings-and-webinars/treningi">Тренинги</a></li>';
$out .= '<li><a href="/trainings-and-webinars/meropriyatiya-po-tekhnicheskomu-auditu">Тех. аудит</a></li>';
$out .= '</ul></li>';
$out .= '<li class="tz-nav__item' . tzA('vacancy', $active) . '"><a href="/vacancy">Вакансии</a></li>';
$out .= '<li class="tz-nav__item' . tzA('contacts', $active) . '"><a href="/contacts">Контакты</a></li>';
$out .= '<li class="tz-nav__item' . tzA('blog', $active) . '"><a href="/blog">Новости</a></li>';
$out .= '</ul>';
return $out;
