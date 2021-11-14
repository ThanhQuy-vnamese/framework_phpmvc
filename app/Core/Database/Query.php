<?php

declare(strict_types=1);


namespace App\Core\Database;


use App\Adapter\ModelAdapter;
use App\Core\Application;
use stdClass;

class Query extends AbstractQuery
{
    protected string $condition = '';

    protected string $limitField = '';

    protected string $table = '';

    protected string $operation = '=';

    public function tableName(): string
    {
        return '';
    }

    public function getDatabase(): Database
    {
        return Application::$APPLICATION->database;
    }

    public function table(string $table): Query
    {
        $this->table = $table;
        return $this;
    }

    public function get(): stdClass
    {
        $limitSelect = $this->limitSelectNew();
        $condition = $this->condition;
        if (!empty($condition)) {
            $condition = "WHERE $condition";
        }
        $tableName = empty(static::tableName()) ? $this->table : static::tableName();
        $query = "SELECT $limitSelect FROM $tableName $condition";

        $result = Application::$APPLICATION->database->mysql->query($query);
        if (!$result) {
            return new stdClass();
        }
        if ($result->num_rows === 0) {
            return new stdClass();
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $modelAdapter = new ModelAdapter($data);
        return $modelAdapter->setDataToAttributes();
    }

    public function insert(array $values)
    {
        $strField = $this->generateFieldToString($values);
        $strValue = $this->generateValuesToString($values);
        $db = $this->getDatabase();
        $query = "INSERT INTO $this->table ($strField) VALUES $strValue;";
        $result = $db->mysql->query($query);
        if (!$result) {
            return false;
        }
        return $db->mysql->insert_id;
    }

    public function update(array $values, array $conditions): bool
    {
        $lastValue = end($values);
        $strUpdateQuery = '';
        foreach ($values as $key => $value) {
            $strUpdateQuery .= $key . ' = ' . (is_int($value) ? $value : "'$value'");
            if ($value != $lastValue) {
                $strUpdateQuery .= ', ';
            }
        }

        $this->condition($conditions);
        $query = "UPDATE $this->table SET $strUpdateQuery WHERE $this->condition";
        $db = $this->getDatabase();
        $result = $db->mysql->query($query);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function delete(): bool
    {
        $db = $this->getDatabase();
        $query = "DELETE FROM $this->table WHERE $this->condition";
        $result = $db->mysql->query($query);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function condition(array $conditions, string $operation = ''): Query
    {
        $condition = '';
        $lastIndex = end($conditions);
        $operation = $this->getOperation($operation);
        foreach ($conditions as $key => $value) {
            if (is_int($value)) {
                $condition .= "$key $operation $value";
            } else {
                $condition .= "$key $operation '$value'";
            }
            if ($value !== $lastIndex) {
                $condition .= ' AND ';
            }
        }

        $this->condition = $condition;

        return $this;
    }

    public function limitSelectNew(): string
    {
        if (empty($this->limitField)) {
            return '*';
        }

        return $this->limitField;
    }

    public function select(array $fields): Query
    {
        $this->limitField = implode(',', $fields);
        return $this;
    }

    protected function getOperation(string $operation): string
    {
        return empty($operation) ? $this->operation : $operation;
    }

    protected function generateValuesToString(array $values): string
    {
        $strValue = '';
        $isMultiValue = false;
        $lastValue = end($values);
        foreach ($values as $value) {
            if (is_array($value)) {
                $isMultiValue = true;
                $str = '';
                $lastValueChild = end($value);
                foreach ($value as $childValue) {
                    $str .= $this->generateValues($childValue, $lastValueChild);
                }
                $strValue .= '(' . $str . ')';
                if ($lastValue != $value) {
                    $strValue .= ', ';
                }
            } else {
                $strValue .= $this->generateValues($value, $lastValue);
            }
        }

        return $isMultiValue ? $strValue : '(' . $strValue . ')';
    }

    protected function generateValues($value, $lastValue): string
    {
        $strValue = '';
        if (is_int($value)) {
            $strValue .= $value;
        } else {
            $strValue .= "'$value'";
        }
        if ($value !== $lastValue) {
            $strValue .= ', ';
        }
        return $strValue;
    }

    protected function generateFieldToString(array $values): string
    {
        $value = array_values($values);
        if (is_array($value[0])) {
            return implode(', ', array_keys($value[0]));
        }
        return implode(', ', array_keys($values));
    }
}