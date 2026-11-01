<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $price
 */
class SideDish extends Model
{
    protected static string $table = 'side_dishes';
    protected static array $columns = ['name', 'price'];

    public function validates(): void
    {
        Validations::notEmpty('name', $this);
        Validations::uniqueness('name', $this);
        Validations::positiveNumber('price', $this);
    }
}
