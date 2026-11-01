<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\Model;
use Core\Database\Database;

/**
 * @property int $id
 * @property int $product_id
 * @property string $purchase_date
 * @property int $quantity
 * @property string $total_amount
 * @property string|null $price_per_kg
 * @property Product $product
 */
class Purchase extends Model
{
    protected static string $table = 'purchases';
    protected static array $columns = ['product_id', 'purchase_date', 'quantity', 'total_amount', 'price_per_kg'];

    public function validates(): void
    {
        if (Validations::notEmpty('product_id', $this)) {
            if (Product::findById((int) $this->product_id) === null) {
                $this->addError('product_id', 'produto não encontrado!');
            }
        }

        Validations::date('purchase_date', $this);
        Validations::positiveInteger('quantity', $this);
        Validations::positiveNumber('total_amount', $this);

        if ($this->price_per_kg !== null) {
            Validations::positiveNumber('price_per_kg', $this);
        }
    }

    public function __set(string $property, mixed $value): void
    {
        if ($property === 'price_per_kg' && $value === '') {
            $value = null;
        }

        parent::__set($property, $value);
    }

    public function weight(): ?float
    {
        if ($this->price_per_kg === null) {
            return null;
        }

        return (float) $this->total_amount / (float) $this->price_per_kg;
    }

    /**
     * @return array{quantity: int, amount: float, weight: float, amount_with_weight: float}
     */
    public static function totalsBetween(string $from, string $to): array
    {
        $sql = <<<SQL
            SELECT
                COALESCE(SUM(quantity), 0) AS quantity,
                COALESCE(SUM(total_amount), 0) AS amount,
                COALESCE(SUM(total_amount / price_per_kg), 0) AS weight,
                COALESCE(SUM(CASE WHEN price_per_kg IS NULL THEN 0 ELSE total_amount END), 0) AS amount_with_weight
            FROM purchases
            WHERE purchase_date BETWEEN :from AND :to
        SQL;

        $stmt = Database::getDatabaseConn()->prepare($sql);
        $stmt->bindValue(':from', $from);
        $stmt->bindValue(':to', $to);
        $stmt->execute();
        $row = $stmt->fetch();

        return [
            'quantity' => (int) $row['quantity'],
            'amount' => (float) $row['amount'],
            'weight' => (float) $row['weight'],
            'amount_with_weight' => (float) $row['amount_with_weight'],
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
