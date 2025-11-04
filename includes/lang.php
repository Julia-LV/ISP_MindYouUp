<?php
// Simple language loader and helper
// Usage: include/require this early (e.g. in includes/header.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$available_langs = ['en', 'pt'];

// Determine language priority: explicit GET/POST param (handled elsewhere), cookie, session, Accept-Language, default
$lang = 'en';
if (!empty($_COOKIE['site_lang']) && in_array($_COOKIE['site_lang'], $available_langs)) {
    $lang = $_COOKIE['site_lang'];
} elseif (!empty($_SESSION['site_lang']) && in_array($_SESSION['site_lang'], $available_langs)) {
    $lang = $_SESSION['site_lang'];
} else {
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $al = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (in_array($al, $available_langs)) {
            $lang = $al;
        }
    }
}

$TRANSLATIONS = [
    'en' => [
        'title' => 'My Website',
        'choose_language' => 'Choose language',
        'english' => 'English',
        'portuguese' => 'Portuguese',
        'saved' => 'Language saved.',
        'welcome' => 'Welcome to the site!'
    ],
    'pt' => [
        'title' => 'Meu Site',
        'choose_language' => 'Escolha o idioma',
        'english' => 'Inglês',
        'portuguese' => 'Português',
        'saved' => 'Idioma salvo.',
        'welcome' => 'Bem-vindo ao site!'
    ]
];

function __($key)
{
    global $TRANSLATIONS, $lang;
    if (isset($TRANSLATIONS[$lang][$key])) {
        return $TRANSLATIONS[$lang][$key];
    }
    if (isset($TRANSLATIONS['en'][$key])) {
        return $TRANSLATIONS['en'][$key];
    }
    return $key;
}

// Helper to set language (used by the selector page)
function set_language($newLang)
{
    global $available_langs;
    if (!in_array($newLang, $available_langs)) {
        $newLang = 'en';
    }
    // set cookie + session
    setcookie('site_lang', $newLang, time() + 60 * 60 * 24 * 30, '/');
    $_SESSION['site_lang'] = $newLang;
}

?>
