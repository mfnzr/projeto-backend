<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property Production[] $productions
 * @property Purchase[] $purchases
 * @property OrderItem[] $orderItems
 * @property SaleItem[] $saleItems
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

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class, 'product_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'product_id');
    }

    public function hasMovements(): bool
    {
        if (count($this->productions) > 0) {
            return true;
        }

        if (count($this->purchases) > 0) {
            return true;
        }

        if (count($this->orderItems) > 0) {
            return true;
        }

        if (count($this->saleItems) > 0) {
            return true;
        }

        return false;
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'product_id');
    }
}
