<?php

abstract class FilterRequest
{

    static function postRequest($request)
    {
        return htmlspecialchars(strip_tags($_POST[$request]));
    }

    static function getRequest($request)
    {
        return htmlspecialchars(strip_tags($_GET[$request]));
    }
}
