<?php
/**
 * Created by PhpStorm.
 * User: Xander
 * Date: 2-10-2018
 * Time: 20:21
 */

namespace Core;


class Model extends AbstractModel
{
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    public function setFieldPrefix($fieldPrefix)
    {
        $this->fieldPrefix = $fieldPrefix;
        return $this;
    }

}