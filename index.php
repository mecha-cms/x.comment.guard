<?php namespace x;

// Disable this extension if `comment` extension is disabled or removed ;)
if (!isset($state->x->comment)) {
    return;
}

function comment__guard($content, $path, $query, $hash) {
    if ('POST' !== $_SERVER['REQUEST_METHOD']) {
        return $content;
    }
    $error = 0;
    $lot = (array) ($_POST['comment'] ?? []);
    $author = $lot['author'] ?? "";
    $content = $lot['content'] ?? "";
    $email = $lot['email'] ?? "";
    $ip = \ip();
    $link = $lot['link'] ?? "";
    if ($author) {
        $author = \strip_tags($author);
        foreach (\stream(__DIR__ . \D . 'author.txt') as $v) {
            if ("" === ($v = \trim($v))) {
                continue;
            }
            // Check for exact value if the data is surrounded by double quote character(s)
            if (0 === \strpos($v, '"') && '"' === \substr($v, -1)) {
                if ($author === \substr(\strtr($v, ["\\\"" => '"']), 1, -1)) {
                    \class_exists("\\Alert") && \Alert::error('Blocked author name: %s', ['<mark>' . \htmlspecialchars($author) . '</mark>']);
                    ++$error;
                    break;
                }
            }
            // Check if author name contains the data as part of it
            if (false !== \stripos($author, $v)) {
                \class_exists("\\Alert") && \Alert::error('Please use other words: %s', ['<mark>' . \htmlspecialchars($v) . '</mark>']);
                ++$error;
                break;
            }
        }
    }
    if ($content) {
        $content = \strip_tags($content);
        foreach (\stream(__DIR__ . \D . 'content.txt') as $v) {
            if ("" === ($v = \trim($v))) {
                continue;
            }
            // Check for exact value if the data is surrounded by double quote character(s)
            if (0 === \strpos($v, '"') && '"' === \substr($v, -1)) {
                if ($content === \substr(\strtr($v, ["\\\"" => '"']), 1, -1)) {
                    \class_exists("\\Alert") && \Alert::error('Blocked comment content: %s', ['<mark>' . \htmlspecialchars($content) . '</mark>']);
                    ++$error;
                    break;
                }
            }
            // Check if comment content contains the data as part of it
            if (false !== \stripos($content, $v)) {
                \class_exists("\\Alert") && \Alert::error('Please use other words: %s', ['<mark>' . \htmlspecialchars($v) . '</mark>']);
                ++$error;
                break;
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
            \class_exists("\\Alert") && \Alert::error('Blocked email address: %s', ['<mark>' . \htmlspecialchars($email) . '</mark>']);
            ++$error;
        } else {
            foreach (\stream(__DIR__ . \D . 'email.txt') as $v) {
                if ("" === ($v = \trim($v))) {
                    continue;
                }
                if ($v === $email) {
                    \class_exists("\\Alert") && \Alert::error('Blocked email address: %s', ['<mark>' . \htmlspecialchars($v) . '</mark>']);
                    ++$error;
                    break;
                }
            }
        }
    }
    if ($ip) {
        foreach (\stream(__DIR__ . \D . 'ip.txt') as $v) {
            if ("" === ($v = \trim($v))) {
                continue;
            }
            if ($v === $ip) {
                \class_exists("\\Alert") && \Alert::error('Blocked internet protocol address: %s', ['<mark>' . \htmlspecialchars($v) . '</mark>']);
                ++$error;
                break;
            }
        }
    }
    // Make sure link value is in the form of base URL
    if ($link) {
        if (
            // `http://example.com?a=b`
            false !== \strpos($link, '?') ||
            // `http://example.com&a=b`
            false !== \strpos($link, '&') ||
            // `http://example.com#a`
            false !== \strpos($link, '#') ||
            // `http://example.com/a`
            \substr_count(\rtrim($link, '/'), '/') > 2
        ) {
            \class_exists("\\Alert") && \Alert::error('Link must be in the form of base URL. Links that point to a specific page is not allowed.');
            ++$error;
        }
        foreach (\stream(__DIR__ . \D . 'link.txt') as $v) {
            if ("" === ($v = \trim($v))) {
                continue;
            }
            if ($v === $link) {
                \class_exists("\\Alert") && \Alert::error('Blocked link address: %s', ['<mark>' . \htmlspecialchars($v) . '</mark>']);
                ++$error;
                break;
            }
        }
    }
    if ($error > 0) {
        unset($lot['token']);
        $_SESSION['form']['comment'] = $lot;
        \kick($path . $query . ($hash ?? '#comment'));
    }
    return $content;
}

\Hook::set('route.comment', __NAMESPACE__ . "\\comment__guard", 0);