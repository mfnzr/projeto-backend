<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int $customer_id
 * @property string $status
 * @property string $created_at
 * @property Customer $customer
 * @property OrderItem[] $items
 * @property Sale[] $sales
 */
class Order extends Model
{
    protected static string $table = 'orders';
    protected static array $columns = ['customer_id', 'status', 'created_at'];

    public const array STATUSES = [
        'ordered' => 'Encomendado',
        'picked_up' => 'Retirado',
        'cancelled' => 'Cancelado',
    ];

    /** @var array<int, mixed> */
    protected array $quantities = [];

    public function validates(): void
    {
        if (Validations::notEmpty('customer_id', $this)) {
            if (Customer::findById((int) $this->customer_id) === null) {
                $this->addError('customer_id', 'cliente não encontrado!');
            }
        }

        if (!array_key_exists($this->status, self::STATUSES)) {
            $this->addError('status', 'status inválido!');
        }

        $this->validatesQuantities();
    }

    private function validatesQuantities(): void
    {
        if (count($this->quantities) === 0) {
            $this->addError('items', 'escolha pelo menos um produto!');
            return;
        }

        foreach ($this->quantities as $productId => $quantity) {
            $errorKey = 'item_' . $productId;
            $production = Production::today($productId);

            if ($production === null) {
                $this->addError($errorKey, 'esse produto não foi produzido hoje!');
            } elseif (filter_var($quantity, FILTER_VALIDATE_INT) === false || $quantity < 1) {
                $this->addError($errorKey, 'deve ser um número inteiro maior que zero!');
            } else {
                $available = $production->available() + $this->savedQuantity($productId);

                if ($quantity > $available) {
                    $this->addError($errorKey, 'só tem ' . $available . ' disponível!');
                }
            }
        }
    }

    /**
     * @param array<int|string, mixed> $quantities
     */
    public function setQuantities(array $quantities): void
    {
        $this->quantities = [];

        foreach ($quantities as $productId => $quantity) {
            if ($quantity !== '' && $quantity !== '0' && $quantity !== null) {
                $this->quantities[(int) $productId] = $quantity;
            }
        }
    }

    public function loadQuantities(): void
    {
        $this->quantities = [];

        foreach ($this->items as $item) {
            $this->quantities[$item->product_id] = $item->quantity;
        }
    }

    public function quantityOf(int $productId): mixed
    {
        if (array_key_exists($productId, $this->quantities)) {
            return $this->quantities[$productId];
        }

        return '';
    }

    public function savedQuantity(int $productId): int
    {
        if ($this->newRecord()) {
            return 0;
        }

        $total = 0;
        foreach ($this->items as $item) {
            if ($item->product_id === $productId) {
                $total = $total + $item->quantity;
            }
        }

        return $total;
    }

    public function save(): bool
    {
        if (!parent::save()) {
            return false;
        }

        foreach ($this->items as $item) {
            $item->destroy();
        }

        foreach ($this->quantities as $productId => $quantity) {
            $item = new OrderItem([
                'order_id' => $this->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
            $item->save();
        }

        return true;
    }

    public function totalUnits(): int
    {
        $total = 0;

        foreach ($this->items as $item) {
            $total = $total + $item->quantity;
        }

        return $total;
    }

    public function statusName(): string
    {
        return self::STATUSES[$this->status];
    }

    public function isOrdered(): bool
    {
        return $this->status === 'ordered';
    }

    public function isPickedUp(): bool
    {
        return $this->status === 'picked_up';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'order_id');
    }
}
