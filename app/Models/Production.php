<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\Model;
use Core\Database\Database;

/**
 * @property int $id
 * @property int $product_id
 * @property string $production_date
 * @property int $quantity
 * @property string $price_per_kg
 * @property Product $product
 */
class Production extends Model
{
    protected static string $table = 'productions';
    protected static array $columns = ['product_id', 'production_date', 'quantity', 'price_per_kg'];

    public function validates(): void
    {
        if (Validations::notEmpty('product_id', $this)) {
            if (Product::findById((int) $this->product_id) === null) {
                $this->addError('product_id', 'produto não encontrado!');
            }
        }

        Validations::date('production_date', $this);
        Validations::positiveInteger('quantity', $this);
        Validations::positiveNumber('price_per_kg', $this);

        if (!$this->hasErrors()) {
            $this->validatesOneProductionPerDay();
        }
    }

    private function validatesOneProductionPerDay(): void
    {
        $productions = Production::where([
            'product_id' => $this->product_id,
            'production_date' => $this->production_date
        ]);

        foreach ($productions as $production) {
            if ($production->id !== $this->id) {
                $this->addError('production_date', 'já existe produção desse produto nessa data!');
            }
        }
    }

    public static function onDate(int $productId, string $date): ?Production
    {
        return Production::findBy(['product_id' => $productId, 'production_date' => $date]);
    }

    public static function today(int $productId): ?Production
    {
        return Production::onDate($productId, date('Y-m-d'));
    }

    /**
     * @return array<Production>
     */
    public static function allToday(): array
    {
        return Production::where(['production_date' => date('Y-m-d')]);
    }

    public function available(): int
    {
        return $this->quantity - $this->orderedUnits() - $this->soldUnitsWithoutOrder();
    }

    private function orderedUnits(): int
    {
        $sql = <<<SQL
            SELECT COALESCE(SUM(order_items.quantity), 0)
            FROM order_items
            INNER JOIN orders ON orders.id = order_items.order_id
            WHERE order_items.product_id = :product_id
            AND orders.status <> 'cancelled'
            AND DATE(orders.created_at) = :date
        SQL;

        $stmt = Database::getDatabaseConn()->prepare($sql);
        $stmt->bindValue(':product_id', $this->product_id);
        $stmt->bindValue(':date', $this->production_date);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function soldUnitsWithoutOrder(): int
    {
        $sql = <<<SQL
            SELECT COALESCE(SUM(sale_items.quantity), 0)
            FROM sale_items
            INNER JOIN sales ON sales.id = sale_items.sale_id
            WHERE sale_items.product_id = :product_id
            AND sales.order_id IS NULL
            AND DATE(sales.created_at) = :date
        SQL;

        $stmt = Database::getDatabaseConn()->prepare($sql);
        $stmt->bindValue(':product_id', $this->product_id);
        $stmt->bindValue(':date', $this->production_date);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
