<?php
/**
 * Created by PhpStorm.
 * User: Xander
 * Date: 20-9-2018
 * Time: 10:38
 */

namespace Core;

class View
{
    private $data = array();

    private $render = FALSE;

    private $template = FALSE;

    public function __construct($template, $view)
    {
        try {
            $template = 'Templates/' . $template . '.phtml';
            $file = 'Views/' . strtolower($view) . '.phtml';

            if (file_exists($template)) {
                $this->template = $template;
            } else {
                throw new \Exception('Template ' . $template . ' not found!');
            }

            if (file_exists($file)) {
                $this->render = $file;
            } else {
                throw new \Exception('View ' . $view . ' not found!');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function assign($variable, $value)
    {
        $this->data[$variable] = $value;
    }

    public function __destruct()
    {
        extract($this->data);

        if ($this->render) {
            ob_start();
            include($this->render);
            $contentFile = ob_get_clean();
        }
        if ($this->template)
            include $this->template;


    }
}