<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property int $side_dish_id
 * @property Order $order
 * @property SideDish $sideDish
 */
class OrderSideDish extends Model
{
    protected static string $table = 'order_side_dishes';
    protected static array $columns = ['order_id', 'side_dish_id'];

    public function validates(): void
    {
        if (Validations::notEmpty('order_id', $this)) {
            if (Order::findById((int) $this->order_id) === null) {
                $this->addError('order_id', 'pedido não encontrado!');
            }
        }

        if (Validations::notEmpty('side_dish_id', $this)) {
            if (SideDish::findById((int) $this->side_dish_id) === null) {
                $this->addError('side_dish_id', 'acompanhamento não encontrado!');
            }
        }

        $conditions = ['order_id' => $this->order_id, 'side_dish_id' => $this->side_dish_id];
        if (!$this->hasErrors() && self::exists($conditions)) {
            $this->addError('side_dish_id', 'esse acompanhamento já está no pedido!');
        }
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function sideDish(): BelongsTo
    {
        return $this->belongsTo(SideDish::class, 'side_dish_id');
    }
}
