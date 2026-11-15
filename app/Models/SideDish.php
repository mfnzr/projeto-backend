<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\BelongsToMany;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $price
 * @property Order[] $orders
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

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_side_dishes', 'side_dish_id', 'order_id');
    }

    public function hasOrders(): bool
    {
        return OrderSideDish::exists(['side_dish_id' => $this->id]);
    }
}
