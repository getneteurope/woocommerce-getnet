<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Models\Transaction;

class TransactionIndexCallback
{
    public function __construct()
    {
        //
    }

    public function show()
    {
        $results = Transaction::all();
        echo "
            <div class=\"wrap\">
                <h1>Transactions</h1>
                <table>
                    <tr>
                        <th>Order ID</th>
                        <th>Transaction ID</th>
                        <th>Payment Method</th>
                        <th>Transaction Type</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th></th>
                    </tr>
                    <tbody>
        ";
        $idRow = 0;
        foreach ($results as $row) {
            $order = \WC_Order_Factory::get_order($row->order_id);
            $detailUrl = "?page=gnr_detail&tx_id=" . $row->id;
            echo "
                <tr id=\"getnet-txs-row-" . $idRow . "\">
                    <td>
                        <a href=\"{$order->get_edit_order_url()}\">
                            {$row->order_id}
                        </a>
                    </td>
                    <td>{$row->transaction_id}</td>
                    <td>{$row->payment_method}</td>
                    <td id=\"getnet-txs-row-" . $idRow . "-type\">{$row->last_transaction_type}</td>
                    <td>{$row->created_at}</td>
                    <td>{$row->updated_at}</td>
                    <td>
                        <a id=\"getnet-txs-row-" . $idRow . "-edit\" href=\"{$detailUrl}\">
                            " . __('Edit') . "
                        </a>
                    </td>
                </tr>
            ";
            $idRow++;
        }
        echo "
                </tbody>
            </table>
        </div>";
    }
}
