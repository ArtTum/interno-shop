<?php

namespace App\Repositories\OrderRefund;

use App\Models\OrderRefund;
use App\Repositories\BaseRepository;
use App\Repositories\OrderRefund\Interfaces\OrderRefundRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class OrderRefundRepository extends BaseRepository implements OrderRefundRepositoryInterface
{
    public function __construct(OrderRefund $model)
    {
        $this->model = $model;
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function updateOrCreateByOrderIdWithLogic(int $orderId, array $params): Model
    {
        $refundTotalAmount = str_replace(',', '.', $params['total_price']);
        $orderTotalPrice = str_replace(',', '.', $params['total_price_old']);

        $refund = $this->model->select('id', 'refund_amount as already_refunded_amount', 'tax_amount', 'shipping_tax_amount', 'fulled_tax',
            'shipping_refund_amount as already_refunded_shipping_amount', 'total_shipping_refund_amount as already_refunded_total_shipping_amount')
            ->where('order_id', $orderId)
            ->first();

        $shippingTotalPrice  = $params['shipping_total_price'] ?? 0;
        $netAmount = ($refundTotalAmount - $shippingTotalPrice) / (1 + $params['tax_rate'] / 100);
        $netShipping =  $shippingTotalPrice ? ($shippingTotalPrice / (1 + $params['tax_rate'] / 100)) : 0;
        $paramsShippingTax = $params['shipping_tax'] ?? 0;
        $productTax = $refundTotalAmount - $netAmount;
        $shippingTax = $shippingTotalPrice - $netShipping;
        $salesTax = $productTax + $shippingTax;
        $shippingTaxAmount = ($refund->shipping_tax_amount ?? 0) + $paramsShippingTax;
        $fulled = $params['fulled'] ?? ($refundTotalAmount >= $orderTotalPrice);
        $fulledTax = $params['fulled_tax'] ?? ($refund['fulled_tax'] ?? false);
        $type = $params['type'] ?? false;

        $refundUpdateOrCreateArray = [
            'order_id' => $params['order_id'],
            'refund_amount' => $refundTotalAmount,
            'shipping_refund_amount' => $params['shipping_price'] ?? 0,
            'tax_amount' => $salesTax,
            'fulled' => $fulled,
            'fulled_tax' => $fulledTax,
            'type' => $type,
        ];

        if (!($refund->fulled_tax ?? false)) {
            $refundUpdateOrCreateArray['shipping_tax_amount'] = $shippingTaxAmount;
            $refundUpdateOrCreateArray['total_shipping_refund_amount'] = $shippingTotalPrice;
        } else {
            $refundUpdateOrCreateArray['total_shipping_refund_amount'] = $shippingTotalPrice + $shippingTaxAmount;
        }

        if ($refund) {
            if (!empty($refund['already_refunded_amount'])) {
                $refundUpdateOrCreateArray['refund_amount'] += $refund['already_refunded_amount'];
                $refundUpdateOrCreateArray['fulled'] = $refundUpdateOrCreateArray['refund_amount'] >= $orderTotalPrice;
            }
            if (!empty($refund['already_refunded_shipping_amount'])) {
                $refundUpdateOrCreateArray['shipping_refund_amount'] += $refund['already_refunded_shipping_amount'];
            }
            if (!empty($refund['already_refunded_total_shipping_amount'])) {
                $refundUpdateOrCreateArray['total_shipping_refund_amount'] += $refund['already_refunded_total_shipping_amount'];
            }
            if (!empty($refund['tax_amount'])) {
                $refundUpdateOrCreateArray['tax_amount'] += $refund['tax_amount'];
            }

            $refund->update($refundUpdateOrCreateArray);
        } else {
            $refund = $this->model->create($refundUpdateOrCreateArray);
        }

        return $refund;
    }

    public function updateOrCreateByOrderId(int $orderId, array $params): Model
    {
        $refund = $this->model->select('id')
            ->where('order_id', $orderId)
            ->first();

        if ($refund) {
            $refund->update($params);
        } else {
            $refund = $this->model->create($params);
        }

        return $refund;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }
}
