<?php

defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$tablename = $xcmodule . "_" . "move";
$do = $_GPC["do"];
if (strlen($_GPC["xtitlea"]) > 0) {
    $xtitlea = urldecode($_GPC["xtitlea"]);
    $xtitleb = urldecode($_GPC["xtitleb"]);
}
switch ($op) {
    case "list":
        $store = pdo_getall($xcmodule . "_school", array("uniacid" => $_W["uniacid"], "status" => 1));
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        break;
    case "edit":
        if (!empty($_GPC["id"])) {
            $list = pdo_get($tablename, array("id" => $_GPC["id"]));
            if (!empty($list["bimg"])) {
                $list["bimg"] = explode(",", $list["bimg"]);
            }
            $list["times"] = array("start" => $list["start_time"], "end" => $list["end_time"]);
            if (!empty($list["format"])) {
                $list["format"] = json_decode($list["format"], true);
            }
        } else {
            $list["status"] = 1;
            $list["times"] = array("start" => date("Y-m-d H:i:s"), "end" => date("Y-m-d H:i:s", strtotime("+1 months")));
        }
        $store = pdo_getall($xcmodule . "_school", array("uniacid" => $_W["uniacid"], "status" => 1));
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        break;
    case "savemodel":
        $data = $_GPC["xc"];
        if (!empty($data["format"])) {
            $data["format"] = htmlspecialchars_decode($data["format"]);
        }
        if (!empty($data["bimg"])) {
            $data["bimg"] = implode(",", $data["bimg"]);
        }
        $data["start_time"] = $_GPC["times"]["start"];
        $data["end_time"] = $_GPC["times"]["end"];
        if (empty($data["status"])) {
            $data["status"] = -1;
        }
        if (empty($_GPC["id"])) {
            $data["uniacid"] = $_W["uniacid"];
            $data["createtime"] = date("Y-m-d H:i:s");
            $request = pdo_insert($tablename, $data);
        } else {
            $request = pdo_update($tablename, $data, array("id" => $_GPC["id"], "uniacid" => $uniacid));
        }
        if (!empty($request)) {
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "statuschange":
        $request = pdo_update($tablename, array($_GPC["name"] => $_GPC["status"]), array("id" => $_GPC["id"], "uniacid" => $uniacid));
        if ($request) {
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
    case "getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid ";
        $params = array();
        if (!empty($_GPC["xname"])) {
            $where .= " AND name LIKE :name ";
            $params[":name"] = "%" . $_GPC["xname"] . "%";
        }
        if (!empty($_GPC["store"])) {
            $where .= " AND store=:store ";
            $params[":store"] = $_GPC["store"];
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
                $store = pdo_getall($xcmodule . "_school", array("uniacid" => $_W["uniacid"], "status" => 1));
                $store_data = array();
                if ($store) {
                    foreach ($store as $s) {
                        $store_data[$s["id"]] = $s;
                    }
                }
                foreach ($listmodel as &$x) {
                    if (!empty($x["store"]) && !empty($store_data[$x["store"]])) {
                        $x["store_name"] = $store_data[$x["store"]]["name"];
                    }
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "midstatus":
        $id = intval($_GPC["id"]);
        $status = -$_GPC["status"];
        $updatestatus = array();
        $updatestatus["status"] = $status;
        $ret = pdo_update($tablename, $updatestatus, array("id" => $id, "uniacid" => $_W["uniacid"]));
        xc_message($ret, null);
        break;
    case "new_delete":
        $uniacid = $_W["uniacid"];
        $ids = $_GPC["ids"];
        $arrids = explode(",", $ids);
        if (count($arrids) < 1) {
            xc_message(-1, null);
        } else {
            $result = pdo_delete($tablename, array("id" => $arrids, "uniacid" => $uniacid));
            $status = $result > 0 ? 1 : -1;
            xc_message($status, null);
        }
        break;
    case "updatesorts":
        $id = intval($_GPC["id"]);
        $sorts = $_GPC["sorts"];
        $updatestatus = array();
        $updatestatus["sort"] = $sorts;
        $ret = pdo_update($tablename, $updatestatus, array("id" => $id, "uniacid" => $_W["uniacid"]));
        xc_message($ret, null);
        break;
}