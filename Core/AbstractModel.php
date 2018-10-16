<?php
/**
 * Created by PhpStorm.
 * User: Xander
 * Date: 10-10-2018
 * Time: 16:32
 */

namespace Core;

use Aura\Sql\ExtendedPdo;

abstract class AbstractModel
{
    protected $pdo;

    protected $table;

    protected $fieldPrefix = null;

    public function __construct()
    {
        $this->pdo = new ExtendedPdo(
                'mysql:host=localhost;dbname=test',
                '',
                '');
    }

    public function getById(int $id): array
    {
        $query = 'SELECT `' . $this->table . '`.* FROM `' . $this->table . '` WHERE `' . $this->table . '`.`' . $this->fieldPrefix . 'id` = :id';
        $bind = ['id' => $id];

        $line = $this->pdo->fetchOne($query, $bind);

        return $line === false ? [] : $this->removePrefixFromFields($line);

    }

    public function get(string $where = null, array $bind = [], $onlyFirstRow = false): array
    {
        $query = 'SELECT `' . $this->table . '`.* FROM `' . $this->table . '` ' . (!empty($where) ? 'WHERE ' . $where : null);

        if ($onlyFirstRow && ($line = $this->pdo->fetchOne($query, $bind))) {
            $data = $this->removePrefixFromFields($line);
        } elseif (!$onlyFirstRow && ($data = $this->pdo->fetchAll($query, $bind))) {
            if (is_array($data)) {
                foreach ($data as $key => $line) {
                    $data[$key] = $this->removePrefixFromFields($line);
                }
            }
        } else {
            $data = [];
        }

        return $data;
    }
    public function insert(array $fields = [], $withIgnore = false): ?int
    {
        $this->checkSetup();

        if (empty($fields)) {
            $fields = ['id' => null];
        }

        $fieldsWithPrefix = $this->addPrefixToFields($fields);
        $fieldNames = array_keys($fieldsWithPrefix);

        $query = 'INSERT ' . ($withIgnore ? 'IGNORE ' : null) . 'INTO `' . $this->table . '` (`' . implode('`, `', $fieldNames) . '`) VALUES (:' . implode(', :', $fieldNames) . ')';
        $bind = $fieldsWithPrefix;

        $return = $this->pdo->perform($query, $bind) === false ? null : ($fields['id'] ?? $this->pdo->lastInsertId());

        return $return;
    }

    public function update($ids, array $fields = []): bool
    {
        if (!is_int($ids) && !(is_array($ids) && ctype_digit((string)implode('', $ids)))) {
            throw new \Exception('The $ids param of ' . get_class($this) . '::update() only supports an integer as ID or an array of integers.');
        } elseif (is_array($ids) && count($ids) > 1 && isset($fields['id'])) {
            throw new \Exception('Cant set multple ID\'s to the same value with  ' . get_class($this) . '::update()');
        }

        $this->checkSetup();

        if (empty($fields)) {
            return true;
        } else {
            $fieldsWithPrefix = $this->addPrefixToFields($fields);
            $fieldNames = array_keys($fieldsWithPrefix);

            $query = 'UPDATE `' . $this->table . '` SET ';
            $fieldsQuery = [];
            foreach ($fieldNames as $fieldName) {
                $fieldsQuery[] = '`' . $fieldName . '` = :' . $fieldName;
            }
            $query .= implode(', ', $fieldsQuery) . ' WHERE `' . $this->fieldPrefix . 'id` ' . (is_array($ids) ? 'IN (:id)' : '= :id');
            $bind = $fieldsWithPrefix;
            $bind['id'] = $ids;

            $return = $this->pdo->perform($query, $bind) === false ? false : true;

            return $return;
        }
    }

    public function delete($ids): bool
    {
        if (!is_int($ids) && !(is_array($ids) && ctype_digit((string)implode('', $ids)))) {
            throw new \Exception('The $ids param of ' . get_class($this) . '::delete() only supports an integer as ID or an array of integers.');
        }

        $this->checkSetup();

        $query = 'DELETE FROM `' . $this->table . '` WHERE `' . $this->table . '`.`' . $this->fieldPrefix . 'id` ' . (is_array($ids) ? 'IN (:id)' : '= :id');
        $bind = ['id' => $ids];

        $return = $this->pdo->perform($query, $bind) === false ? false : true;

        return $return;
    }

    public function distinct($field, string $where = null, array $bind = [])
    {
        $query = 'SELECT DISTINCT `' . $this->table . '`.`' . $this->fieldPrefix . $field . '` FROM `' . $this->table . '`  ' . (preg_match('/=| IS |<>|EXISTS| IN /', $where) ? 'WHERE ' . $where : $where);
        $return = $this->pdo->fetchCol($query, $bind);

        return $return;
    }

    protected function checkSetup()
    {
        if (empty($this->table)) {
            throw new \Exception('Class ' . get_class($this) . ' needs to have the property \'table\' set correctly.');
        }
    }

    protected function addPrefixToFields(array $fields = [])
    {
        if ($this->fieldPrefix && !empty($fields)) {
            return array_combine(array_map(function ($key) {
                return $this->fieldPrefix . $key;
            }, array_keys($fields)), $fields);
        } else {
            return $fields;
        }
    }

    protected function removePrefixFromFields($fields = [])
    {
        if ($this->fieldPrefix && !empty($fields)) {
            return array_combine(
                array_map(function ($key) {
                    return strpos($key, $this->fieldPrefix) === 0 ? substr($key, strlen($this->fieldPrefix)) : $key;
                }, array_keys($fields)),
                $fields
            );
        } else {
            return $fields;
        }
    }

    public function getTable()
    {
        return (string)$this->table;
    }


}