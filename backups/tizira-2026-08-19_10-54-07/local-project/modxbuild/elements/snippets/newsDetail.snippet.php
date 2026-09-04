<?php
/**
 * newsDetail — страница одной новости (tz-nd).
 */
$r = $modx->resource;
if (!$r) return '';
$esc = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

$id = (int)$r->get('id');
$title = $r->get('pagetitle');
$content = (string)$r->get('content');
$intro = trim((string)$r->get('introtext'));

$excludeAliases = array('test', 'пэдийуаи', 'бутуту', 'жлпижмцкломосывзщостэ', 'еуые');

$parseDate = function ($resource) {
    $raw = $resource->get('publishedon');
    if ($raw === null || $raw === '' || $raw === '0' || $raw === 0) {
        $raw = $resource->get('createdon');
    }
    if (is_string($raw) && !ctype_digit($raw)) {
        $ts = strtotime($raw);
        return $ts ? $ts : 0;
    }
    return (int)$raw;
};

$ts = $parseDate($r);
$dateStr = $ts > 0 ? date('d.m.Y', $ts) : '';
$dateIso = $ts > 0 ? date('Y-m-d', $ts) : '';

$blogId = (int)$modx->findResource('blog');
$related = array();
if ($blogId) {
    $c = $modx->newQuery('modResource');
    $c->where(array(
        'parent' => $blogId,
        'published' => 1,
        'deleted' => 0,
        'id:!=' => $id,
    ));
    $c->sortby('publishedon', 'DESC');
    $c->sortby('id', 'DESC');
    $c->limit(24);
    $items = $modx->getCollection('modResource', $c);
    foreach ($items as $item) {
        $alias = mb_strtolower((string)$item->get('alias'), 'UTF-8');
        if (in_array($alias, $excludeAliases, true)) continue;
        $related[] = $item;
        if (count($related) >= 4) break;
    }
}

$o  = '<article class="tz-nd">';
$o .= '<header class="tz-nd__hero">';
$o .= '<div class="tz-nd__hero-top">';
$o .= '<span class="tz-nd__badge"><i class="fa fa-newspaper-o" aria-hidden="true"></i> Новость</span>';
$o .= '<a href="/blog" class="tz-nd__back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Все новости</a>';
$o .= '</div>';
$o .= '<h1 class="tz-nd__title">' . $esc($title) . '</h1>';
if ($dateStr !== '') {
    $o .= '<time class="tz-nd__date" datetime="' . $esc($dateIso) . '"><i class="fa fa-calendar" aria-hidden="true"></i> ' . $esc($dateStr) . '</time>';
}
if ($intro !== '' && $intro !== $title) {
    $o .= '<p class="tz-nd__lead">' . $esc($intro) . '</p>';
}
$o .= '</header>';

$o .= '<div class="tz-nd__layout">';
$o .= '<div class="tz-nd__main">';
if (trim(strip_tags($content)) !== '') {
    $o .= '<div class="tz-nd__body tz-prose">' . $content . '</div>';
} else {
    $o .= '<p class="tz-nd__empty">Текст новости скоро будет опубликован.</p>';
}
$o .= '</div>';

$o .= '<aside class="tz-nd__aside">';
$o .= '<div class="tz-nd__card">';
$o .= '<h2 class="tz-nd__card-title">Нужна помощь?</h2>';
$o .= '<p class="tz-nd__card-lead">Подбор персонала и трудоустройство в Москве и области</p>';
$o .= '<a href="tel:+79637112724" class="tz-btn tz-btn--glow tz-btn--block"><i class="fa fa-phone" aria-hidden="true"></i> 8 (963) 711-27-24</a>';
$o .= '<a href="tel:+74957407888" class="tz-btn tz-btn--line tz-btn--block"><i class="fa fa-briefcase" aria-hidden="true"></i> 8 (495) 740-78-88</a>';
$o .= '<a href="/vacancy" class="tz-btn tz-btn--line tz-btn--block"><i class="fa fa-search" aria-hidden="true"></i> Смотреть вакансии</a>';
$o .= '</div>';
$o .= '</aside>';
$o .= '</div>';

if ($related) {
    $o .= '<section class="tz-nd__related">';
    $o .= '<div class="tz-nd__related-head">';
    $o .= '<h2 class="tz-nd__related-title">Другие новости</h2>';
    $o .= '<a href="/blog" class="tz-link-arrow">Вся лента <i class="fa fa-arrow-right" aria-hidden="true"></i></a>';
    $o .= '</div>';
    $o .= '<div class="tz-nd__related-grid">';
    foreach ($related as $item) {
        $alias = mb_strtolower((string)$item->get('alias'), 'UTF-8');
        if (in_array($alias, $excludeAliases, true)) continue;
        $uri = $item->get('uri');
        if ($uri === null || $uri === '') {
            $uri = 'blog/' . $item->get('alias');
        }
        $link = '/' . ltrim($uri, '/');
        $relDate = $parseDate($item);
        $relDateStr = $relDate > 0 ? date('d.m.Y', $relDate) : '';
        $relTitle = $item->get('introtext');
        if ($relTitle === null || trim($relTitle) === '') {
            $relTitle = $item->get('pagetitle');
        }
        $o .= '<a class="tz-nd__related-item" href="' . $esc($link) . '">';
        if ($relDateStr !== '') {
            $o .= '<time class="tz-nd__related-date">' . $esc($relDateStr) . '</time>';
        }
        $o .= '<span class="tz-nd__related-name">' . $esc(mb_substr(strip_tags($relTitle), 0, 140, 'UTF-8')) . '</span>';
        $o .= '<span class="tz-nd__related-go"><i class="fa fa-arrow-right" aria-hidden="true"></i></span>';
        $o .= '</a>';
    }
    $o .= '</div></section>';
}

$o .= '</article>';
return $o;
