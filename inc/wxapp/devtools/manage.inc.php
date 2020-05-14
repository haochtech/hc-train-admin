<?php

//decode by http://www.zx-xcx.com/
defined("IN_IA") or exit("Access Denied");
$ops = array("bind", "login", "index", "order_status", "search", "search2", "sign", "order_status2");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = in_array($_GPC["op"], $ops) ? $_GPC["op"] : "manage";
switch ($op) {
	case "bind":
		$request = pdo_get("xc_train_userinfo", array("status" => 1, "uniacid" => $uniacid, "openid" => $_W["openid"]));
		if ($request) {
			if ($request["shop"] == 1) {
				return $this->result(0, "操作成功", array("status" => 1));
			} else {
				return $this->result(0, "操作失败");
			}
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "login":
		$request = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "web"));
		if ($request) {
			$request["content"] = json_decode($request["content"], true);
			if (!empty($request["content"]["password"])) {
				if ($request["content"]["password"] == $_GPC["password"]) {
					if ($_GPC["status"] == 1) {
						pdo_update("xc_train_userinfo", array("shop" => 1), array("uniacid" => $uniacid, "status" => 1, "openid" => $_W["openid"]));
					}
					return $this->result(0, "操作成功", array("status" => 1));
				} else {
					return $this->result(1, "密码错误");
				}
			} else {
				return $this->result(1, "密码错误");
			}
		} else {
			return $this->result(1, "密码错误");
		}
		break;
	case "index":
		$request = array();
		if ($_GPC["curr"] == 3) {
			$request = pdo_getall("xc_train_prize", array("status" => 1, "uniacid" => $uniacid), array(), '', "prizetime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		} elseif ($_GPC["curr"] == 4) {
			$condition["status"] = 1;
			$condition["uniacid"] = $uniacid;
			if (!empty($_GPC["cid"])) {
				$condition["cid"] = $_GPC["cid"];
			}
			$service = pdo_getall("xc_train_service", $condition);
			if ($service) {
				$pid = array();
				foreach ($service as $ssss) {
					$pid[] = $ssss["id"];
				}
				$request = pdo_getall("xc_train_service_team", array("status" => 1, "uniacid" => $uniacid, "pid IN" => $pid), array(), '', "createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
			}
		} elseif ($_GPC["curr"] == 5) {
			if (!empty($_GPC["store"])) {
				$condition["store"] = $_GPC["store"];
			}
			$condition["status"] = 1;
			$condition["uniacid"] = $uniacid;
			$condition["order_type"] = 4;
			$request = pdo_getall("xc_train_order", $condition, array(), '', "createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		} elseif ($_GPC["curr"] == 7) {
			if (!empty($_GPC["store"])) {
				$condition["store"] = $_GPC["store"];
			}
			$condition["status"] = 1;
			$condition["uniacid"] = $uniacid;
			$condition["order_type"] = 8;
			$request = pdo_getall("xc_train_order", $condition, array(), '', "createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		} elseif ($_GPC["curr"] == 8) {
			if (!empty($_GPC["store"])) {
				$condition["store"] = $_GPC["store"];
			}
			$condition["status"] = 1;
			$condition["uniacid"] = $uniacid;
			$condition["order_type"] = 9;
			$request = pdo_getall("xc_train_order", $condition, array(), '', "createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		} elseif ($_GPC["curr"] == 9) {
			if (!empty($_GPC["store"])) {
				$condition["store"] = $_GPC["store"];
			}
			$condition["status"] = 1;
			$condition["uniacid"] = $uniacid;
			$condition["order_type"] = 10;
			$condition["end_time <"] = date("Y-m-d H:i:s");
			$request = pdo_getall("xc_train_order", $condition, array(), '', "createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		} else {
			if (!empty($_GPC["store"])) {
				$condition["store"] = $_GPC["store"];
			}
			$condition["status"] = 1;
			$condition["uniacid"] = $uniacid;
			if ($_GPC["curr"] == 6) {
				$condition["order_type"] = 7;
				$condition["group_status"] = 1;
			} else {
				$condition["order_type"] = $_GPC["curr"];
			}
			$request = pdo_getall("xc_train_order", $condition, array(), '', "createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		}
		if ($request) {
			if ($_GPC["curr"] == 3) {
				$userinfo = pdo_getall("xc_train_userinfo", array("uniacid" => $uniacid));
				$datalist = array();
				if ($userinfo) {
					foreach ($userinfo as $u) {
						$datalist[$u["openid"]] = $u;
					}
				}
				$service = pdo_getall("xc_train_active", array("uniacid" => $uniacid));
				$service_list = array();
				if ($service) {
					foreach ($service as $s) {
						$service_list[$s["id"]] = $s;
					}
				}
				$gua = pdo_getall("xc_train_gua", array("uniacid" => $uniacid));
				$gua_list = array();
				if ($gua) {
					foreach ($gua as $g) {
						$gua_list[$g["id"]] = $g;
					}
				}
				foreach ($request as &$x) {
					$x["simg"] = $datalist[$x["openid"]]["avatar"];
					$x["nick"] = base64_decode($datalist[$x["openid"]]["nick"]);
					if (!empty($x["prizetime"])) {
						$x["prizetime"] = date("Y-m-d H:i", strtotime($x["prizetime"]));
					}
					if (!empty($x["usetime"])) {
						$x["usetime"] = date("Y-m-d H:i", strtotime($x["usetime"]));
					}
					if ($x["type"] == 1) {
						$x["bimg"] = tomedia($service_list[$x["cid"]]["bimg"]);
					} elseif ($x["type"] == 2) {
						$x["bimg"] = tomedia($gua_list[$x["pid"]]["bimg"]);
					}
				}
			} elseif ($_GPC["curr"] == 4) {
				$service = pdo_getall("xc_train_service", array("uniacid" => $uniacid));
				$datalist = array();
				if ($service) {
					foreach ($service as $s) {
						$datalist[$s["id"]] = $s;
					}
				}
				$order = pdo_getall("xc_train_order", array("status" => 1, "uniacid" => $uniacid, "use" => -1, "order_type !=" => 3));
				$order_list = array();
				if ($order) {
					foreach ($order as $o) {
						$order_list[$o["pid"]][] = $o;
					}
				}
				foreach ($request as &$x) {
					$x["service_name"] = $datalist[$x["pid"]]["name"];
					$x["order"] = $order_list[$x["id"]];
				}
			} elseif ($_GPC["curr"] == 5) {
				$store = pdo_getall("xc_train_school", array("uniacid" => $uniacid));
				$store_list = array();
				if ($store) {
					foreach ($store as $s) {
						$store_list[$s["id"]] = $s;
					}
				}
				foreach ($request as &$x) {
					$x["userinfo"] = json_decode($x["userinfo"], true);
					if (!empty($x["store"])) {
						$x["store_name"] = $store_list[$x["store"]]["name"];
					}
				}
			} else {
				$service = pdo_getall("xc_train_service_team", array("uniacid" => $uniacid));
				$datalist = array();
				if ($service) {
					foreach ($service as $s) {
						$datalist[$s["id"]] = $s;
					}
				}
				foreach ($request as &$x) {
					$x["start_time"] = $datalist[$x["pid"]]["start_time"];
					if (!empty($x["sign"])) {
						$x["sign"] = json_decode($x["sign"], true);
					}
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "order_status":
		$tablename = "xc_train_order";
		$request = array();
		if ($_GPC["curr"] == 1 || $_GPC["curr"] == 2 || $_GPC["curr"] == 6) {
			$order = pdo_get("xc_train_order", array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
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
			$request = pdo_update("xc_train_order", $condition, array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
			if ($request && !empty($condition["use"])) {
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
		} else {
			if ($_GPC["curr"] == 3) {
				$request = pdo_update("xc_train_prize", array("use" => 1, "usetime" => date("Y-m-d H:i:s")), array("status" => 1, "id" => $_GPC["id"], "uniacid" => $uniacid));
			}
		}
		if (!empty($request)) {
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "order_status2":
		$tablename = "xc_train_order";
		$request = pdo_update("xc_train_order", array("order_status" => 1), array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
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
			}
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "search":
		$content = $_GPC["content"];
		if (!empty($_GPC["id"])) {
			$content = $_GPC["id"];
		}
		$request = array();
		$request["curr"] = 1;
		if (!is_numeric($content)) {
			$request["curr"] = 3;
			$request["list"] = pdo_get("xc_train_prize", array("id" => base64_decode($content), "uniacid" => $uniacid));
			if ($request["list"]) {
				$userinfo = pdo_get("xc_train_userinfo", array("status" => 1, "openid" => $request["list"]["openid"], "uniacid" => $uniacid));
				if ($userinfo) {
					$request["list"]["nick"] = base64_decode($userinfo["nick"]);
				}
				$service = pdo_get("xc_train_active", array("status" => 1, "id" => $request["list"]["cid"]));
				if ($service) {
					$request["list"]["bimg"] = tomedia($service["bimg"]);
				}
			} else {
				return $this->result(1, "没有此订单");
			}
		} else {
			$condition["status"] = 1;
			$condition["uniacid"] = $uniacid;
			if (!empty($_GPC["content"])) {
				$condition["out_trade_no LIKE"] = "%" . $content . "%";
			} elseif (!empty($_GPC["id"])) {
				$condition["id"] = $_GPC["id"];
			}
			if (!empty($_GPC["store"])) {
				$condition["store"] = $_GPC["store"];
			}
			$request["list"] = pdo_get("xc_train_order", $condition);
			if ($request["list"]) {
				$service = pdo_getall("xc_train_service_team", array("uniacid" => $uniacid, "id" => $request["list"]["pid"]));
				$request["list"]["start_time"] = $service["start_time"];
				$request["list"]["userinfo"] = json_decode($request["list"]["userinfo"], true);
				if (!empty($request["list"]["store"])) {
					$store = pdo_get("xc_train_school", array("uniacid" => $uniacid, "id" => $request["list"]["store"]));
					if ($store) {
						$request["list"]["store_name"] = $store["name"];
					}
				}
			} else {
				return $this->result(1, "没有此订单");
			}
			$request["curr"] = 1;
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "search2":
		$content = $_GPC["content"];
		$request = array();
		$condition = '';
		if (!empty($_GPC["store"])) {
			$condition = " AND store=" . $_GPC["store"];
		}
		$request["list"] = pdo_fetch("SELECT * FROM " . tablename("xc_train_order") . " WHERE status=1 AND uniacid=:uniacid AND (out_trade_no LIKE '%{$content}%' OR name LIKE '%{$content}%') " . $condition, array(":uniacid" => $uniacid));
		if ($request["list"]) {
			$service = pdo_getall("xc_train_service_team", array("uniacid" => $uniacid, "id" => $request["list"]["pid"]));
			$request["list"]["start_time"] = $service["start_time"];
		} else {
			return $this->result(1, "没有此订单");
		}
		$request["curr"] = 1;
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "sign":
		$id = htmlspecialchars_decode($_GPC["id"]);
		$id = json_decode($id, true);
		if (!empty($id) && is_array($id)) {
			foreach ($id as $x) {
				$order = pdo_get("xc_train_order", array("status" => 1, "uniacid" => $uniacid, "id" => $x));
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
				$request = pdo_update("xc_train_order", $condition, array("status" => 1, "uniacid" => $uniacid, "id" => $x));
			}
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "核销失败");
		}
		break;
}