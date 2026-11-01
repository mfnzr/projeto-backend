<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int|null $order_id
 * @property string $payment_method
 * @property string $created_at
 * @property Order $order
 * @property SaleItem[] $items
 */
class Sale extends Model
{
    protected static string $table = 'sales';
    protected static array $columns = ['order_id', 'payment_method', 'created_at'];

    public const array PAYMENT_METHODS = [
        'cash' => 'Dinheiro',
        'pix' => 'Pix',
        'card' => 'Cartão',
    ];

    /** @var array<int, array{quantity: mixed, amount: mixed}> */
    protected array $lines = [];

    public function validates(): void
    {
        if (!array_key_exists((string) $this->payment_method, self::PAYMENT_METHODS)) {
            $this->addError('payment_method', 'escolha a forma de pagamento!');
        }

        if (count($this->lines) === 0) {
            $this->addError('items', 'escolha pelo menos um produto!');
        }

        foreach ($this->lines as $productId => $line) {
            $this->validatesLine($productId, $line['quantity'], $line['amount']);
        }
    }

    private function validatesLine(int $productId, mixed $quantity, mixed $amount): void
    {
        $errorKey = 'item_' . $productId;

        if ($this->productionOf($productId) === null) {
            $this->addError($errorKey, 'esse produto não foi produzido no dia da venda!');
            return;
        }

        if (filter_var($quantity, FILTER_VALIDATE_INT) === false || $quantity < 1) {
            $this->addError($errorKey, 'a quantidade deve ser um número inteiro maior que zero!');
            return;
        }

        if (!is_numeric($amount) || $amount <= 0) {
            $this->addError($errorKey, 'o valor da balança deve ser maior que zero!');
            return;
        }

        if ($this->newRecord() && $this->order_id === null) {
            $available = $this->productionOf($productId)->available();

            if ($quantity > $available) {
                $this->addError($errorKey, 'só tem ' . $available . ' disponível!');
            }
        }
    }

    /**
     * @param array<int|string, array<string, mixed>> $items
     */
    public function setItems(array $items): void
    {
        $this->lines = [];

        foreach ($items as $productId => $item) {
            $quantity = $item['quantity'] ?? '';
            $amount = $item['amount'] ?? '';

            if ($quantity !== '' && $quantity !== '0') {
                $this->lines[(int) $productId] = ['quantity' => $quantity, 'amount' => $amount];
            }
        }
    }

    public function loadItems(): void
    {
        $this->lines = [];

        foreach ($this->items as $item) {
            $this->lines[$item->product_id] = ['quantity' => $item->quantity, 'amount' => $item->amount];
        }
    }

    /**
     * @param array<int|string, array<string, mixed>> $items
     */
    public function setAmounts(array $items): void
    {
        foreach ($this->lines as $productId => $line) {
            $amount = '';

            if (isset($items[$productId]['amount'])) {
                $amount = $items[$productId]['amount'];
            }

            $this->lines[$productId]['amount'] = $amount;
        }
    }

    /**
     * @return array<int, array{quantity: mixed, amount: mixed}>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    public function quantityOf(int $productId): mixed
    {
        if (array_key_exists($productId, $this->lines)) {
            return $this->lines[$productId]['quantity'];
        }

        return '';
    }

    public function amountOf(int $productId): mixed
    {
        if (array_key_exists($productId, $this->lines)) {
            return $this->lines[$productId]['amount'];
        }

        return '';
    }

    public function __set(string $property, mixed $value): void
    {
        if ($property === 'order_id' && $value === '') {
            $value = null;
        }

        parent::__set($property, $value);
    }

    public function saleDate(): string
    {
        return substr((string) $this->created_at, 0, 10);
    }

    public function productionOf(int $productId): ?Production
    {
        return Production::onDate($productId, $this->saleDate());
    }

    public function save(): bool
    {
        if (!parent::save()) {
            return false;
        }

        foreach ($this->items as $item) {
            $item->destroy();
        }

        foreach ($this->lines as $productId => $line) {
            $pricePerKg = (float) $this->productionOf($productId)->price_per_kg;
            $weight = (float) $line['amount'] / $pricePerKg;

            $item = new SaleItem([
                'sale_id' => $this->id,
                'product_id' => $productId,
                'quantity' => $line['quantity'],
                'amount' => $line['amount'],
                'weight' => round($weight, 3),
            ]);
            $item->save();
        }

        return true;
    }

    public function total(): float
    {
        $total = 0.0;

        foreach ($this->items as $item) {
            $total = $total + (float) $item->amount;
        }

        return $total;
    }

    public function paymentMethodName(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method];
    }

    public function isFromOrder(): bool
    {
        return $this->order_id !== null;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }
}
