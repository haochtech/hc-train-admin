<?php

defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$tablename = $xcmodule . "_" . "mall_team";
$do = $_GPC["do"];
if (strlen($_GPC["xtitlea"]) > 0) {
    $xtitlea = urldecode($_GPC["xtitlea"]);
    $xtitleb = urldecode($_GPC["xtitleb"]);
}
switch ($op) {
    case "list":
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        break;
    case "edit":
        if (!empty($_GPC["id"])) {
            $list = pdo_get($tablename, array("id" => $_GPC["id"]));
            if ($list) {
                if (!empty($list["bimg"])) {
                    $list["bimg"] = explode(",", $list["bimg"]);
                }
                $list["times"] = array("start" => $list["start_time"], "end" => $list["end_time"]);
                if (!empty($list["service"])) {
                    $mall = pdo_getall($xcmodule . "_mall", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $list["service"]));
                }
            }
        } else {
            $list["status"] = 1;
        }
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        break;
    case "savemodel":
        $data = $_GPC["xc"];
        if (!empty($_GPC["times"])) {
            $data["start_time"] = $_GPC["times"]["start"];
            $data["end_time"] = $_GPC["times"]["end"];
        }
        if (!empty($data["bimg"])) {
            $data["bimg"] = implode(",", $data["bimg"]);
        }
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
                $ids = array();
                foreach ($listmodel as $xx) {
                    if (!empty($xx["service"])) {
                        $ids[] = $xx["service"];
                    }
                }
                $service = pdo_getall($xcmodule . "_mall", array("uniacid" => $_W["uniacid"], "status" => 1));
                $service_data = array();
                if ($service) {
                    foreach ($service as $s) {
                        $service_data[$s["id"]] = $s;
                    }
                }
                foreach ($listmodel as &$y) {
                    if (!empty($y["service"]) && !empty($service_data[$y["service"]])) {
                        $y["service_name"] = $service_data[$y["service"]]["name"];
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
        exit;
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
    case "select":
        $fulltable = tablename($xcmodule . "_mall");
        $q = $_GPC["q"];
        $strwhere = array();
        $pages = $_GPC["page"];
        if (empty($pages)) {
            $pages = 1;
        }
        $pagesize = 20;
        $where = " WHERE uniacid=:uniacid AND status=1 ";
        $params = array();
        $params["uniacid"] = $_W["uniacid"];
        if ($q && $q != -1) {
            $where .= " and name like :q";
            $params[":q"] = "%" . $q . "%";
        }
        $sql = "SELECT COUNT(*) FROM   " . $fulltable . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $jsondate = array();
        $jsondate["total"] = pdo_fetchcolumn($sql, $params);
        if (empty($jsondate["total"])) {
            $jsondate["rows"] = array();
            xc_ajax($jsondate);
        } else {
            $sql = "SELECT * FROM  {$fulltable}   {$where} ORDER BY sort DESC,id DESC LIMIT " . ($pages - 1) * $pagesize . "," . $pagesize;
            $listmodel = pdo_fetchall($sql, $params);
            $request = array();
            if ($pages == 1) {
                $request[] = array("id" => 0, "text" => "不选择");
            }
            foreach ($listmodel as $x) {
                $umdoel = array();
                $umdoel["id"] = $x["id"];
                $umdoel["text"] = $x["name"];
                $request[] = $umdoel;
            }
            $jsondate["total"] = $jsondate["total"] + 1;
            $jsondate["rows"] = $request;
            xc_ajax($jsondate);
        }
        break;
}