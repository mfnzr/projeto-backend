<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\Model;
use Core\Database\Database;

/**
 * @property int $id
 * @property int $sale_id
 * @property int $product_id
 * @property int $quantity
 * @property string $amount
 * @property string $weight
 * @property Product $product
 */
class SaleItem extends Model
{
    protected static string $table = 'sale_items';
    protected static array $columns = ['sale_id', 'product_id', 'quantity', 'amount', 'weight'];

    /**
     * @return array{quantity: int, amount: float, weight: float}
     */
    public static function totalsBetween(string $from, string $to): array
    {
        $sql = <<<SQL
            SELECT
                COALESCE(SUM(sale_items.quantity), 0) AS quantity,
                COALESCE(SUM(sale_items.amount), 0) AS amount,
                COALESCE(SUM(sale_items.weight), 0) AS weight
            FROM sale_items
            INNER JOIN sales ON sales.id = sale_items.sale_id
            WHERE DATE(sales.created_at) BETWEEN :from AND :to
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
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
