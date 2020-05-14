<?php

defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$tablename = $xcmodule . "_" . "discuss";
$do = $_GPC["do"];
if (strlen($_GPC["xtitlea"]) > 0) {
    $xtitlea = urldecode($_GPC["xtitlea"]);
    $xtitleb = urldecode($_GPC["xtitleb"]);
}
switch ($op) {
    case "list":
        $condition = array();
        $condition["uniacid"] = $uniacid;
        if (!empty($_GPC["openid"])) {
            $openid = $_GPC["openid"];
            $condition["openid LIKE"] = "%" . $_GPC["openid"] . "%";
            $_GET["openid"] = $openid;
        }
        if (!empty($_GPC["type"])) {
            $type = $_GPC["type"];
            $condition["type"] = $_GPC["type"];
            $_GET["type"] = $type;
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
        $service = pdo_getall("xc_train_service", array("uniacid" => $uniacid));
        $datalist = array();
        if ($service) {
            foreach ($service as $x) {
                $datalist[$x["id"]] = $x;
            }
        }
        $video = pdo_getall("xc_train_video", array("uniacid" => $uniacid));
        $video_list = array();
        if ($video) {
            foreach ($video as $v) {
                $video_list[$v["id"]] = $v;
            }
        }
        $audio = pdo_getall("xc_train_audio", array("uniacid" => $uniacid));
        $audio_list = array();
        if ($audio) {
            foreach ($audio as $a) {
                $audio_list[$a["id"]] = $a;
            }
        }
        foreach ($list as &$y) {
            if ($y["type"] == 1) {
                $y["type_name"] = "课程";
                $y["pname"] = $datalist[$y["pid"]]["name"];
            } else {
                if ($y["type"] == 2) {
                    $y["type_name"] = "视频";
                    $y["pname"] = $video_list[$y["pid"]]["name"];
                } else {
                    if ($y["type"] == 3) {
                        $y["type_name"] = "音频";
                        $y["pname"] = $audio_list[$y["pid"]]["name"];
                    }
                }
            }
        }
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Discuss/" . $op);
        }
        break;
    case "statuschange":
        $request = pdo_update($tablename, array("status" => $_GPC["status"]), array("id" => $_GPC["id"], "uniacid" => $uniacid));
        if ($request) {
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "delete":
        $discuss = pdo_get($tablename, array("id" => $_GPC["id"], "uniacid" => $uniacid));
        $request = pdo_delete($tablename, array("id" => $_GPC["id"], "uniacid" => $uniacid));
        if ($request) {
            $service = pdo_get("xc_beauty_service", array("status" => 1, "id" => $discuss["pid"]));
            if ($service) {
                $data["discuss_total"] = $service["discuss_total"] - 1;
                if ($discuss["score"] == 1) {
                    $data["good_total"] = $service["good_total"] - 1;
                } else {
                    if ($discuss["score"] == 2) {
                        $data["middle_total"] = $service["middle_total"] - 1;
                    } else {
                        if ($discuss["score"] == 3) {
                            $data["bad_total"] = $service["bad_total"] - 1;
                        }
                    }
                }
                pdo_update("xc_beauty_service", $data, array("status" => 1, "id" => $discuss["pid"]));
            }
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "reply":
        $request = pdo_update($tablename, array("reply_status" => 1, "reply_content" => $_GPC["content"]), array("id" => $_GPC["id"], "uniacid" => $uniacid));
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
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["type"])) {
            $where .= " AND type=:type ";
            $params[":type"] = $_GPC["type"];
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
                $service = pdo_getall("xc_train_service", array("uniacid" => $uniacid));
                $datalist = array();
                if ($service) {
                    foreach ($service as $x) {
                        $datalist[$x["id"]] = $x;
                    }
                }
                $video = pdo_getall("xc_train_video", array("uniacid" => $uniacid));
                $video_list = array();
                if ($video) {
                    foreach ($video as $v) {
                        $video_list[$v["id"]] = $v;
                    }
                }
                $audio = pdo_getall("xc_train_audio", array("uniacid" => $uniacid));
                $audio_list = array();
                if ($audio) {
                    foreach ($audio as $a) {
                        $audio_list[$a["id"]] = $a;
                    }
                }
                $line = pdo_getall("xc_train_line", array("uniacid" => $uniacid));
                $line_list = array();
                if ($line) {
                    foreach ($line as $l) {
                        $line_list[$l["id"]] = $l;
                    }
                }
                $group = pdo_getall("xc_train_service_group", array("uniacid" => $uniacid));
                $group_list = array();
                if ($group) {
                    foreach ($group as $g) {
                        $group_list[$g["id"]] = $g;
                    }
                }
                foreach ($listmodel as &$y) {
                    if ($y["type"] == 1) {
                        $y["type_name"] = "课程";
                        $y["pname"] = $datalist[$y["pid"]]["name"];
                    } else {
                        if ($y["type"] == 2) {
                            $y["type_name"] = "视频";
                            $y["pname"] = $video_list[$y["pid"]]["name"];
                        } else {
                            if ($y["type"] == 3) {
                                $y["type_name"] = "音频";
                                $y["pname"] = $audio_list[$y["pid"]]["name"];
                            } else {
                                if ($y["type"] == 4) {
                                    $y["type_name"] = "礼包";
                                    $y["pname"] = $line_list[$y["pid"]]["name"];
                                } else {
                                    if ($y["type"] == 5) {
                                        $y["type_name"] = "团购";
                                        $y["pname"] = $datalist[$group_list[$y["pid"]]["pid"]]["name"] . " " . $group_list[$y["pid"]]["mark"];
                                    }
                                }
                            }
                        }
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
}