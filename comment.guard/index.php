<?php namespace x;

function comment__guard($any) {
    $link_max = 5;
    if (\Request::is('Post')) {
        $error = 0;
        $lot = (array) \Post::get('comment');
        $content = $lot['content'] ?? "";
        $email = $lot['email'] ?? "";
        if ($content) {
            /* if (\preg_match('/[ЁёА-я]{2,}/', $content)) {
                ++$error;
                \Alert::error('Sorry for being racist. You can use regular alphabet to prevent this alert.');
            } else */ if (
                false !== \strpos($content, '://') &&
                \preg_match_all('/\bhttps?:\/\/\S+/', $content, $m)
            ) {
                if (\count($m[0]) > $link_max) {
                    ++$error;
                    \Alert::error('Too many links in the comment.');
                }
            } else {
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
        }
        if ($email) {
            $host = \strtok(\explode('@', $email, 2)[1] ?? "", '.');
            // <https://email-verify.my-addr.com/list-of-most-popular-email-domains.php>
            // <https://email-verify.my-addr.com/top-email-service-providers.php>
            $hosts = [
                'aim' => 1,
                'alice' => 1,
                'aliceadsl' => 1,
                'aol' => 1,
                'arcor' => 1,
                'att' => 1,
                'bellsouth' => 1,
                'bigpond' => 1,
                'bluewin' => 1,
                'blueyonder' => 1,
                'bol' => 1,
                'centurytel' => 1,
                'charter' => 1,
                'chello' => 1,
                'club-internet' => 1,
                'comcast' => 1,
                'cox' => 1,
                'earthlink' => 1,
                'facebook' => 1,
                'free' => 1,
                'freenet' => 1,
                'frontiernet' => 1,
                'gmail' => 1,
                'gmx' => 1,
                'googlemail' => 1,
                'hello' => 1,
                'hetnet' => 1,
                'home' => 1,
                'hotmail' => 1,
                'ig' => 1,
                'juno' => 1,
                'laposte' => 1,
                'libero' => 1,
                'live' => 1,
                'mac' => 1,
                'mail' => 1,
                'me' => 1,
                'msn' => 1,
                'neuf' => 1,
                'ntlworld' => 1,
                'optonline' => 1,
                'optusnet' => 1,
                'orange' => 1,
                'outlook' => 1,
                'planet' => 1,
                'qq' => 1,
                'rambler' => 1,
                'rediff' => 1,
                'rediffmail' => 1,
                'rocketmail' => 1,
                'sbcglobal' => 1,
                'sfr' => 1,
                'shaw' => 1,
                'sky' => 1,
                'skynet' => 1,
                'sympatico' => 1,
                't-online' => 1,
                'telenet' => 1,
                'terra' => 1,
                'tin' => 1,
                'tiscali' => 1,
                'twcc' => 1,
                'uol' => 1,
                'verizon' => 1,
                'virgilio' => 1,
                'voila' => 1,
                'wanadoo' => 1,
                'web' => 1,
                'windstream' => 1,
                'yahoo' => 1,
                'yandex' => 1,
                'ymail' => 1,
                'zonnet' => 1
            ];
            if (!isset($hosts[$host])) {
                ++$error;
                \Alert::error('Blocked email address: %s', ['<mark>' . $email . '</mark>']);
            } else {
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
            unset($lot['token']);
            \Session::set('form.comment', $lot);
            \Guard::kick('/' . $any);
        }
    }
}

\Route::hit('.comment/*', __NAMESPACE__ . "\\comment__guard", 0);