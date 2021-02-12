<?php

declare(strict_types=1);

use Dcrypt\OpensslKey;
use Dcrypt\OpensslStatic;
use Tuupola\Base32;
use Tuupola\Base62Proxy;

require 'vendor/autoload.php';

function _abort(int $code = 403)
{
    http_response_code($code);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    _abort(405);
}

$cipher = $_REQUEST['cipher'] ?? _abort();
$secret = $_REQUEST['secret'] ?? _abort();
$encode = $_REQUEST['encode'] ?? _abort();
$hashal = $_REQUEST['hashal'] ?? _abort();
$encdec = $_REQUEST['encdec'] ?? _abort();
$passwd = $_REQUEST['passwd'] ?? null;

if ($passwd === null || strlen(trim($passwd)) === 0) {
    $passwd = OpensslKey::create(32);
}

header("passwd: $passwd");

$base32 = new Base32([
    "padding" => false
]);

if ($encdec === 'encrypt') {
    try {
        $output = OpensslStatic::encrypt($secret, $passwd, $cipher, $hashal);

        if ($encode === 'base32') {
            echo $base32->encode($output);
        } else if ($encode === 'base62') {
            echo Base62Proxy::encode($output);
        } else if ($encode === 'base64') {
            echo base64_encode($output);
        } else if ($encode === 'hex') {
            echo bin2hex($output);
        } else {
            _abort();
        }
    } catch (Exception $e) {
        _abort(500);
    }
} else if ($encdec === 'decrypt') {
    try {
        $secret = trim($secret);

        if ($encode === 'base32') {
            $output = $base32->decode($secret);
        } else if ($encode === 'base62') {
            $output = Base62Proxy::decode($secret);
        } else if ($encode === 'base64') {
            $output = base64_decode($secret);
        } else if ($encode === 'hex') {
            $output = hex2bin($secret);
        } else {
            _abort();
        }

        echo OpensslStatic::decrypt($output, $passwd, $cipher, $hashal);
    } catch (Exception $e) {
        _abort(500);
    }
} else {
    _abort();
}