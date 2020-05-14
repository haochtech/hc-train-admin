<?php

defined("IN_IA") or exit("Access Denied");
$ops = array("list");
global $_GPC, $_W;
$uniacid = $_W["uniacid"];
$op = in_array($_GPC["op"], $ops) ? $_GPC["op"] : "list";
$tablename = "xc_train_order";
switch ($op) {
    case "list":
        $order = pdo_getall("xc_train_order", array("status" => 1, "uniacid" => $uniacid), array(), '', "createtime DESC");
        $store = pdo_getall("xc_train_school", array("uniacid" => $uniacid));
        $store_list = array();
        if ($store) {
            foreach ($store as $s) {
                $store_list[$s["id"]] = $s;
            }
        }
        if ($order) {
            header("Content-type: application/vnd.ms-excel; charset=utf8");
            header("Content-Disposition: attachment; filename=order.xls");
            $data = "<tr>";
            if ($_GPC["title"] == 1) {
                $data .= "<th>服务项目</th>";
            }
            if ($_GPC["total"] == 1) {
                $data .= "<th>人数</th>";
            }
            if ($_GPC["out_trade_no"] == 1) {
                $data .= "<th>订单号</th>";
            }
            if ($_GPC["wx_out_trade_no"] == 1) {
                $data .= "<th>微信订单号</th>";
            }
            if ($_GPC["amount"] == 1) {
                $data .= "<th>应付款</th>";
            }
            if ($_GPC["o_amount"] == 1) {
                $data .= "<th>实付款</th>";
            }
            if ($_GPC["openid"] == 1) {
                $data .= "<th>用户id</th>";
            }
            if ($_GPC["name"] == 1) {
                $data .= "<th>姓名</th>";
            }
            if ($_GPC["store"] == 1) {
                $data .= "<th>性别</th>";
            }
            if ($_GPC["mobile"] == 1) {
                $data .= "<th>手机号</th>";
            }
            if ($_GPC["content"] == 1) {
                $data .= "<th>身份证号</th>";
            }
            if ($_GPC["status"] == 1) {
                $data .= "<th>状态</th>";
            }
            if ($_GPC["createtime"] == 1) {
                $data .= "<th>添加时间</th>";
            }
            $data .= "</tr>";
            foreach ($order as $v) {
                $data = $data . "<tr>";
                if ($_GPC["title"] == 1) {
                    $v["title"] = explode("【", $v["title"]);
                    $data .= "<td>" . $v["title"][0] . "</td>";
                }
                if ($_GPC["total"] == 1) {
                    $data .= "<td>" . $v["total"] . "</td>";
                }
                if ($_GPC["out_trade_no"] == 1) {
                    $data .= "<td style='vnd.ms-excel.numberformat:@'>" . $v["out_trade_no"] . "</td>";
                }
                if ($_GPC["wx_out_trade_no"] == 1) {
                    $data .= "<td style='vnd.ms-excel.numberformat:@'>" . $v["wx_out_trade_no"] . "</td>";
                }
                if ($_GPC["amount"] == 1) {
                    if (!empty($v["amount"])) {
                        $data .= "<td>" . $v["amount"] . "</td>";
                    } else {
                        $data .= "<td>免费</td>";
                    }
                }
                if ($_GPC["o_amount"] == 1) {
                    if (!empty($v["amount"])) {
                        $data .= "<td>" . $v["o_amount"] . "</td>";
                    } else {
                        $data .= "<td>免费</td>";
                    }
                }
                if ($_GPC["openid"] == 1) {
                    $data .= "<td>" . $v["openid"] . "</td>";
                }
                if ($_GPC["name"] == 1) {
                    $data .= "<td>" . $v["name"] . "</td>";
                }
                if ($_GPC["store"] == 1) {
                    $data .= "<td>" . $store_list[$v["store"]]["name"] . "</td>";
                }
                if ($_GPC["mobile"] == 1) {
                    $data .= "<td>" . $v["mobile"] . "</td>";
                }
                if ($_GPC["content"] == 1) {
                    $data .= "<td style='vnd.ms-excel.numberformat:@'>" . $v["content"] . "</td>";
                }
                if ($_GPC["status"] == 1) {
                    if ($v["use"] == 1) {
                        $data .= "<td>已使用</td>";
                    } else {
                        $data .= "<td>未使用</td>";
                    }
                }
                if ($_GPC["createtime"] == 1) {
                    $data .= "<td>" . $v["createtime"] . "</td>";
                }
                $data = $data . "</tr>";
            }
            $data = "<table border='1'>" . $data . "</table>";
            echo iconv("UTF-8", "GBK//TRANSLIT", $data . "\t");
            exit;
        }
        break;
}