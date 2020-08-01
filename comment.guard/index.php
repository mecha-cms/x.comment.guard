<?php

Route::hit('.comment/*', function($path) {
    if (Request::is('Post')) {
        $error = 0;
        $data = Post::get('comment');
        if (isset($data['content']) && preg_match_all('/\bhttps?:\/\/\S+/', $data['content'], $m)) {
            if (!empty($m[0]) && count($m[0]) > 5) {
                ++$error;
                Alert::error('Too much URL in the comment body.');
            }
        }
        foreach (stream(__DIR__ . DS . 'email.csv') as $email) {
            if (($email = trim($email)) && !empty($data['email']) && $data['email'] === $email) {
                ++$error;
                Alert::error('Blocked email address: %s', ['<em>' . $email . '</em>']);
                break;
            }
        }
        foreach (stream(__DIR__ . DS . 'ip.csv') as $ip) {
            if (($ip = trim($ip)) && Client::IP() === $ip) {
                ++$error;
                Alert::error('Blocked IP address: %s', ['<em>' . $ip . '</em>']);
                break;
            }
        }
        if ($error > 0) {
            Guard::kick($path);
        }
    }
}, 0);
