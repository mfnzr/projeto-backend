<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 */
class Product extends Model
{
    protected static string $table = 'products';
    protected static array $columns = ['name', 'description'];

    public function validates(): void
    {
        Validations::notEmpty('name', $this);
        Validations::uniqueness('name', $this);
    }
}
