<?php

defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$tablename = $xcmodule . "_" . "line";
$do = $_GPC["do"];
if (strlen($_GPC["xtitlea"]) > 0) {
    $xtitlea = urldecode($_GPC["xtitlea"]);
    $xtitleb = urldecode($_GPC["xtitleb"]);
}
switch ($op) {
    case "list":
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/new_class/" . $op);
        }
        break;
    case "edit":
        if (!empty($_GPC["id"])) {
            $xc = pdo_get($tablename, array("id" => $_GPC["id"]));
            if ($xc) {
                $xc["times"] = array("start" => $xc["start_time"], "end" => $xc["end_time"]);
                if (!empty($xc["video"])) {
                    $xc["video"] = json_decode($xc["video"], true);
                }
                if (!empty($xc["audio"])) {
                    $xc["audio"] = json_decode($xc["audio"], true);
                }
            }
        } else {
            $xc["status"] = 1;
        }
        $class = pdo_getall($xcmodule . "_service_class", array("uniacid" => $_W["uniacid"], "status" => 1, "type" => 5), array(), '', "sort DESC,id DESC");
        $share = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
        if ($share) {
            $share = json_decode($share["content"], true);
        }
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/new_class/" . $op);
        }
        break;
    case "savemodel":
        $xc = $_GPC["xc"];
        if (!empty($_GPC["times"])) {
            $xc["start_time"] = $_GPC["times"]["start"];
            $xc["end_time"] = $_GPC["times"]["end"];
        }
        if (!empty($xc["video"])) {
            $xc["video"] = htmlspecialchars_decode($xc["video"]);
        }
        if (!empty($xc["audio"])) {
            $xc["audio"] = htmlspecialchars_decode($xc["audio"]);
        }
        if (empty($xc["status"])) {
            $xc["status"] = -1;
        }
        if (empty($xc["sort"])) {
            $xc["sort"] = 0;
        }
        if (empty($_GPC["id"])) {
            $xc["uniacid"] = $_W["uniacid"];
            $xc["createtime"] = date("Y-m-d H:i:s");
            $request = pdo_insert($tablename, $xc);
        } else {
            $request = pdo_update($tablename, $xc, array("id" => $_GPC["id"], "uniacid" => $uniacid));
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
                foreach ($listmodel as &$x) {
                    $x["bimg"] = tomedia($x["bimg"]);
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
    case "video":
        $tablename = $xcmodule . "_" . "video";
        $version_id = $_GPC["version_id"];
        $condition = array();
        $condition["uniacid"] = $uniacid;
        $condition["cid !="] = -1;
        if (!empty($_GPC["xname"])) {
            $xname = $_GPC["xname"];
            $condition["name LIKE"] = "%" . $_GPC["xname"] . "%";
            $_GET["xname"] = $xname;
        }
        if (!empty($_GPC["cid"])) {
            $cid = $_GPC["cid"];
            $condition["cid"] = $_GPC["cid"];
            $_GET["cid"] = $cid;
        }
        $request = pdo_getall($tablename, $condition);
        $total = count($request);
        if (!isset($_GPC["page"])) {
            $pageindex = 1;
        } else {
            $pageindex = intval($_GPC["page"]);
        }
        $pagesize = 6;
        $pager = pagination($total, $pageindex, $pagesize);
        $list = pdo_getall($tablename, $condition, array(), '', "sort DESC,createtime DESC", array($pageindex, $pagesize));
        $class = pdo_getall("xc_train_video_class", array("uniacid" => $uniacid, "type" => 1, "status" => 1), array(), '', "sort DESC,createtime DESC");
        $datalist = array();
        if ($class) {
            foreach ($class as $x) {
                $datalist[$x["id"]] = $x["name"];
            }
        }
        $service = pdo_getall("xc_train_service", array("uniacid" => $uniacid), array(), '', "sort DESC,createtime DESC");
        $service_list = array();
        if ($service) {
            foreach ($service as $s) {
                $service_list[$s["id"]] = $s;
            }
        }
        if ($list) {
            foreach ($list as &$y) {
                $y["cidname"] = $datalist[$y["cid"]];
                $y["service_name"] = $service_list[$y["pid"]]["name"];
            }
        }
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        break;
    case "audio":
        $tablename = $xcmodule . "_" . "audio";
        $version_id = $_GPC["version_id"];
        $condition = array();
        $condition["uniacid"] = $uniacid;
        $condition["cid !="] = -1;
        if (!empty($_GPC["xname"])) {
            $xname = $_GPC["xname"];
            $condition["name LIKE"] = "%" . $_GPC["xname"] . "%";
            $_GET["xname"] = $xname;
        }
        if (!empty($_GPC["cid"])) {
            $cid = $_GPC["cid"];
            $condition["cid"] = $_GPC["cid"];
            $_GET["cid"] = $cid;
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
        $list = pdo_getall($tablename, $condition, array(), '', "sort DESC,createtime DESC", array($pageindex, $pagesize));
        $class = pdo_getall("xc_train_video_class", array("uniacid" => $uniacid, "type" => 2, "status" => 1), array(), '', "sort DESC,createtime DESC");
        $datalist = array();
        if ($class) {
            foreach ($class as $x) {
                $datalist[$x["id"]] = $x["name"];
            }
        }
        if ($list) {
            foreach ($list as &$y) {
                $y["cidname"] = $datalist[$y["cid"]];
            }
        }
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        break;
}