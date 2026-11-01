<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\SaleItem;
use DateTime;
use Lib\FlashMessage;
use Core\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request): void
    {
        $title = 'Relatório de margem';
        $from = $request->getParam('from', date('Y-m-01'));
        $to = $request->getParam('to', date('Y-m-d'));

        if (!$this->isValidDate($from) || !$this->isValidDate($to)) {
            FlashMessage::danger('Informe datas válidas');
            $from = date('Y-m-01');
            $to = date('Y-m-d');
        } elseif ($from > $to) {
            FlashMessage::danger('A data inicial deve ser antes da data final');
            $from = date('Y-m-01');
            $to = date('Y-m-d');
        }

        $sales = SaleItem::totalsBetween($from, $to);
        $purchases = Purchase::totalsBetween($from, $to);

        $margin = $sales['amount'] - $purchases['amount'];

        $marginPerUnit = null;
        if ($sales['quantity'] > 0 && $purchases['quantity'] > 0) {
            $salePricePerUnit = $sales['amount'] / $sales['quantity'];
            $purchasePricePerUnit = $purchases['amount'] / $purchases['quantity'];
            $marginPerUnit = $salePricePerUnit - $purchasePricePerUnit;
        }

        $marginPerKg = null;
        if ($sales['weight'] > 0 && $purchases['weight'] > 0) {
            $salePricePerKg = $sales['amount'] / $sales['weight'];
            $purchasePricePerKg = $purchases['amount_with_weight'] / $purchases['weight'];
            $marginPerKg = $salePricePerKg - $purchasePricePerKg;
        }

        $this->render(
            'reports/index',
            compact('title', 'from', 'to', 'sales', 'purchases', 'margin', 'marginPerUnit', 'marginPerKg')
        );
    }

    private function isValidDate(string $value): bool
    {
        $date = DateTime::createFromFormat('Y-m-d', $value);

        if ($date === false) {
            return false;
        }

        return $date->format('Y-m-d') === $value;
    }
}
