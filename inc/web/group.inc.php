<?php

defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule, $xc_type, $xc_admin;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$table = $xcmodule . "_" . "service_group";
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
        $id = intval($_GPC["id"]);
        $xc = array();
        $params = array("id" => $id, "uniacid" => $_W["uniacid"]);
        if (!empty($id)) {
            $xc = pdo_get($table, $params);
            if ($xc) {
                if (!empty($xc["bimg"])) {
                    $xc["bimg"] = explode(",", $xc["bimg"]);
                }
                if (!empty($xc["format"])) {
                    $xc["format"] = json_decode($xc["format"], true);
                }
            }
        }
        $class = pdo_getall($xcmodule . "_service", array("uniacid" => $uniacid, "status" => 1), array(), '', "sort DESC,createtime DESC");
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
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
        $fulltable = tablename($table);
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
                $service = pdo_getall($xcmodule . "_service", array("uniacid" => $_W["uniacid"], "status" => 1));
                $service_data = array();
                if ($service) {
                    foreach ($service as $s) {
                        $service_data[$s["id"]] = $s;
                    }
                }
                foreach ($listmodel as &$x) {
                    if (!empty($service_data[$x["pid"]])) {
                        $x["service_name"] = $service_data[$x["pid"]]["name"];
                    } else {
                        $x["service_name"] = "课程不存在";
                    }
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "save":
        $id = $_GPC["id"];
        $data = $_GPC["xc"];
        if (!empty($data["bimg"])) {
            $data["bimg"] = implode(",", $data["bimg"]);
        } else {
            $data["bimg"] = '';
        }
        if (!empty($data["format"])) {
            $data["format"] = htmlspecialchars_decode($data["format"]);
        } else {
            $data["format"] = '';
        }
        if (strlen($data["status"]) < 1) {
            $data["status"] = -1;
        }
        if ($data["sort"]) {
            $data["sort"] = intval($data["sort"]);
        }
        $ret = -1;
        if (empty($id)) {
            $data["uniacid"] = $_W["uniacid"];
            $data["createtime"] = date("Y-m-d H:i:s");
            $ret = pdo_insert($table, $data);
            if (!empty($ret)) {
                $id = pdo_insertid();
            }
        } else {
            $ret = pdo_update($table, $data, array("id" => $id, "uniacid" => $_W["uniacid"]));
        }
        $status = $ret > 0 ? 1 : -1;
        xc_message($status, null);
        break;
    case "midstatus":
        $id = intval($_GPC["id"]);
        $status = -$_GPC["status"];
        $updatestatus = array();
        $updatestatus["status"] = $status;
        $ret = pdo_update($table, $updatestatus, array("id" => $id, "uniacid" => $_W["uniacid"]));
        xc_message($ret, null);
        break;
    case "updatesorts":
        $id = intval($_GPC["id"]);
        $sorts = $_GPC["sorts"];
        $updatestatus = array();
        $updatestatus["sort"] = $sorts;
        $ret = pdo_update($table, $updatestatus, array("id" => $id, "uniacid" => $_W["uniacid"]));
        xc_message($ret, null);
        break;
    case "delete":
        $uniacid = $_W["uniacid"];
        $ids = $_GPC["ids"];
        $arrids = explode(",", $ids);
        if (count($arrids) < 1) {
            xc_message(-1, null);
        } else {
            $result = pdo_delete($table, array("id" => $arrids, "uniacid" => $uniacid));
            $status = $result > 0 ? 1 : -1;
            xc_message($status, null);
        }
        break;
}