<?php
/**
 * trainingsContent — hub page for trainings section.
 */
$file = MODX_BASE_PATH . 'assets/trainings/trainings-content.html';
if (is_readable($file)) {
    return file_get_contents($file);
}
return '<p>Раздел тренингов и вебинаров скоро будет обновлён. Свяжитесь с нами по телефону <a href="tel:+74957407888">8 (495) 740-78-88</a>.</p>';
