<?php
/**
 * newsList — лента новостей (карточки tz-nl).
 */
$parent = isset($parent) ? (int)$parent : ($modx->resource ? (int)$modx->resource->get('id') : 0);
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

$c = $modx->newQuery('modResource');
$c->where(array('parent' => $parent, 'published' => 1, 'deleted' => 0));
$c->sortby('publishedon', 'DESC');
$c->sortby('id', 'DESC');
$items = $modx->getCollection('modResource', $c);

$esc = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

$o  = '<div class="tz-nl">';
$o .= '<header class="tz-nl__intro">';
$o .= '<span class="tz-label">Новости компании</span>';
$o .= '<p class="tz-nl__desc">Актуальные материалы о подборе персонала, рынке труда и работе агентства «ТРИЗА-Спутник».</p>';
$o .= '</header>';

if (!$items) {
    $o .= '<p class="tz-nl__empty">Пока нет опубликованных новостей.</p>';
    $o .= '</div>';
    return $o;
}

$o .= '<div class="tz-nl__grid">';

$i = 0;
foreach ($items as $it) {
    $alias = mb_strtolower((string)$it->get('alias'), 'UTF-8');
    if (in_array($alias, $excludeAliases, true)) continue;

    $uri = $it->get('uri');
    if ($uri === null || $uri === '') {
        $uri = 'blog/' . $it->get('alias');
    }
    $link = '/' . ltrim($uri, '/');

    $txt = $it->get('introtext');
    if ($txt === null || trim($txt) === '') {
        $txt = $it->get('pagetitle');
    }
    $txt = strip_tags($txt);
    if (mb_strlen($txt, 'UTF-8') > 220) {
        $txt = mb_substr($txt, 0, 217, 'UTF-8') . '…';
    }

    $ts = $parseDate($it);
    $dateStr = $ts > 0 ? date('d.m.Y', $ts) : '';

    $o .= '<a class="tz-nl__card tz-reveal" href="' . $esc($link) . '" style="--tz-delay:' . ($i % 12 * 0.05) . 's">';
    $o .= '<div class="tz-nl__card-top">';
    if ($dateStr !== '') {
        $o .= '<time class="tz-nl__date">' . $esc($dateStr) . '</time>';
    }
    $o .= '<span class="tz-nl__read">Читать <i class="fa fa-arrow-right" aria-hidden="true"></i></span>';
    $o .= '</div>';
    $o .= '<h2 class="tz-nl__title">' . $esc($it->get('pagetitle')) . '</h2>';
    if ($txt !== '') {
        $o .= '<p class="tz-nl__excerpt">' . $esc($txt) . '</p>';
    }
    $o .= '</a>';
    $i++;
}

$o .= '</div></div>';
return $o;
