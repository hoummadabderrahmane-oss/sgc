<?php

function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header("Location: $url");
    exit();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}