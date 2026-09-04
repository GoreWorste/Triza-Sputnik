<?php
/**
 * staffingForm — заявка на подбор персонала (почта + файл + невидимая защита).
 * reCAPTCHA v3: ключи подставляются при деплое из .env (RECAPTCHA_SITE_KEY / RECAPTCHA_SECRET_KEY).
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

$recaptchaSiteKey = '{{RECAPTCHA_SITE_KEY}}';
$recaptchaSecretKey = '{{RECAPTCHA_SECRET_KEY}}';
$useRecaptcha = $recaptchaSiteKey !== '' && $recaptchaSecretKey !== '';

$to = 'sputnik.personal@yandex.ru';
$maxBytes = 5 * 1024 * 1024;
$allowed = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'txt' => 'text/plain',
    'rtf' => 'application/rtf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
];

$verifyRecaptcha = static function (string $secret, string $token, string $ip): bool {
    if ($secret === '' || $token === '') {
        return false;
    }
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query([
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]),
            'timeout' => 8,
        ],
    ]);
    $raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    if ($raw === false) {
        return false;
    }
    $data = json_decode($raw, true);
    return is_array($data)
        && !empty($data['success'])
        && (float)($data['score'] ?? 0) >= 0.5;
};

$verifySilent = static function (array $post): bool {
    if (trim((string)($post['tz_hp'] ?? '')) !== '') {
        return false;
    }
    $ts = (int)($post['tz_ts'] ?? 0);
    if ($ts <= 0) {
        return false;
    }
    $age = time() - $ts;
    return $age >= 3 && $age <= 86400;
};

$success = false;
$errors = [];
$old = [
    'company' => '',
    'contact' => '',
    'phone' => '',
    'email' => '',
    'position' => '',
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tz_form'] ?? '') === 'staffing') {
    foreach ($old as $k => $_) {
        $old[$k] = trim((string)($_POST[$k] ?? ''));
    }

    if ($old['company'] === '') $errors[] = 'Укажите название компании.';
    if ($old['contact'] === '') $errors[] = 'Укажите контактное лицо.';
    if ($old['phone'] === '') $errors[] = 'Укажите телефон.';
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Укажите корректный email.';
    }
    if ($old['message'] === '') $errors[] = 'Опишите задачу или прикрепите заявку.';

    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $captchaOk = false;
    if ($useRecaptcha) {
        $captchaOk = $verifyRecaptcha(
            $recaptchaSecretKey,
            (string)($_POST['g-recaptcha-response'] ?? ''),
            $ip
        );
    } else {
        $captchaOk = $verifySilent($_POST);
    }
    if (!$captchaOk) {
        $errors[] = 'Не удалось проверить отправку. Обновите страницу и попробуйте снова.';
    }

    $filePart = null;
    if (!empty($_FILES['attachment']['name'])) {
        $f = $_FILES['attachment'];
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errors[] = 'Не удалось загрузить файл. Попробуйте ещё раз.';
        } elseif (($f['size'] ?? 0) > $maxBytes) {
            $errors[] = 'Файл больше 5 МБ.';
        } else {
            $ext = strtolower(pathinfo((string)$f['name'], PATHINFO_EXTENSION));
            if (!isset($allowed[$ext])) {
                $errors[] = 'Допустимы файлы: PDF, DOC, DOCX, XLS, XLSX, TXT, RTF, JPG, PNG.';
            } else {
                $tmp = (string)$f['tmp_name'];
                $data = is_readable($tmp) ? file_get_contents($tmp) : false;
                if ($data === false) {
                    $errors[] = 'Не удалось прочитать вложение.';
                } else {
                    $filePart = [
                        'name' => basename((string)$f['name']),
                        'type' => $allowed[$ext],
                        'data' => $data,
                    ];
                }
            }
        }
    }

    if (!$errors) {
        $page = $modx->resource ? (string)$modx->resource->get('pagetitle') : 'Сайт';
        $uri = $modx->resource ? (string)$modx->resource->get('uri') : '';
        $bodyText = "Заявка на подбор персонала\n"
            . "Страница: {$page} (/{$uri})\n"
            . "Компания: {$old['company']}\n"
            . "Контакт: {$old['contact']}\n"
            . "Телефон: {$old['phone']}\n"
            . "Email: {$old['email']}\n"
            . "Должность / профиль: {$old['position']}\n\n"
            . "Сообщение:\n{$old['message']}\n";

        $from = $modx->getOption('emailsender') ?: 'noreply@romanovivv.ru';
        $subject = 'Заявка на подбор: ' . $old['company'];
        $headers = [];
        $headers[] = 'From: СПУТНИК-Персонал <' . $from . '>';
        $headers[] = 'Reply-To: ' . $old['contact'] . ' <' . $old['email'] . '>';
        $headers[] = 'MIME-Version: 1.0';

        if ($filePart) {
            $boundary = 'tz_' . bin2hex(random_bytes(8));
            $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
            $message = "--{$boundary}\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: 8bit\r\n\r\n"
                . $bodyText . "\r\n"
                . "--{$boundary}\r\n"
                . 'Content-Type: ' . $filePart['type'] . '; name="' . $filePart['name'] . "\"\r\n"
                . "Content-Transfer-Encoding: base64\r\n"
                . 'Content-Disposition: attachment; filename="' . $filePart['name'] . "\"\r\n\r\n"
                . chunk_split(base64_encode($filePart['data'])) . "\r\n"
                . "--{$boundary}--";
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $message = $bodyText;
        }

        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $message, implode("\r\n", $headers));
        if ($ok) {
            $success = true;
            $old = array_map(static function () { return ''; }, $old);
        } else {
            $errors[] = 'Не удалось отправить заявку. Позвоните 8 (495) 740-78-88 или напишите на sputnik.personal@yandex.ru.';
        }
    }
}

$formTs = time();
$e = static function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };

$out = '<section class="tz-staff-form" id="tz-staff-form">';
$out .= '<div class="tz-staff-form__intro">';
$out .= '<h2>Заказать подбор персонала</h2>';
$out .= '<p>У вас возникли дополнительные вопросы? Мы предоставим вам специализированную консультацию. Заполните форму — заявка придёт на <a href="mailto:sputnik.personal@yandex.ru">sputnik.personal@yandex.ru</a>.</p>';
$out .= '</div>';

if ($success) {
    $out .= '<div class="tz-staff-form__ok" role="status">Спасибо! Заявка отправлена. Мы свяжемся с вами в ближайшее время.</div>';
}
if ($errors) {
    $out .= '<div class="tz-staff-form__err" role="alert"><ul>';
    foreach ($errors as $err) $out .= '<li>' . $e($err) . '</li>';
    $out .= '</ul></div>';
}

$formAttrs = 'class="tz-staff-form__form" method="post" enctype="multipart/form-data" action="#tz-staff-form" novalidate';
if ($useRecaptcha) {
    $formAttrs .= ' data-recaptcha-site-key="' . $e($recaptchaSiteKey) . '"';
}
$out .= '<form ' . $formAttrs . '>';
$out .= '<input type="hidden" name="tz_form" value="staffing">';
$out .= '<input type="hidden" name="tz_ts" value="' . $formTs . '">';
$out .= '<input type="hidden" name="g-recaptcha-response" value="">';
$out .= '<label class="tz-staff-form__hp" aria-hidden="true"><span>Сайт</span><input type="text" name="tz_hp" tabindex="-1" autocomplete="off"></label>';
$out .= '<div class="tz-staff-form__grid">';
$out .= '<label class="tz-staff-form__field"><span>Компания *</span><input type="text" name="company" required value="' . $e($old['company']) . '"></label>';
$out .= '<label class="tz-staff-form__field"><span>Контактное лицо *</span><input type="text" name="contact" required value="' . $e($old['contact']) . '"></label>';
$out .= '<label class="tz-staff-form__field"><span>Телефон *</span><input type="tel" name="phone" required value="' . $e($old['phone']) . '"></label>';
$out .= '<label class="tz-staff-form__field"><span>Email *</span><input type="email" name="email" required value="' . $e($old['email']) . '"></label>';
$out .= '<label class="tz-staff-form__field tz-staff-form__field--full"><span>Должность / профиль поиска</span><input type="text" name="position" value="' . $e($old['position']) . '"></label>';
$out .= '<label class="tz-staff-form__field tz-staff-form__field--full"><span>Описание задачи *</span><textarea name="message" rows="10" required>' . $e($old['message']) . '</textarea></label>';
$out .= '<label class="tz-staff-form__field tz-staff-form__field--full"><span>Прикрепить заявку / ТЗ (до 5 МБ)</span><input type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.rtf,.jpg,.jpeg,.png"></label>';
$out .= '</div>';
$out .= '<button type="submit" class="tz-btn tz-btn--glow tz-staff-form__submit">Отправить заявку</button>';
$out .= '<p class="tz-staff-form__note">Нажимая кнопку, вы соглашаетесь с <a href="/politika-obrabotki-personalnykh-dannykh-polzovatelej-sajta">политикой обработки персональных данных</a>.</p>';
$out .= '</form></section>';

if ($useRecaptcha) {
    $out .= '<script src="https://www.google.com/recaptcha/api.js?render=' . $e($recaptchaSiteKey) . '" async defer></script>';
}

return $out;
