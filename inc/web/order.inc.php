<?php

defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$tablename = $xcmodule . "_" . "order";
$do = $_GPC["do"];
if (strlen($_GPC["xtitlea"]) > 0) {
    $xtitlea = urldecode($_GPC["xtitlea"]);
    $xtitleb = urldecode($_GPC["xtitleb"]);
}
switch ($op) {
    case "list":
        $condition = array();
        $condition["status"] = 1;
        $condition["order_type IN"] = array(1, 2);
        $condition["uniacid"] = $uniacid;
        if (!empty($_GPC["out_trade_no"])) {
            $out_trade_no = $_GPC["out_trade_no"];
            $condition["out_trade_no LIKE"] = "%" . $_GPC["out_trade_no"] . "%";
            $_GET["out_trade_no"] = $out_trade_no;
        }
        if (!empty($_GPC["openid"])) {
            $openid = $_GPC["openid"];
            $condition["openid LIKE"] = "%" . $_GPC["openid"] . "%";
            $_GET["openid"] = $openid;
        }
        if (!empty($_GPC["mobile"])) {
            $mobile = $_GPC["mobile"];
            $condition["mobile LIKE"] = "%" . $_GPC["mobile"] . "%";
            $_GET["mobile"] = $mobile;
        }
        if (!empty($_GPC["use"])) {
            $use = $_GPC["use"];
            $condition["use"] = $_GPC["use"];
            $_GET["use"] = $use;
        }
        $request = pdo_getall($tablename, $condition);
        $total = count($request);
        if (!isset($_GPC["page"])) {
            $pageindex = 1;
        } else {
            $pageindex = intval($_GPC["page"]);
        }
        $pagesize = 15;
        $pager = pagination($total, $pageindex, $pagesize);
        $list = pdo_getall($tablename, $condition, array(), '', "createtime DESC", array($pageindex, $pagesize));
        if ($list) {
            $store = pdo_getall("xc_train_school", array("uniacid" => $uniacid));
            $store_list = array();
            if ($store) {
                foreach ($store as $s) {
                    $store_list[$s["id"]] = $s;
                }
            }
            foreach ($list as &$x) {
                if (!empty($x["sign"])) {
                    $x["sign"] = json_decode($x["sign"], true);
                }
                if (!empty($x["store"])) {
                    $x["store_name"] = $store_list[$x["store"]]["name"];
                }
            }
        }
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Order/" . $op);
        }
        break;
    case "getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid AND status=1 AND order_type IN (1,2) ";
        $params = array();
        if (!empty($_GPC["out_trade_no"])) {
            $where .= " AND out_trade_no LIKE :out_trade_no ";
            $params[":out_trade_no"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["mobile"])) {
            $where .= " AND mobile LIKE :mobile ";
            $params[":mobile"] = "%" . $_GPC["mobile"] . "%";
        }
        if (!empty($_GPC["use"])) {
            $where .= " AND `use`=:use ";
            $params[":use"] = $_GPC["use"];
        }
        $params["uniacid"] = $_W["uniacid"];
        $fulltable = tablename($tablename);
        $sql = "SELECT COUNT(*) FROM   " . $fulltable . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $jsondate = array();
        $jsondate["total"] = pdo_fetchcolumn($sql, $params);
        if (empty($jsondate["total"])) {
            $jsondate["rows"] = array();
            xc_ajax($jsondate);
        } else {
            $sql = "SELECT * FROM  {$fulltable}   {$where} ORDER BY " . $ararysort["order"] . " LIMIT " . $ararysort["offset"] . "," . $ararysort["limit"];
            $listmodel = pdo_fetchall($sql, $params);
            if ($listmodel) {
                $store = pdo_getall("xc_train_school", array("uniacid" => $uniacid));
                $store_list = array();
                if ($store) {
                    foreach ($store as $s) {
                        $store_list[$s["id"]] = $s;
                    }
                }
                foreach ($listmodel as &$x) {
                    if (!empty($x["sign"])) {
                        $x["sign"] = json_decode($x["sign"], true);
                    }
                    if (!empty($x["store"])) {
                        $x["store_name"] = $store_list[$x["store"]]["name"];
                    }
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "statuschange":
        $order = pdo_get($tablename, array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
        $condition["is_use"] = intval($order["is_use"]) + 1;
        if (intval($condition["is_use"]) == intval($order["can_use"])) {
            $condition["use"] = 1;
        }
        if (!empty($order["use_time"])) {
            $order["use_time"] = json_decode($order["use_time"], true);
        } else {
            $order["use_time"] = array();
        }
        $condition["use_time"] = $order["use_time"];
        $condition["use_time"][] = date("Y-m-d H:i:s");
        $condition["use_time"] = json_encode($condition["use_time"]);
        $request = pdo_update($tablename, $condition, array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
        if ($request) {
            if (!empty($condition["use"])) {
                $order = pdo_get($tablename, array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
                if ($order) {
                    $share_order = pdo_getall($xcmodule . "_share_order", array("uniacid" => $_W["uniacid"], "out_trade_no" => $order["out_trade_no"], "status" => -1));
                    if ($share_order) {
                        foreach ($share_order as $so) {
                            pdo_update($xcmodule . "_userinfo", array("share_fee +=" => floatval($so["amount"])), array("uniacid" => $_W["uniacid"], "openid" => $so["openid"]));
                            pdo_update($xcmodule . "_share_order", array("status" => 1), array("uniacid" => $_W["uniacid"], "id" => $so["id"]));
                        }
                    }
                }
            }
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "delete":
        $request = pdo_delete($tablename, array("id" => $_GPC["id"], "uniacid" => $uniacid));
        if ($request) {
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "mall":
        $condition = array();
        $condition["status IN"] = array(1, 2);
        $condition["order_type"] = 4;
        $condition["uniacid"] = $uniacid;
        if (!empty($_GPC["out_trade_no"])) {
            $out_trade_no = $_GPC["out_trade_no"];
            $condition["out_trade_no LIKE"] = "%" . $_GPC["out_trade_no"] . "%";
            $_GET["out_trade_no"] = $out_trade_no;
        }
        if (!empty($_GPC["openid"])) {
            $openid = $_GPC["openid"];
            $condition["openid LIKE"] = "%" . $_GPC["openid"] . "%";
            $_GET["openid"] = $openid;
        }
        $request = pdo_getall($tablename, $condition);
        $total = count($request);
        if (!isset($_GPC["page"])) {
            $pageindex = 1;
        } else {
            $pageindex = intval($_GPC["page"]);
        }
        $pagesize = 15;
        $pager = pagination($total, $pageindex, $pagesize);
        $list = pdo_getall($tablename, $condition, array(), '', "createtime DESC", array($pageindex, $pagesize));
        $store = pdo_getall("xc_train_school", array("uniacid" => $uniacid));
        if ($list) {
            $store_list = array();
            if ($store) {
                foreach ($store as $s) {
                    $store_list[$s["id"]] = $s;
                }
            }
            $group = pdo_getall("xc_train_group", array("uniacid" => $uniacid));
            $group_list = array();
            if ($group) {
                foreach ($group as $g) {
                    $group_list[$g["id"]] = $g;
                }
            }
            foreach ($list as &$x) {
                $x["userinfo"] = json_decode($x["userinfo"], true);
                if (!empty($x["store"])) {
                    $x["store_name"] = $store_list[$x["store"]]["name"];
                }
                if ($x["mall_type"] == 2) {
                    $x["group_list"] = $group_list[$x["group_id"]];
                }
            }
        }
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Order/" . $op);
        }
        break;
    case "mall_getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid AND status IN (1,2) AND order_type=4 ";
        $params = array();
        if (!empty($_GPC["out_trade_no"])) {
            $where .= " AND out_trade_no LIKE :out_trade_no ";
            $params[":out_trade_no"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        $params["uniacid"] = $_W["uniacid"];
        $fulltable = tablename($tablename);
        $sql = "SELECT COUNT(*) FROM   " . $fulltable . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $jsondate = array();
        $jsondate["total"] = pdo_fetchcolumn($sql, $params);
        if (empty($jsondate["total"])) {
            $jsondate["rows"] = array();
            xc_ajax($jsondate);
        } else {
            $sql = "SELECT * FROM  {$fulltable}   {$where} ORDER BY " . $ararysort["order"] . " LIMIT " . $ararysort["offset"] . "," . $ararysort["limit"];
            $listmodel = pdo_fetchall($sql, $params);
            if ($listmodel) {
                $store = pdo_getall("xc_train_school", array("uniacid" => $uniacid));
                $store_list = array();
                if ($store) {
                    foreach ($store as $s) {
                        $store_list[$s["id"]] = $s;
                    }
                }
                $group = pdo_getall("xc_train_group", array("uniacid" => $uniacid));
                $group_list = array();
                if ($group) {
                    foreach ($group as $g) {
                        $group_list[$g["id"]] = $g;
                    }
                }
                foreach ($listmodel as &$x) {
                    $x["userinfo"] = json_decode($x["userinfo"], true);
                    if (!empty($x["store"])) {
                        $x["store_name"] = $store_list[$x["store"]]["name"];
                    }
                    if ($x["mall_type"] == 2) {
                        $x["group_list"] = $group_list[$x["group_id"]];
                    }
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "orderchange":
        $request = pdo_update($tablename, array("order_status" => 1), array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
        if ($request) {
            $order = pdo_get($tablename, array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
            if ($order) {
                $share_order = pdo_getall($xcmodule . "_share_order", array("uniacid" => $_W["uniacid"], "out_trade_no" => $order["out_trade_no"], "status" => -1));
                if ($share_order) {
                    foreach ($share_order as $so) {
                        pdo_update($xcmodule . "_userinfo", array("share_fee +=" => floatval($so["amount"])), array("uniacid" => $_W["uniacid"], "openid" => $so["openid"]));
                        pdo_update($xcmodule . "_share_order", array("status" => 1), array("uniacid" => $_W["uniacid"], "id" => $so["id"]));
                    }
                }
                if ($order["order_type"] == 10 && !empty($order["group_openid"]) && !empty($order["group_fee"])) {
                    pdo_update($xcmodule . "_userinfo", array("team_fee +=" => floatval($order["group_fee"])), array("uniacid" => $_W["uniacid"], "openid" => $order["group_openid"]));
                }
            }
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "tui":
        $order = pdo_get($tablename, array("uniacid" => $uniacid, "id" => $_GPC["id"]));
        if ($order) {
            $setting = uni_setting_load("payment", $_W["uniacid"]);
            $refund_setting = $setting["payment"]["wechat_refund"];
            if ($refund_setting["switch"] != 1) {
                $json = array("status" => 0, "msg" => "未开启微信退款功能");
                echo json_encode($json);
                exit;
            }
            if (empty($refund_setting["key"]) || empty($refund_setting["cert"])) {
                $json = array("status" => 0, "msg" => "缺少微信证书");
                echo json_encode($json);
                exit;
            }
            $cert = authcode($refund_setting["cert"], "DECODE");
            $key = authcode($refund_setting["key"], "DECODE");
            file_put_contents(ATTACHMENT_ROOT . $_W["uniacid"] . "_wechat_refund_all.pem", $cert . $key);
            $status = 1;
            $message = '';
            if (floatval($order["o_amount"]) != 0) {
                $transaction_id = $order["wx_out_trade_no"];
                $total_fee = floatval($order["o_amount"]) * 100;
                $refund_fee = floatval($order["o_amount"]) * 100;
                $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
                $refund = array("appid" => $_W["account"]["key"], "mch_id" => $setting["payment"]["wechat"]["mchid"], "nonce_str" => random(8), "out_refund_no" => $transaction_id, "out_trade_no" => $transaction_id, "refund_fee" => $refund_fee, "total_fee" => $total_fee);
                $ref = strtoupper(md5("appid=" . $refund["appid"] . "&mch_id=" . $refund["mch_id"] . "&nonce_str=" . $refund["nonce_str"] . "&out_refund_no=" . $refund["out_refund_no"] . "&out_trade_no=" . $refund["out_trade_no"] . "&refund_fee=" . $refund["refund_fee"] . "&total_fee=" . $refund["total_fee"] . "&key=" . $setting["payment"]["wechat"]["signkey"]));
                $refund["sign"] = $ref;
                load()->func("communication");
                $xml = array2xml($refund);
                $response = ihttp_request("https://api.mch.weixin.qq.com/secapi/pay/refund", $xml, array(CURLOPT_SSLCERT => ATTACHMENT_ROOT . $_W["uniacid"] . "_wechat_refund_all.pem"));
                if ($response) {
                    $data = xml2array($response["content"]);
                    if ($data["return_code"] == "SUCCESS") {
                        if ($data["result_code"] == "SUCCESS") {
                            pdo_update($tablename, array("tui_status" => 1), array("id" => $order["id"], "uniacid" => $_W["uniacid"]));
                        } else {
                            $message = $data["err_code_des"];
                            $status = -1;
                        }
                    } else {
                        $message = $data["return_msg"];
                        $status = -1;
                    }
                } else {
                    $error = curl_errno($ch);
                    $message = $error;
                    $status = -1;
                }
            } else {
                pdo_update($tablename, array("tui_status" => 1), array("id" => $order["id"], "uniacid" => $_W["uniacid"]));
                $status = 1;
            }
            unlink(ATTACHMENT_ROOT . $_W["uniacid"] . "_wechat_refund_all.pem");
            if ($status == 1) {
                $json = array("status" => 1, "msg" => "操作成功");
                echo json_encode($json);
                exit;
            } else {
                $json = array("status" => 0, "msg" => $message);
                echo json_encode($json);
                exit;
            }
        } else {
            $json = array("status" => 0, "msg" => "操作失败1");
            echo json_encode($json);
            exit;
        }
        break;
    case "line":
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Order/" . $op);
        }
        break;
    case "line_getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid AND status=1 AND order_type=6 ";
        $params = array();
        if (!empty($_GPC["out_trade_no"])) {
            $where .= " AND out_trade_no LIKE :out_trade_no ";
            $params[":out_trade_no"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        $params["uniacid"] = $_W["uniacid"];
        $fulltable = tablename($tablename);
        $sql = "SELECT COUNT(*) FROM   " . $fulltable . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $jsondate = array();
        $jsondate["total"] = pdo_fetchcolumn($sql, $params);
        if (empty($jsondate["total"])) {
            $jsondate["rows"] = array();
            xc_ajax($jsondate);
        } else {
            $sql = "SELECT * FROM  {$fulltable}   {$where} ORDER BY " . $ararysort["order"] . " LIMIT " . $ararysort["offset"] . "," . $ararysort["limit"];
            $listmodel = pdo_fetchall($sql, $params);
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "group":
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        break;
    case "group_getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid AND status IN (1,2) AND order_type=7 ";
        $params = array();
        if (!empty($_GPC["out_trade_no"])) {
            $where .= " AND out_trade_no LIKE :out_trade_no ";
            $params[":out_trade_no"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["mobile"])) {
            $where .= " AND mobile LIKE :mobile ";
            $params[":mobile"] = "%" . $_GPC["mobile"] . "%";
        }
        if (!empty($_GPC["use"])) {
            $where .= " AND `use`=:use ";
            $params[":use"] = $_GPC["use"];
        }
        $params["uniacid"] = $_W["uniacid"];
        $fulltable = tablename($tablename);
        $sql = "SELECT COUNT(*) FROM   " . $fulltable . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $jsondate = array();
        $jsondate["total"] = pdo_fetchcolumn($sql, $params);
        if (empty($jsondate["total"])) {
            $jsondate["rows"] = array();
            xc_ajax($jsondate);
        } else {
            $sql = "SELECT * FROM  {$fulltable}   {$where} ORDER BY " . $ararysort["order"] . " LIMIT " . $ararysort["offset"] . "," . $ararysort["limit"];
            $listmodel = pdo_fetchall($sql, $params);
            if ($listmodel) {
                $store = pdo_getall("xc_train_school", array("uniacid" => $uniacid));
                $store_list = array();
                if ($store) {
                    foreach ($store as $s) {
                        $store_list[$s["id"]] = $s;
                    }
                }
                foreach ($listmodel as &$x) {
                    if (!empty($x["sign"])) {
                        $x["sign"] = json_decode($x["sign"], true);
                    }
                    if (!empty($x["store"])) {
                        $x["store_name"] = $store_list[$x["store"]]["name"];
                    }
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "group_export":
        $condition = array();
        $condition["status"] = array(1, 2);
        $condition["order_type"] = 7;
        $condition["uniacid"] = $_W["uniacid"];
        if (!empty($_GPC["out_trade_no"])) {
            $condition["out_trade_no LIKE"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["openid"])) {
            $condition["openid LIKE"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["mobile"])) {
            $condition["mobile LIKE"] = "%" . $_GPC["mobile"] . "%";
        }
        if (!empty($_GPC["use"])) {
            $condition["use"] = $_GPC["use"];
        }
        $order = pdo_getall("xc_train_order", $condition, array(), '', "id DESC");
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
            $xc = htmlspecialchars_decode($_GPC["xc"]);
            $xc = json_decode($xc, true);
            foreach ($xc as $x) {
                if ($x["status"] == 1) {
                    $data .= "<th>" . $x["name"] . "</th>";
                }
            }
            $data .= "</tr>";
            foreach ($order as $v) {
                if ($v["status"] == 1) {
                    if ($v["group_status"] == -1) {
                        $v["status_name"] = "拼团中";
                    } else {
                        if ($v["group_status"] == 1) {
                            if ($v["use"] == 1) {
                                $v["status_name"] = "已使用";
                            } else {
                                $v["status_name"] = "未使用";
                            }
                        }
                    }
                } else {
                    if ($v["status"] == 2) {
                        if ($v["tui_status"] == -1) {
                            $v["status_name"] = "退款中";
                        } else {
                            if ($v["tui_status"] == 1) {
                                $v["status_name"] = "已退款";
                            }
                        }
                    }
                }
                $v["store_name"] = $store_list[$v["store"]]["name"];
                $data = $data . "<tr>";
                foreach ($xc as $x) {
                    if ($x["status"] == 1) {
                        if ($x["key"] == "sign") {
                            $data .= "<td>";
                            if (!empty($v["sign"])) {
                                $v["sign"] = json_decode($v["sign"], true);
                                if (is_array($v["sign"]) && !empty($v["sign"])) {
                                    foreach ($v["sign"] as $vs) {
                                        $data .= $vs["name"] . "：" . $vs["value"] . "<br/>";
                                    }
                                }
                            }
                            $data .= "</td>";
                        } else {
                            $data .= "<td style='vnd.ms-excel.numberformat:@'>" . $v[$x["key"]] . "</td>";
                        }
                    }
                }
                $data = $data . "</tr>";
            }
            $data = "<table border='1'>" . $data . "</table>";
            echo iconv("UTF-8", "GBK//TRANSLIT", $data);
            exit;
        }
        break;
    case "score":
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        break;
    case "score_getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid AND status=1 AND order_type=8 ";
        $params = array();
        if (!empty($_GPC["out_trade_no"])) {
            $where .= " AND out_trade_no LIKE :out_trade_no ";
            $params[":out_trade_no"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["xname"])) {
            $where .= " AND name LIKE :name ";
            $params[":name"] = "%" . $_GPC["xname"] . "%";
        }
        if (!empty($_GPC["mobile"])) {
            $where .= " AND mobile LIKE :mobile ";
            $params[":mobile"] = "%" . $_GPC["mobile"] . "%";
        }
        if (!empty($_GPC["order_status"])) {
            $where .= " AND `order_status`=:order_status ";
            $params[":order_status"] = $_GPC["order_status"];
        }
        $params["uniacid"] = $_W["uniacid"];
        $fulltable = tablename($tablename);
        $sql = "SELECT COUNT(*) FROM   " . $fulltable . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $jsondate = array();
        $jsondate["total"] = pdo_fetchcolumn($sql, $params);
        if (empty($jsondate["total"])) {
            $jsondate["rows"] = array();
            xc_ajax($jsondate);
        } else {
            $sql = "SELECT * FROM  {$fulltable}   {$where} ORDER BY " . $ararysort["order"] . " LIMIT " . $ararysort["offset"] . "," . $ararysort["limit"];
            $listmodel = pdo_fetchall($sql, $params);
            if (!$listmodel) {
                $jsondate["rows"] = $listmodel;
            }
            xc_ajax($jsondate);
        }
        break;
    case "score_export":
        $condition = array();
        $condition["status"] = 1;
        $condition["order_type"] = 8;
        $condition["uniacid"] = $_W["uniacid"];
        if (!empty($_GPC["out_trade_no"])) {
            $condition["out_trade_no LIKE"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["openid"])) {
            $condition["openid LIKE"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["xname"])) {
            $condition["name LIKE"] = "%" . $_GPC["xname"] . "%";
        }
        if (!empty($_GPC["mobile"])) {
            $condition["mobile LIKE"] = "%" . $_GPC["mobile"] . "%";
        }
        if (!empty($_GPC["order_status"])) {
            $condition["order_status"] = $_GPC["order_status"];
        }
        $order = pdo_getall("xc_train_order", $condition, array(), '', "id DESC");
        if ($order) {
            header("Content-type: application/vnd.ms-excel; charset=utf8");
            header("Content-Disposition: attachment; filename=order.xls");
            $data = "<tr>";
            $xc = htmlspecialchars_decode($_GPC["xc"]);
            $xc = json_decode($xc, true);
            foreach ($xc as $x) {
                if ($x["status"] == 1) {
                    $data .= "<th>" . $x["name"] . "</th>";
                }
            }
            $data .= "</tr>";
            foreach ($order as $v) {
                if ($v["order_status"] == -1) {
                    $v["status_name"] = "未核销";
                } else {
                    if ($v["order_status"] == 1) {
                        $v["status_name"] = "已核销";
                    }
                }
                if ($v["pei_type"] == 1) {
                    $v["pei_type_name"] = "商家配送";
                } else {
                    if ($v["pei_type"] == 2) {
                        $v["pei_type_name"] = "自提";
                    }
                }
                $data = $data . "<tr>";
                foreach ($xc as $x) {
                    if ($x["status"] == 1) {
                        $data .= "<td style='vnd.ms-excel.numberformat:@'>" . $v[$x["key"]] . "</td>";
                    }
                }
                $data = $data . "</tr>";
            }
            $data = "<table border='1'>" . $data . "</table>";
            echo iconv("UTF-8", "GBK//TRANSLIT", $data);
            exit;
        }
        break;
    case "move":
        $store = pdo_getall($xcmodule . "_school", array("uniacid" => $_W["uniacid"], "status" => 1));
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        break;
    case "move_getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid AND status IN (1,2) AND order_type=9 ";
        $params = array();
        if (!empty($_GPC["out_trade_no"])) {
            $where .= " AND out_trade_no LIKE :out_trade_no ";
            $params[":out_trade_no"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["xname"])) {
            $where .= " AND name LIKE :name ";
            $params[":name"] = "%" . $_GPC["xname"] . "%";
        }
        if (!empty($_GPC["mobile"])) {
            $where .= " AND mobile LIKE :mobile ";
            $params[":mobile"] = "%" . $_GPC["mobile"] . "%";
        }
        if (!empty($_GPC["store"])) {
            $where .= " AND store=:store ";
            $params[":store"] = $_GPC["store"];
        }
        if (!empty($_GPC["status"])) {
            if ($_GPC["status"] == -1) {
                $where .= " AND status=1 AND order_status=-1 ";
            } else {
                if ($_GPC["status"] == 1) {
                    $where .= " AND status=1 AND order_status=1 ";
                } else {
                    if ($_GPC["status"] == 2) {
                        $where .= " AND status=2 AND tui_status=-1 ";
                    } else {
                        if ($_GPC["status"] == 3) {
                            $where .= " AND status=2 AND tui_status=1 ";
                        }
                    }
                }
            }
        }
        $params["uniacid"] = $_W["uniacid"];
        $fulltable = tablename($tablename);
        $sql = "SELECT COUNT(*) FROM   " . $fulltable . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $jsondate = array();
        $jsondate["total"] = pdo_fetchcolumn($sql, $params);
        if (empty($jsondate["total"])) {
            $jsondate["rows"] = array();
            xc_ajax($jsondate);
        } else {
            $sql = "SELECT * FROM  {$fulltable}   {$where} ORDER BY " . $ararysort["order"] . " LIMIT " . $ararysort["offset"] . "," . $ararysort["limit"];
            $listmodel = pdo_fetchall($sql, $params);
            if ($listmodel) {
                foreach ($listmodel as &$x) {
                    if (!empty($x["line_data"])) {
                        $x["line_data"] = json_decode($x["line_data"], true);
                    }
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "move_export":
        $condition = array();
        $condition["status IN"] = array(1, 2);
        $condition["order_type"] = 9;
        $condition["uniacid"] = $_W["uniacid"];
        if (!empty($_GPC["out_trade_no"])) {
            $condition["out_trade_no LIKE"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["openid"])) {
            $condition["openid LIKE"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["xname"])) {
            $condition["name LIKE"] = "%" . $_GPC["xname"] . "%";
        }
        if (!empty($_GPC["mobile"])) {
            $condition["mobile LIKE"] = "%" . $_GPC["mobile"] . "%";
        }
        if (!empty($_GPC["store"])) {
            $where .= " AND store=:store ";
            $params[":store"] = $_GPC["store"];
        }
        if (!empty($_GPC["status"])) {
            if ($_GPC["status"] == -1) {
                $condition["status"] = 1;
                $condition["order_status"] = -1;
            } else {
                if ($_GPC["status"] == 1) {
                    $condition["status"] = 1;
                    $condition["order_status"] = 1;
                } else {
                    if ($_GPC["status"] == 2) {
                        $condition["status"] = 2;
                        $condition["tui_status"] = -1;
                    } else {
                        if ($_GPC["status"] == 3) {
                            $condition["status"] = 2;
                            $condition["tui_status"] = 1;
                        }
                    }
                }
            }
        }
        $order = pdo_getall("xc_train_order", $condition, array(), '', "id DESC");
        if ($order) {
            header("Content-type: application/vnd.ms-excel; charset=utf8");
            header("Content-Disposition: attachment; filename=order.xls");
            $data = "<tr>";
            $xc = htmlspecialchars_decode($_GPC["xc"]);
            $xc = json_decode($xc, true);
            foreach ($xc as $x) {
                if ($x["status"] == 1) {
                    $data .= "<th>" . $x["name"] . "</th>";
                }
            }
            $data .= "</tr>";
            foreach ($order as $v) {
                if ($v["status"] == 1) {
                    if ($v["order_status"] == -1) {
                        $v["status_name"] = "未核销";
                    } else {
                        if ($v["order_status"] == 1) {
                            $v["status_name"] = "已核销";
                        }
                    }
                } else {
                    if ($v["status"] == 2) {
                        if ($v["tui_status"] == -1) {
                            $v["status_name"] = "退款中";
                        } else {
                            if ($v["tui_status"] == 1) {
                                $v["status_name"] = "已退款";
                            }
                        }
                    } else {
                        $v["status_name"] = "未支付";
                    }
                }
                $data = $data . "<tr>";
                foreach ($xc as $x) {
                    if ($x["status"] == 1) {
                        $data .= "<td style='vnd.ms-excel.numberformat:@'>" . $v[$x["key"]] . "</td>";
                    }
                }
                $data = $data . "</tr>";
            }
            $data = "<table border='1'>" . $data . "</table>";
            echo iconv("UTF-8", "GBK//TRANSLIT", $data);
            exit;
        }
        break;
    case "team":
        $store = pdo_getall($xcmodule . "_school", array("uniacid" => $_W["uniacid"], "status" => 1));
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        break;
    case "team_getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid AND status=1 AND order_type=10 ";
        $params = array();
        if (!empty($_GPC["out_trade_no"])) {
            $where .= " AND out_trade_no LIKE :out_trade_no ";
            $params[":out_trade_no"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["xname"])) {
            $where .= " AND name LIKE :name ";
            $params[":name"] = "%" . $_GPC["xname"] . "%";
        }
        if (!empty($_GPC["mobile"])) {
            $where .= " AND mobile LIKE :mobile ";
            $params[":mobile"] = "%" . $_GPC["mobile"] . "%";
        }
        if (!empty($_GPC["store"])) {
            $where .= " AND store=:store ";
            $params[":store"] = $_GPC["store"];
        }
        if (!empty($_GPC["status"])) {
            if ($_GPC["status"] == -1) {
                $where .= " AND unix_timestamp(end_time)<:times AND order_status=-1 ";
                $params[":times"] = time();
            } else {
                if ($_GPC["status"] == 1) {
                    $where .= " AND unix_timestamp(end_time)<:times AND order_status=1 ";
                    $params[":times"] = time();
                } else {
                    if ($_GPC["status"] == 2) {
                        $where .= " AND unix_timestamp(end_time)>:times ";
                        $params[":times"] = time();
                    }
                }
            }
        }
        $params["uniacid"] = $_W["uniacid"];
        $fulltable = tablename($tablename);
        $sql = "SELECT COUNT(*) FROM   " . $fulltable . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $jsondate = array();
        $jsondate["total"] = pdo_fetchcolumn($sql, $params);
        if (empty($jsondate["total"])) {
            $jsondate["rows"] = array();
            xc_ajax($jsondate);
        } else {
            $sql = "SELECT * FROM  {$fulltable}   {$where} ORDER BY " . $ararysort["order"] . " LIMIT " . $ararysort["offset"] . "," . $ararysort["limit"];
            $listmodel = pdo_fetchall($sql, $params);
            if ($listmodel) {
                foreach ($listmodel as &$x) {
                    $x["curr"] = -1;
                    if (time() > strtotime($x["end_time"])) {
                        $x["curr"] = 1;
                    }
                    if (!empty($x["service_data"])) {
                        $x["service_data"] = json_decode($x["service_data"], true);
                    }
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "team_export":
        $condition = array();
        $condition["status"] = 1;
        $condition["order_type"] = 10;
        $condition["uniacid"] = $_W["uniacid"];
        if (!empty($_GPC["out_trade_no"])) {
            $condition["out_trade_no LIKE"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["openid"])) {
            $condition["openid LIKE"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["xname"])) {
            $condition["name LIKE"] = "%" . $_GPC["xname"] . "%";
        }
        if (!empty($_GPC["mobile"])) {
            $condition["mobile LIKE"] = "%" . $_GPC["mobile"] . "%";
        }
        if (!empty($_GPC["store"])) {
            $where .= " AND store=:store ";
            $params[":store"] = $_GPC["store"];
        }
        if (!empty($_GPC["status"])) {
            if ($_GPC["status"] == -1) {
                $condition["end_time <"] = date("Y-m-d H:i:s");
                $condition["order_status"] = -1;
            } else {
                if ($_GPC["status"] == 1) {
                    $condition["end_time <"] = date("Y-m-d H:i:s");
                    $condition["order_status"] = 1;
                } else {
                    if ($_GPC["status"] == 2) {
                        $condition["end_time >"] = date("Y-m-d H:i:s");
                        $condition["status"] = 2;
                    }
                }
            }
        }
        $order = pdo_getall("xc_train_order", $condition, array(), '', "id DESC");
        if ($order) {
            header("Content-type: application/vnd.ms-excel; charset=utf8");
            header("Content-Disposition: attachment; filename=order.xls");
            $data = "<tr>";
            $xc = htmlspecialchars_decode($_GPC["xc"]);
            $xc = json_decode($xc, true);
            foreach ($xc as $x) {
                if ($x["status"] == 1) {
                    $data .= "<th>" . $x["name"] . "</th>";
                }
            }
            $data .= "</tr>";
            foreach ($order as $v) {
                if (strtotime($v["end_time"]) < time()) {
                    if ($v["order_status"] == -1) {
                        $v["status_name"] = "未核销";
                    } else {
                        if ($v["order_status"] == 1) {
                            $v["status_name"] = "已核销";
                        }
                    }
                } else {
                    $v["status_name"] = "接龙中";
                }
                if ($v["pei_type"] == 1) {
                    $v["pei_name"] = "商家配送";
                } else {
                    if ($v["pei_type"] == 2) {
                        $v["pei_name"] = "自提";
                    }
                }
                $data = $data . "<tr>";
                foreach ($xc as $x) {
                    if ($x["status"] == 1) {
                        $data .= "<td style='vnd.ms-excel.numberformat:@'>" . $v[$x["key"]] . "</td>";
                    }
                }
                $data = $data . "</tr>";
            }
            $data = "<table border='1'>" . $data . "</table>";
            echo iconv("UTF-8", "GBK//TRANSLIT", $data);
            exit;
        }
        break;
}