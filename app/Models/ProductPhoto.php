<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int $product_id
 * @property string $file_name
 * @property Product $product
 */
class ProductPhoto extends Model
{
    protected static string $table = 'product_photos';
    protected static array $columns = ['product_id', 'file_name'];

    public function validates(): void
    {
        if (Validations::notEmpty('product_id', $this)) {
            if (Product::findById((int) $this->product_id) === null) {
                $this->addError('product_id', 'produto não encontrado!');
            }
        }

        Validations::notEmpty('file_name', $this);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function url(): string
    {
        return '/assets/uploads/' . $this->file_name;
    }
}
