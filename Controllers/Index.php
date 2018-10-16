<?php
/**
 * Created by PhpStorm.
 * User: Xander
 * Date: 18-9-2018
 * Time: 15:58
 */
namespace Controllers;

class Index extends RootController
{
    public function get()
    {
        $model = new \Models\XanderMvc();
        self::renderView(null, 'test')->assign('mooi', $model->getById(1));
    }
}