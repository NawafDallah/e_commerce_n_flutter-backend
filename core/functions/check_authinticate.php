<?php


function checkAuthenticate()
{
    if (isset($_SERVER['PHP_AUTH_USER'])  && isset($_SERVER['PHP_AUTH_PW'])) {

        if (
            $_SERVER['PHP_AUTH_USER'] != "nawaf" ||
            $_SERVER['PHP_AUTH_PW'] != "123456"
        ) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Page Not Found';
            exit;
        }
    } else {
        exit;
    }
}
