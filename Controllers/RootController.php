<?php

namespace Controllers;

class RootController extends \Core\Controller
{
    public static function renderView($template, $view)
    {
        return new \Core\View($template ?? 'indexTemplate', $view);
    }
}