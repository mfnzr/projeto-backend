<?php

namespace Tests\Acceptance\Reports;

use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\Sale;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class ReportsCest extends BaseAcceptanceCest
{
    public function adminSeesTheMarginReport(AcceptanceTester $page): void
    {
        $product = new Product(['name' => 'Frango assado']);
        $product->save();

        $production = new Production([
            'product_id' => $product->id,
            'production_date' => date('Y-m-d'),
            'quantity' => '20',
            'price_per_kg' => '50.00'
        ]);
        $production->save();

        $sale = new Sale(['payment_method' => 'cash', 'created_at' => date('Y-m-d H:i:s')]);
        $sale->setItems([$product->id => ['quantity' => 4, 'amount' => '300.00']]);
        $sale->save();

        $purchase = new Purchase([
            'product_id' => $product->id,
            'purchase_date' => date('Y-m-d'),
            'quantity' => '10',
            'total_amount' => '200.00',
            'price_per_kg' => '20.00'
        ]);
        $purchase->save();

        $page->login('admin@example.com', '123456');
        $page->click('Relatório de margem');

        $page->see('Saídas (vendas)');
        $page->see('R$ 300,00');
        $page->see('Entradas (compras)');
        $page->see('R$ 200,00');
        $page->see('Total: R$ 100,00');
    }

    public function attendantCannotSeeTheReport(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/admin/reports');

        $page->dontSeeInCurrentUrl('/admin/reports');
        $page->see('Você deve ser administrador para acessar essa página');
    }
}
