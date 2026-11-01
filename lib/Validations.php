<?php

namespace Lib;

use Core\Database\Database;
use DateTime;

class Validations
{
    public static function notEmpty($attribute, $obj)
    {
        if ($obj->$attribute === null || $obj->$attribute === '') {
            $obj->addError($attribute, 'não pode ser vazio!');
            return false;
        }

        return true;
    }

    public static function email($attribute, $obj)
    {
        if (filter_var($obj->$attribute, FILTER_VALIDATE_EMAIL) === false) {
            $obj->addError($attribute, 'deve ser um e-mail válido!');
            return false;
        }

        return true;
    }

    public static function positiveInteger($attribute, $obj)
    {
        $value = filter_var($obj->$attribute, FILTER_VALIDATE_INT);

        if ($value === false || $value < 1) {
            $obj->addError($attribute, 'deve ser um número inteiro maior que zero!');
            return false;
        }

        return true;
    }

    public static function positiveNumber($attribute, $obj)
    {
        if (!is_numeric($obj->$attribute) || $obj->$attribute <= 0) {
            $obj->addError($attribute, 'deve ser um valor maior que zero!');
            return false;
        }

        return true;
    }

    public static function date($attribute, $obj)
    {
        $value = (string) $obj->$attribute;
        $date = DateTime::createFromFormat('Y-m-d', $value);

        if ($date === false || $date->format('Y-m-d') !== $value) {
            $obj->addError($attribute, 'deve ser uma data válida!');
            return false;
        }

        return true;
    }

    public static function passwordConfirmation($obj)
    {
        if ($obj->password !== $obj->password_confirmation) {
            $obj->addError('password', 'as senhas devem ser idênticas!');
            return false;
        }

        return true;
    }

    public static function uniqueness($fields, $object)
    {
        $dbFieldsValues = [];
        $objFieldValues = [];

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        if (!$object->newRecord()) {
            $dbObject = $object::findById($object->id);

            foreach ($fields as $field) {
                $dbFieldsValues[] = $dbObject->$field;
                $objFieldValues[] = $object->$field;
            }

            if (
                !empty($dbFieldsValues) &&
                !empty($objFieldValues) &&
                $dbFieldsValues === $objFieldValues
            ) {
                return true;
            }
        }

        $table = $object::table();
        $conditions = implode(' AND ', array_map(fn($field) => "{$field} = :{$field}", $fields));

        $sql = <<<SQL
            SELECT id FROM {$table} WHERE {$conditions};
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);

        foreach ($fields as $field) {
            $stmt->bindValue($field, $object->$field);
        }

        $stmt->execute();

        if ($stmt->rowCount() !== 0) {
            foreach ($fields as $field) {
                $object->addError($field, 'já existe um registro com esse dado');
            }
            return false;
        }

        return true;
    }
}
