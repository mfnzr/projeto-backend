<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property Order[] $orders
 */
class Customer extends Model
{
    protected static string $table = 'customers';
    protected static array $columns = ['name', 'phone'];

    public function validates(): void
    {
        Validations::notEmpty('name', $this);

        if (Validations::notEmpty('phone', $this)) {
            if (!preg_match('/^[0-9]{10,11}$/', $this->phone)) {
                $this->addError('phone', 'deve ter 10 ou 11 números, com DDD!');
            }
        }
        Validations::uniqueness('phone', $this);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function hasOrders(): bool
    {
        return count($this->orders) > 0;
    }
}
