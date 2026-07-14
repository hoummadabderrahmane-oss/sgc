<?php

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

}

function csrf_token()
{
    return $_SESSION['csrf_token'];
}

function csrf_verify($token)
{
    return hash_equals($_SESSION['csrf_token'], $token);
}