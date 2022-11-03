<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Models\RequestLog;

class LogIndexCallback
{
    public function __construct()
    {
        //
    }

    public function show()
    {
        $results = RequestLog::all([
            'id',
            'url',
            'req_headers',
            'req_body',
            'res_code',
            'res_headers',
            'res_body',
            'order_id',
            'created_at',
            'updated_at'
        ]);
        echo "<div class=\"wrap\">
            <h1>Log</h1>
            <table>
                <tr>
                    <th>id</th>
                    <th>url</th>
                    <th>req_headers</th>
                    <th>req_body</th>
                    <th>res_code</th>
                    <th>res_headers</th>
                    <th>res_body</th>
                    <th>order_id</th>
                    <th>created_at</th>
                    <th>updated_at</th>
                </tr>
                <tbody>
        ";
        foreach ($results as $row) {
            echo "
                <tr>
                    <td>{$row->id}</td>
                    <td>{$row->url}</td>
                    <td>{$row->req_headers}</td>
                    <td>{$row->req_body}</td>
                    <td>{$row->res_code}</td>
                    <td>{$row->res_headers}</td>
                    <td>{$row->res_body}</td>
                    <td>{$row->order_id}</td>
                    <td>{$row->created_at}</td>
                    <td>{$row->updated_at}</td>
                </tr>
            ";
        }
        echo "
                </tbody>
            </table>
        </div>";
    }
}
