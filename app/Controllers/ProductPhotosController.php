<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\ProductPhoto;
use App\Services\ProductPhotoUpload;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;

class ProductPhotosController extends Controller
{
    public function create(Request $request): void
    {
        $product = Product::findById($request->getParam('product_id'));

        if ($product === null) {
            FlashMessage::danger('Produto não encontrado');
            $this->redirectTo(route('products.index'));
            return;
        }

        $photo = new ProductPhoto(['product_id' => $product->id]);
        $upload = new ProductPhotoUpload($photo);

        $file = [];
        if (isset($_FILES['photo'])) {
            $file = $_FILES['photo'];
        }

        if ($upload->save($file)) {
            FlashMessage::success('Foto enviada com sucesso!');
        } else {
            FlashMessage::danger('Não foi possível enviar a foto: ' . $photo->errors('file'));
        }

        $this->redirectTo(route('products.show', ['id' => $product->id]));
    }

    public function destroy(Request $request): void
    {
        $product = Product::findById($request->getParam('product_id'));

        if ($product === null) {
            FlashMessage::danger('Produto não encontrado');
            $this->redirectTo(route('products.index'));
            return;
        }

        $photo = ProductPhoto::findBy(['id' => $request->getParam('id'), 'product_id' => $product->id]);

        if ($photo === null) {
            FlashMessage::danger('Foto não encontrada');
        } else {
            $upload = new ProductPhotoUpload($photo);
            $upload->remove();
            FlashMessage::success('Foto excluída com sucesso!');
        }

        $this->redirectTo(route('products.show', ['id' => $product->id]));
    }
}
