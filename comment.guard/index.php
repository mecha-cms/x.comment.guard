<?php namespace x;

function comment__guard($path) {
    $link_max = 5;
    if (\Request::is('Post')) {
        $error = 0;
        $lot = (array) \Post::get('comment');
        if ($content = ($lot['content'] ?? "")) {
            if (
                false !== \strpos($content, '://') &&
                \preg_match_all('/\bhttps?:\/\/\S+/', $content, $m)
            ) {
                if (\count($m[0]) > $link_max) {
                    ++$error;
                    \Alert::error('Too many links in the comment.');
                }
            }
            foreach (\stream(__DIR__ . \DS . 'words.txt') as $v) {
                if ("" === ($v = \trim($v))) {
                    continue;
                }
                if (false !== \stripos($content, $v)) {
                    ++$error;
                    \Alert::error('Please choose another word: %s', ['<mark>' . \htmlspecialchars($v) . '</mark>']);
                    break;
                }
            }
        }
        if ($email = ($lot['email'] ?? "")) {
            foreach (\stream(__DIR__ . \DS . 'email.txt') as $v) {
                if ("" === ($v = \trim($v))) {
                    continue;
                }
                if ($v === $email) {
                    ++$error;
                    \Alert::error('Blocked email address: %s', ['<mark>' . $v . '</mark>']);
                    break;
                }
            }
        }
        if ($ip = \Client::IP()) {
            foreach (\stream(__DIR__ . \DS . 'ip.txt') as $v) {
                if ("" === ($v = \trim($v))) {
                    continue;
                }
                if ($v === $ip) {
                    ++$error;
                    \Alert::error('Blocked IP address: %s', ['<mark>' . $v . '</mark>']);
                    break;
                }
            }
        }
        if ($error > 0) {
            \Session::set('form.comment', $lot);
            \Guard::kick($path);
        }
    }
}

\Route::hit('.comment/*', __NAMESPACE__ . "\\comment__guard", 0);
