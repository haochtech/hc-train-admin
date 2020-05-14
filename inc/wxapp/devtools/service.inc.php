<?php

//decode by http://www.zx-xcx.com/
defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "service";
switch ($op) {
	case "service_class":
		$request = pdo_getall("xc_train_service_class", array("status" => 1, "uniacid" => $uniacid, "type" => $_GPC["type"]), array(), '', "sort DESC,createtime DESC");
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "service":
		$condition["uniacid"] = $uniacid;
		$condition["status"] = 1;
		if (!empty($_GPC["cid"])) {
			$condition["cid"] = $_GPC["cid"];
		}
		if (!empty($_GPC["tui"])) {
			$condition["tui"] = 1;
		}
		$request = pdo_getall("xc_train_service", $condition, array(), '', "sort DESC,createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			foreach ($request as &$x) {
				$x["bimg"] = tomedia($x["bimg"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "detail":
		$request = pdo_get("xc_train_service", array("uniacid" => $uniacid, "status" => 1, "id" => $_GPC["id"]));
		if ($request) {
			pdo_update("xc_train_service", array("click" => intval($request["click"]) + 1), array("uniacid" => $uniacid, "status" => 1, "id" => $_GPC["id"]));
			$request["bimg"] = tomedia($request["bimg"]);
			$request["click"] = intval($request["click"]) + 1;
			$request["content2"] = htmlspecialchars_decode(str_replace("src=&quot; ", "src=&quot;", $request["content2"]));
			if (!empty($request["teacher"])) {
				$request["teacher"] = json_decode($request["teacher"], true);
				$teacher = pdo_getall("xc_train_teacher", array("status" => 1, "uniacid" => $uniacid));
				$datalist = array();
				if ($teacher) {
					foreach ($teacher as $t) {
						$datalist[$t["id"]] = $t;
					}
				}
				foreach ($request["teacher"] as &$x) {
					$x["name"] = $datalist[$x["id"]]["name"];
					$x["simg"] = tomedia($datalist[$x["id"]]["simg"]);
				}
			}
			$request["team"] = array();
			$team = pdo_getall("xc_train_service_team", array("status" => 1, "pid" => $request["id"], "uniacid" => $uniacid), array(), '', "createtime DESC");
			if ($team) {
				foreach ($team as $key => $t) {
					if (time() > strtotime($t["end_time"])) {
						unset($team[$key]);
					}
					if (intval($t["member"]) >= intval($t["more_member"])) {
						unset($team[$key]);
					}
				}
				if (!empty($team)) {
					$request["team"] = $team;
				}
			}
			$request["is_zan"] = -1;
			if (!empty($_W["openid"])) {
				$zan = pdo_get("xc_train_zan", array("status" => 1, "openid" => $_W["openid"], "uniacid" => $uniacid, "cid" => $request["id"], "type" => 1));
				if ($zan) {
					$request["is_zan"] = 1;
				}
			}
			$request["share"] = -1;
			$config = pdo_get("xc_train_config", array("xkey" => "service_poster", "uniacid" => $uniacid));
			if ($config) {
				$config["content"] = json_decode($config["content"], true);
				if (!empty($config["content"]) && !empty($config["content"]["status"])) {
					$request["share"] = $config["content"]["status"];
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "news":
		$condition["uniacid"] = $uniacid;
		$condition["status"] = 1;
		$cid = array();
		if (!empty($_GPC["cid"])) {
			$condition["cid"] = $_GPC["cid"];
		}
		$request = pdo_getall("xc_train_news", $condition, array(), '', "sort DESC,id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			foreach ($request as &$x) {
				$x["simg"] = tomedia($x["simg"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "news_class":
		$request = pdo_get("xc_train_service_class", array("uniacid" => $uniacid, "status" => 1, "type" => 4, "id" => $_GPC["id"]));
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "分类不存在", array("redirect" => "../index/index"));
		}
		break;
	case "teacher":
		$condition["status"] = 1;
		$condition["uniacid"] = $uniacid;
		if (!empty($_GPC["cid"])) {
			$condition["cid"] = $_GPC["cid"];
		}
		$request = pdo_getall("xc_train_teacher", $condition, array(), '', "sort DESC,createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			foreach ($request as &$x) {
				$x["simg"] = tomedia($x["simg"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "teacher_detail":
		$request = pdo_get("xc_train_teacher", array("uniacid" => $uniacid, "id" => $_GPC["id"]));
		if ($request) {
			$request["simg"] = tomedia($request["simg"]);
			$request["is_student"] = -1;
			$request["is_zan"] = -1;
			$request["content2"] = htmlspecialchars_decode(str_replace("src=&quot; ", "src=&quot;", $request["content2"]));
			$request["member"] = array();
			$log = pdo_getall("xc_train_teacher_log", array("uniacid" => $uniacid, "tid" => $request["id"], "status" => 1), array(), '', "createtime DESC", array(1, 6));
			if ($log) {
				$userinfo = pdo_getall("xc_train_userinfo", array("status" => 1));
				$datalist = array();
				foreach ($userinfo as $u) {
					$datalist[$u["openid"]] = $u;
				}
				foreach ($log as &$l) {
					$l["avatar"] = $datalist[$l["openid"]]["avatar"];
					$l["nick"] = $datalist[$l["openid"]]["nick"];
				}
				$request["member"] = $log;
			}
			if (!empty($_W["openid"])) {
				$student = pdo_get("xc_train_teacher_log", array("uniacid" => $uniacid, "openid" => $_W["openid"], "tid" => $request["id"], "status" => 1));
				if ($student) {
					$request["is_student"] = 1;
				}
				$zan = pdo_get("xc_train_teacher_log", array("uniacid" => $uniacid, "openid" => $_W["openid"], "tid" => $request["id"], "status" => 2));
				if ($zan) {
					$request["is_zan"] = 1;
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "zan":
		$log = pdo_get("xc_train_teacher_log", array("uniacid" => $uniacid, "openid" => $_W["openid"], "tid" => $_GPC["id"], "status" => $_GPC["status"]));
		if (!$log) {
			$request = pdo_insert("xc_train_teacher_log", array("uniacid" => $uniacid, "openid" => $_W["openid"], "tid" => $_GPC["id"], "status" => $_GPC["status"]));
			if ($request) {
				$teacher = pdo_get("xc_train_teacher", array("uniacid" => $uniacid, "id" => $_GPC["id"]));
				if ($teacher) {
					if ($_GPC["status"] == 1) {
						pdo_update("xc_train_teacher", array("students" => intval($teacher["students"]) + 1), array("uniacid" => $uniacid, "id" => $_GPC["id"]));
					} elseif ($_GPC["status"] == 2) {
						pdo_update("xc_train_teacher", array("zan" => intval($teacher["zan"]) + 1), array("uniacid" => $uniacid, "id" => $_GPC["id"]));
					}
				}
				return $this->result(0, "操作成功", array("status" => 1));
			} else {
				return $this->result(1, "操作失败");
			}
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "list":
		$date = time();
		$page = ($_GPC["page"] - 1) * $_GPC["pagesize"];
		$pagesize = $_GPC["pagesize"];
		$sql = "SELECT * FROM " . tablename("xc_train_service_team") . " WHERE status=1 AND uniacid=:uniacid AND type=:type AND UNIX_TIMESTAMP(end_time)>:times AND more_member>member ORDER BY createtime DESC,id DESC LIMIT {$page},{$pagesize}";
		$request = pdo_fetchall($sql, array(":uniacid" => $uniacid, ":type" => $_GPC["curr"], ":times" => $date));
		if ($request) {
			$service = pdo_getall("xc_train_service", array("status" => 1, "uniacid" => $uniacid));
			$datalist = array();
			if ($service) {
				foreach ($service as $s) {
					$datalist[$s["id"]] = $s;
				}
			}
			foreach ($request as &$x) {
				$x["name"] = $datalist[$x["pid"]]["name"];
				$x["keshi"] = $datalist[$x["pid"]]["keshi"];
				$x["bimg"] = tomedia($datalist[$x["pid"]]["bimg"]);
				$x["price"] = $datalist[$x["pid"]]["price"];
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "detail_zan":
		$zan = pdo_get("xc_train_zan", array("status" => 1, "openid" => $_W["openid"], "cid" => $_GPC["id"], "uniacid" => $uniacid, "type" => 1));
		if ($zan) {
			return $this->result(0, "已点赞");
		} else {
			$request = pdo_insert("xc_train_zan", array("openid" => $_W["openid"], "uniacid" => $uniacid, "cid" => $_GPC["id"], "status" => 1, "type" => 1));
			if ($request) {
				$service = pdo_get("xc_train_service", array("status" => 1, "id" => $_GPC["id"], "uniacid" => $uniacid));
				pdo_update("xc_train_service", array("zan" => $service["zan"] + 1), array("status" => 1, "id" => $_GPC["id"], "uniacid" => $uniacid));
				return $this->result(0, "操作成功", array("status" => 1));
			} else {
				return $this->result(0, "操作失败");
			}
		}
		break;
	case "video_class":
		$request = pdo_getall("xc_train_video_class", array("status" => 1, "uniacid" => $uniacid, "type" => 1), array(), '', "sort DESC,createtime DESC");
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "video":
		$condition["status"] = 1;
		$condition["uniacid"] = $uniacid;
		if (!empty($_GPC["cid"])) {
			$condition["cid"] = $_GPC["cid"];
		}
		if (!empty($_GPC["id"])) {
			$condition["id !="] = $_GPC["id"];
		}
		if (!empty($_GPC["service"])) {
			$condition["pid"] = $_GPC["service"];
		}
		$request = pdo_getall("xc_train_video", $condition, array(), '', "sort DESC,createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			$teacher = pdo_getall("xc_train_teacher", array("uniacid" => $uniacid));
			$datalist = array();
			if ($teacher) {
				foreach ($teacher as $t) {
					$datalist[$t["id"]] = $t;
				}
			}
			foreach ($request as &$x) {
				$x["video"] = tomedia($x["video"]);
				$x["bimg"] = tomedia($x["bimg"]);
				$x["zan"] = $datalist[$x["teacher_id"]]["zan"];
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "video_detail":
		$sql = "UPDATE " . tablename("xc_train_video") . " SET click=click+1 WHERE id=:id AND uniacid=:uniacid";
		pdo_query($sql, array(":id" => $_GPC["id"], ":uniacid" => $uniacid));
		$request = pdo_get("xc_train_video", array("status" => 1, "id" => $_GPC["id"], "uniacid" => $uniacid));
		if ($request) {
			if ($request["type"] == 3) {
				$request["link"] = xc_txvideoUrl($request["link"], 1);
			} elseif ($request["type"] == 4) {
				$request["link"] = xc_txvideoUrl($request["link"], 2);
			}
			$request["is_buy"] = -1;
			$request["video"] = tomedia($request["video"]);
			$request["bimg"] = tomedia($request["bimg"]);
			$teacher = pdo_get("xc_train_teacher", array("id" => $request["teacher_id"], "uniacid" => $uniacid));
			if ($teacher) {
				$request["zan"] = $teacher["zan"];
			}
			$service = pdo_get("xc_train_service", array("id" => $request["pid"], "uniacid" => $uniacid));
			if ($service) {
				$request["service_name"] = $service["name"];
				$request["content"] = $service["content"];
				$request["content_type"] = $service["content_type"];
				$request["content2"] = htmlspecialchars_decode(str_replace("src=&quot; ", "src=&quot;", $service["content2"]));
			}
			$service_team = pdo_getall("xc_train_service_team", array("pid" => $request["pid"], "uniacid" => $uniacid));
			if ($service_team) {
				$pid = array();
				foreach ($service_team as $s) {
					$pid[] = $s["id"];
					$service_order = pdo_get("xc_train_order", array("status" => 1, "uniacid" => $uniacid, "pid IN" => $pid, "openid" => $_W["openid"], "order_type IN" => array(1, 2)));
					if ($service_order) {
						$request["is_buy"] = 1;
					}
				}
			}
			$cut_list = pdo_getall("xc_train_cut", array("pid" => $request["pid"], "uniacid" => $uniacid));
			if ($cut_list) {
				$pid = array();
				foreach ($cut_list as $cl) {
					$pid[] = $cl["id"];
					$service_order = pdo_get("xc_train_order", array("status" => 1, "uniacid" => $uniacid, "pid IN" => $pid, "openid" => $_W["openid"], "order_type IN" => array(1, 2)));
					if ($service_order) {
						$request["is_buy"] = 1;
					}
				}
			}
			if ($request["is_buy"] == -1) {
				if ($request["price"] == 0) {
					$request["is_buy"] = 1;
				} else {
					$order = pdo_get("xc_train_order", array("status" => 1, "uniacid" => $uniacid, "pid" => $request["id"], "openid" => $_W["openid"], "order_type" => 3));
					if ($order) {
						$request["is_buy"] = 1;
					}
				}
			}
			if ($request["is_buy"] == -1) {
				$line_order = pdo_get("xc_train_line_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "type" => 1, "pid" => $request["id"]));
				if ($line_order) {
					$request["is_buy"] = 1;
				}
			}
			$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
			if ($user && ($user["shop"] == 1 || $user["shop"] == 2)) {
				$request["is_buy"] = 1;
			}
			$history = pdo_get("xc_train_history", array("uniacid" => $uniacid, "openid" => $_W["openid"], "pid" => $_GPC["id"], "type" => 2));
			if ($history) {
				pdo_update("xc_train_history", array("status" => 1, "createtime" => date("Y-m-d H:i:s")), array("uniacid" => $uniacid, "id" => $history["id"]));
			} else {
				pdo_insert("xc_train_history", array("uniacid" => $uniacid, "openid" => $_W["openid"], "pid" => $_GPC["id"], "status" => 1, "type" => 2, "createtime" => date("Y-m-d H:i:s")));
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "sign":
		$service = pdo_getall("xc_train_service", array("status" => 1, "price !=" => '', "uniacid" => $uniacid));
		if ($service) {
			$pid = array();
			$datalist = array();
			foreach ($service as $s) {
				$pid[] = $s["id"];
				$datalist[$s["id"]] = $s;
			}
			$date = time();
			$sql = "SELECT * FROM " . tablename("xc_train_service_team") . " WHERE status=1 AND uniacid=:uniacid AND UNIX_TIMESTAMP(end_time)>:times AND more_member>member ORDER BY createtime DESC,id DESC";
			$team = pdo_fetchall($sql, array(":uniacid" => $uniacid, ":times" => $date));
			if ($team) {
				$id = array();
				foreach ($team as $t) {
					$id[] = $t["id"];
				}
				$request = pdo_getall("xc_train_service_team", array("status" => 1, "uniacid" => $uniacid, "id IN" => $id, "pid IN" => $pid), array(), '', "createtime DESC,id DESC", array($_GPC["page"], $_GPC["pagesize"]));
				if ($request) {
					foreach ($request as &$x) {
						$x["name"] = $datalist[$x["pid"]]["name"];
					}
					return $this->result(0, "操作成功", $request);
				} else {
					return $this->result(0, "操作失败");
				}
			} else {
				return $this->result(0, "操作失败");
			}
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "cut":
		$service = pdo_getall("xc_train_service", array("status" => 1, "uniacid" => $uniacid));
		if ($service) {
			$service_list = array();
			$ids = array();
			foreach ($service as $s) {
				$service_list[$s["id"]] = $s;
				$ids[] = $s["id"];
			}
			$request = pdo_getall("xc_train_cut", array("status" => 1, "pid IN" => $ids, "uniacid" => $uniacid), array(), '', "sort DESC,id DESC", array($_GPC["page"], $_GPC["pagesize"]));
			if ($request) {
				foreach ($request as &$x) {
					$x["bimg"] = tomedia($service_list[$x["pid"]]["bimg"]);
					$x["name"] = $service_list[$x["pid"]]["name"];
					$x["end"] = -1;
					if (time() > strtotime($x["end_time"])) {
						$x["end"] = 1;
					}
					if ($x["is_member"] >= $x["member"]) {
						$x["end"] = 1;
					}
				}
				return $this->result(0, "操作成功", $request);
			} else {
				return $this->result(0, "操作失败");
			}
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "cut_detail":
		$request = pdo_get("xc_train_cut", array("id" => $_GPC["id"], "uniacid" => $uniacid));
		if ($request) {
			$request["is_member"] = intval($request["is_member"]);
			$request["member"] = intval($request["member"]);
			$request["end"] = -1;
			$request["fail"] = 0;
			if (time() < strtotime($request["end_time"])) {
				$request["fail"] = strtotime($request["end_time"]) - time();
			} else {
				$request["end"] = 1;
			}
			if ($request["is_member"] >= $request["member"]) {
				$request["end"] = 1;
			}
			$service = pdo_get("xc_train_service", array("id" => $request["pid"], "uniacid" => $uniacid));
			if ($service) {
				$request["bimg"] = tomedia($service["bimg"]);
				$request["name"] = $service["name"];
				$request["content_type"] = $service["content_type"];
				$request["content"] = $service["content"];
				$request["content2"] = htmlspecialchars_decode(str_replace("src=&quot; ", "src=&quot;", $service["content2"]));
				if (!empty($service["teacher"])) {
					$service["teacher"] = json_decode($service["teacher"], true);
					$teacher = pdo_getall("xc_train_teacher", array("status" => 1, "uniacid" => $uniacid));
					$datalist = array();
					if ($teacher) {
						foreach ($teacher as $t) {
							$datalist[$t["id"]] = $t;
						}
					}
					foreach ($service["teacher"] as &$x) {
						$x["name"] = $datalist[$x["id"]]["name"];
						$x["simg"] = tomedia($datalist[$x["id"]]["simg"]);
					}
					$request["teacher"] = $service["teacher"];
				}
			}
			$userinfo = pdo_get("xc_train_userinfo", array("openid" => $_W["openid"], "uniacid" => $uniacid));
			if ($userinfo) {
				$request["userinfo"] = $userinfo;
			}
			$order = pdo_get("xc_train_cut_order", array("openid" => $_W["openid"], "cid" => $_GPC["id"], "uniacid" => $uniacid));
			if ($order) {
				$order["pro"] = 0;
				if (floatval($request["price"]) != 0) {
					$order["pro"] = ($request["price"] - $order["price"]) / ($request["price"] - $request["cut_price"]) * 100;
				}
				$request["order"] = $order;
			}
			$request["bang"] = array();
			if (!empty($request["link"])) {
				$request["link"] = json_decode($request["link"], true);
				foreach ($request["link"] as &$ll) {
					$ll["simg"] = tomedia($ll["simg"]);
				}
				$request["bang"] = $request["link"];
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "cut_price":
		if (!empty($_W["openid"])) {
			$sql = "SELECT * FROM " . tablename("xc_train_cut") . " WHERE status=1 AND uniacid=:uniacid AND id=:id AND is_member<member AND unix_timestamp(end_time)>:times";
			$service = pdo_fetch($sql, array(":uniacid" => $uniacid, ":id" => $_GPC["id"], ":times" => time()));
			if ($service) {
				$price = $service["price"];
				$num = rand(floatval($service["min_price"]) * 100, floatval($service["max_price"]) * 100);
				$cut_price = $num / 100;
				$price = floatval($price) - $cut_price;
				if ($price <= floatval($service["cut_price"])) {
					$cut_price = floatval($service["price"]) - floatval($service["cut_price"]);
					$price = $service["cut_price"];
					$condition["is_min"] = 1;
				}
				$condition["uniacid"] = $uniacid;
				$condition["openid"] = $_W["openid"];
				$condition["cid"] = $service["id"];
				$condition["price"] = $price;
				$request = pdo_insert("xc_train_cut_order", $condition);
				if ($request) {
					pdo_insert("xc_train_cut_log", array("uniacid" => $uniacid, "openid" => $_W["openid"], "cid" => $service["id"], "price" => $cut_price, "cut_openid" => $_W["openid"]));
					$sql = "UPDATE " . tablename("xc_train_cut") . " SET join_member=join_member+:member WHERE status=1 AND id=:id AND uniacid=:uniacid";
					pdo_query($sql, array(":member" => 1, ":id" => $_GPC["id"], ":uniacid" => $uniacid));
					return $this->result(0, "操作成功", array("cut_price" => $cut_price));
				} else {
					return $this->result(1, "失败");
				}
			} else {
				return $this->result(1, "已结束");
			}
		} else {
			return $this->result(1, "请先登录");
		}
		break;
	case "cut_log":
		$request = pdo_getall("xc_train_cut_log", array("openid" => $_W["openid"], "cid" => $_GPC["id"], "uniacid" => $uniacid), array(), '', "id DESC");
		if ($request) {
			$user = pdo_getall("xc_train_userinfo", array("uniacid" => $uniacid));
			$user_list = array();
			if ($user) {
				foreach ($user as $u) {
					$u["nick"] = base64_decode($u["nick"]);
					$user_list[$u["openid"]] = $u;
				}
			}
			foreach ($request as &$x) {
				$x["nick"] = $user_list[$x["cut_openid"]]["nick"];
				$x["avatar"] = $user_list[$x["cut_openid"]]["avatar"];
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "cut_price2":
		if (!empty($_W["openid"])) {
			$sql = "SELECT * FROM " . tablename("xc_train_cut") . " WHERE status=1 AND uniacid=:uniacid AND id=:id AND is_member<member AND unix_timestamp(end_time)>:times";
			$service = pdo_fetch($sql, array(":uniacid" => $uniacid, ":id" => $_GPC["id"], ":times" => time()));
			if ($service) {
				$openid = '';
				if (!empty($_GPC["openid"])) {
					$openid = $_GPC["openid"];
				}
				if (!empty($_GPC["user_id"])) {
					$cut_user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "id" => $_GPC["user_id"]));
					if ($cut_user) {
						$openid = $cut_user["openid"];
					}
				}
				if ($openid != $_W["openid"]) {
					$order = pdo_get("xc_train_cut_order", array("openid" => $openid, "cid" => $_GPC["id"], "status" => -1, "is_min" => -1, "uniacid" => $uniacid));
					if ($order) {
						$cut_log = pdo_get("xc_train_cut_log", array("openid" => $openid, "cid" => $_GPC["id"], "uniacid" => $uniacid, "cut_openid" => $_W["openid"]));
						if ($cut_log) {
							return $this->result(1, "已助力");
						} else {
							$price = $order["price"];
							$num = rand(floatval($service["min_price"]) * 100, floatval($service["max_price"]) * 100);
							$cut_price = $num / 100;
							$price = floatval($price) - $cut_price;
							if ($price <= floatval($service["cut_price"])) {
								$cut_price = floatval($order["price"]) - floatval($service["cut_price"]);
								$price = $service["cut_price"];
								$condition["is_min"] = 1;
							}
							$condition["price"] = $price;
							$request = pdo_update("xc_train_cut_order", $condition, array("openid" => $openid, "cid" => $_GPC["id"], "uniacid" => $uniacid));
							if ($request) {
								pdo_insert("xc_train_cut_log", array("uniacid" => $uniacid, "openid" => $openid, "cid" => $service["id"], "price" => $cut_price, "cut_openid" => $_W["openid"]));
								$user = pdo_get("xc_train_userinfo", array("openid" => $openid, "uniacid" => $uniacid));
								if ($user) {
									$user["nick"] = base64_decode($user["nick"]);
								}
								return $this->result(0, "操作成功", array("cut_price" => $cut_price, "cut_user" => $user["nick"]));
							} else {
								return $this->result(1, "失败");
							}
						}
					} else {
						return $this->result(0, "失败");
					}
				} else {
					return $this->result(0, "自己不能助力");
				}
			} else {
				return $this->result(1, "已结束");
			}
		} else {
			return $this->result(1, "请先登录");
		}
		break;
	case "cut_user":
		$service = pdo_getall("xc_train_service", array("status" => 1, "uniacid" => $uniacid));
		if ($service) {
			$service_list = array();
			$ids = array();
			foreach ($service as $s) {
				$service_list[$s["id"]] = $s;
				$ids[] = $s["id"];
			}
			$order = pdo_getall("xc_train_cut_order", array("openid" => $_W["openid"], "uniacid" => $uniacid));
			if ($order) {
				$order_list = array();
				$idt = array();
				foreach ($order as &$o) {
					$order_list[$o["cid"]] = $o;
					$idt[] = $o["cid"];
				}
				$request = pdo_getall("xc_train_cut", array("status" => 1, "pid IN" => $ids, "uniacid" => $uniacid, "id IN" => $idt), array(), '', "sort DESC,id DESC", array($_GPC["page"], $_GPC["pagesize"]));
				if ($request) {
					foreach ($request as &$x) {
						$x["bimg"] = tomedia($service_list[$x["pid"]]["bimg"]);
						$x["name"] = $service_list[$x["pid"]]["name"];
						$x["end"] = -1;
						$x["hour"] = 0;
						$x["min"] = 0;
						$x["second"] = 0;
						$x["fail"] = 0;
						if (time() > strtotime($x["end_time"])) {
							$x["end"] = 1;
						} else {
							$x["fail"] = strtotime($x["end_time"]) - time();
						}
						if ($x["is_member"] >= $x["member"]) {
							$x["end"] = 1;
						}
						$x["order"] = $order_list[$x["id"]];
					}
					return $this->result(0, "操作成功", $request);
				} else {
					return $this->result(0, "操作失败");
				}
			} else {
				return $this->result(0, "操作失败");
			}
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "mall":
		$request = array();
		if ($_GPC["type"] == 4) {
			$pagesize = intval($_GPC["pagesize"]);
			$page = (intval($_GPC["page"]) - 1) * $pagesize;
			$where = " a.uniacid=:uniacid AND a.status=1 AND a.kucun>0 AND unix_timestamp(a.start_time)<:times AND unix_timestamp(a.end_time)>:times AND b.status=1 ";
			$condition = array(":uniacid" => $_W["uniacid"], ":times" => time());
			if (!empty($_GPC["cid"])) {
				$where .= " AND b.cid=:cid ";
				$condition[":cid"] = $_GPC["cid"];
			}
			$sql = "SELECT a.*,b.price,b.format FROM " . tablename($xcmodule . "_mall_team") . " a left join " . tablename($xcmodule . "_mall") . " b on a.uniacid=b.uniacid AND a.service=b.id WHERE {$where} ORDER BY sort DESC,id DESC LIMIT {$page},{$pagesize} ";
			$request = pdo_fetchall($sql, $condition);
		} else {
			$condition["status"] = 1;
			$condition["uniacid"] = $uniacid;
			if (!empty($_GPC["cid"])) {
				$condition["cid"] = $_GPC["cid"];
			}
			if (!empty($_GPC["type"])) {
				$condition["type"] = $_GPC["type"];
			}
			$request = pdo_getall("xc_train_mall", $condition, array(), '', "sort DESC,id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		}
		if ($request) {
			foreach ($request as &$x) {
				$x["simg"] = tomedia($x["simg"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "mall_detail":
		$user = pdo_getall("xc_train_userinfo");
		$datalist = array();
		if ($user) {
			foreach ($user as $uu) {
				$datalist[$uu["openid"]] = $uu;
			}
		}
		$request = pdo_get("xc_train_mall", array("status" => 1, "id" => $_GPC["id"], "uniacid" => $uniacid));
		if ($request) {
			if ($request["type"] == 3) {
				if (strtotime($request["start_time"]) < time() && time() < strtotime($request["end_time"])) {
					$request["failtime"] = strtotime($request["end_time"]) - time();
				} else {
					$request["type"] = 1;
				}
			}
			$request["simg"] = tomedia($request["simg"]);
			$request["bimg"] = explode(",", $request["bimg"]);
			if (!empty($request["bimg"]) && is_array($request["bimg"])) {
				foreach ($request["bimg"] as &$bb) {
					$bb = tomedia($bb);
				}
			}
			$request["format"] = json_decode($request["format"], true);
			if (!empty($_GPC["member"])) {
				$amount = 0;
				if ($_GPC["format"] == -1) {
					$amount = floatval($request["price"]) * intval($_GPC["member"]);
				} else {
					if ($request["type"] == 1 || $request["type"] == 2) {
						if (!empty($_GPC["group"]) && $request["type"] == 2) {
							$amount = floatval($request["format"][$_GPC["format"]]["group_price"]) * intval($_GPC["member"]);
						} else {
							$amount = floatval($request["format"][$_GPC["format"]]["price"]) * intval($_GPC["member"]);
						}
					} else {
						if ($request["type"] == 3) {
							$amount = floatval($request["format"][$_GPC["format"]]["limit_price"]) * intval($_GPC["member"]);
						}
					}
				}
				$request["coupon"] = array();
				$coupon = pdo_getall("xc_train_user_coupon", array("status" => -1, "uniacid" => $uniacid, "openid" => $_W["openid"]));
				if ($coupon) {
					$coupon_id = array();
					foreach ($coupon as $c) {
						$coupon_id[] = $c["cid"];
					}
					$user_coupon = pdo_getall("xc_train_coupon", array("status" => 1, "condition <=" => $amount, "id IN" => $coupon_id, "uniacid" => $uniacid));
					if ($user_coupon) {
						foreach ($user_coupon as $key => $uc) {
							$uc["times"] = json_decode($uc["times"], true);
							if (time() > strtotime($uc["times"]["start"]) && time() < strtotime($uc["times"]["end"])) {
								$user_coupon[$key]["times"] = $uc["times"];
							} else {
								unset($user_coupon[$key]);
							}
							if (floatval($uc["condition"]) > floatval($amount)) {
								unset($user_coupon[$key]);
							}
						}
					}
					$request["coupon"] = $user_coupon;
				}
				$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
				if ($config) {
					$config["content"] = json_decode($config["content"], true);
					if (!empty($config["content"]["fee"])) {
						$request["fee"] = $config["content"]["fee"];
					}
				}
			}
			if (!empty($_GPC["longitude"]) && !empty($_GPC["latitude"])) {
				$sql = "SELECT *,ROUND(6378.138*2*ASIN(SQRT(POW(SIN((:xlatitude*PI()/180-latitude*PI()/180)/2),2)+COS(:xlatitude*PI()/180)*COS(latitude*PI()/180)*POW(SIN((:xlongitude*PI()/180-longitude*PI()/180)/2),2))),2) AS juli FROM " . tablename("xc_train_school") . " WHERE status=1 AND uniacid=:uniacid ORDER BY juli,sort DESC,createtime DESC";
				$store = pdo_fetchall($sql, array(":xlatitude" => $_GPC["latitude"], ":xlongitude" => $_GPC["longitude"], ":uniacid" => $uniacid));
			} else {
				$store = pdo_getall("xc_train_school", array("uniacid" => $uniacid, "status" => 1), array(), '', "sort DESC,id DESC");
			}
			if ($store) {
				foreach ($store as &$x) {
					$x["simg"] = tomedia($x["simg"]);
				}
				$request["store"] = $store;
			}
			if ($request["type"] == 2) {
				$group_order = pdo_getall("xc_train_group", array("uniacid" => $uniacid, "status" => -1, "service" => $_GPC["id"], "failtime >" => date("Y-m-d H;i:s")));
				if ($group_order) {
					foreach ($group_order as &$go) {
						$go["fail"] = strtotime($go["failtime"]) - time();
						$go["join"] = 1;
						$go["group"] = json_decode($go["group"], true);
						$go["list"] = array();
						foreach ($go["group"] as $goo) {
							if ($goo == $_W["openid"]) {
								$go["join"] = -1;
							}
							$go["list"][] = array("openid" => $goo, "nick" => base64_decode($datalist[$goo]["nick"]), "avatar" => $datalist[$goo]["avatar"]);
						}
						$go["u_list"] = array();
						if ($go["is_member"] < $go["member"]) {
							for ($i = 0; $i < $go["member"] - $go["is_member"]; $i++) {
								$go["u_list"][] = array();
							}
						}
						$go["fail"] = strtotime($go["failtime"]) - time();
					}
					$request["group_order"] = $group_order;
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "address":
		$request = pdo_get("xc_train_address", array("uniacid" => $uniacid, "status" => 1, "openid" => $_W["openid"]));
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "audio_class":
		$request = pdo_getall("xc_train_video_class", array("status" => 1, "uniacid" => $uniacid, "type" => 2), array(), '', "sort DESC,createtime DESC");
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "audio":
		$condition["uniacid"] = $uniacid;
		$condition["status"] = 1;
		if (!empty($_GPC["cid"])) {
			$condition["cid"] = $_GPC["cid"];
		}
		$request = pdo_getall("xc_train_audio", $condition, array(), '', "sort DESC,id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			foreach ($request as &$x) {
				$x["simg"] = tomedia($x["simg"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "audio_detail":
		$request = pdo_get("xc_train_audio", array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
		if ($request) {
			$request["simg"] = tomedia($request["simg"]);
			$request["is_buy"] = -1;
			if (!empty($request["price"])) {
				$order = pdo_get("xc_train_order", array("uniacid" => $uniacid, "openid" => $_W["openid"], "order_type" => 5, "pid" => $_GPC["id"], "status" => 1));
				if ($order) {
					$request["is_buy"] = 1;
				}
			} else {
				$request["is_buy"] = 1;
			}
			if ($request["is_buy"] == -1) {
				$line_order = pdo_get("xc_train_line_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "type" => 2, "pid" => $request["id"]));
				if ($line_order) {
					$request["is_buy"] = 1;
				}
			}
			$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
			if ($user && ($user["shop"] == 1 || $user["shop"] == 2)) {
				$request["is_buy"] = 1;
			}
			$request["audio_num"] = 0;
			$request["discuss_num"] = 0;
			$sql = "SELECT count(*) FROM " . tablename("xc_train_discuss") . " WHERE uniacid=:uniacid AND pid=:pid AND status=1 AND `type`=3 ";
			$num = pdo_fetchcolumn($sql, array(":uniacid" => $uniacid, ":pid" => $_GPC["id"]));
			if ($num) {
				$request["discuss_num"] = $num;
			}
			$audio = pdo_getall("xc_train_audio_item", array("uniacid" => $uniacid, "pid" => $_GPC["id"], "status" => 1), array(), '', "sort DESC,id DESC");
			if ($audio) {
				foreach ($audio as &$a) {
					$a["audio"] = tomedia($a["audio"]);
					$a["simg"] = tomedia($a["simg"]);
				}
				$request["audio_list"] = $audio;
				$request["audio_num"] = count($audio);
			}
			$history = pdo_get("xc_train_history", array("uniacid" => $uniacid, "openid" => $_W["openid"], "pid" => $_GPC["id"], "type" => 1));
			if ($history) {
				pdo_update("xc_train_history", array("status" => 1, "createtime" => date("Y-m-d H:i:s")), array("uniacid" => $uniacid, "id" => $history["id"]));
			} else {
				pdo_insert("xc_train_history", array("uniacid" => $uniacid, "openid" => $_W["openid"], "pid" => $_GPC["id"], "status" => 1, "type" => 1, "createtime" => date("Y-m-d H:i:s")));
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "课程已下架");
		}
		break;
	case "audio_item":
		pdo_update("xc_train_audio_item", array("click +=" => 1), array("status" => 1, "id" => $_GPC["id"], "uniacid" => $uniacid));
		$request = pdo_get("xc_train_audio_item", array("status" => 1, "id" => $_GPC["id"], "uniacid" => $uniacid));
		if ($request) {
			$request["audio"] = tomedia($request["audio"]);
			$request["simg"] = tomedia($request["simg"]);
			$list = pdo_get("xc_train_audio", array("status" => 1, "id" => $request["pid"], "uniacid" => $uniacid));
			if ($list) {
				$list["simg"] = tomedia($list["simg"]);
				$list["is_buy"] = -1;
				if (!empty($list["price"])) {
					$order = pdo_get("xc_train_order", array("uniacid" => $uniacid, "openid" => $_W["openid"], "order_type" => 5, "pid" => $list["id"], "status" => 1));
					if ($order) {
						$list["is_buy"] = 1;
					}
				} else {
					$list["is_buy"] = 1;
				}
				if ($list["is_buy"] == -1) {
					$line_order = pdo_get("xc_train_line_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "type" => 2, "pid" => $list["id"]));
					if ($line_order) {
						$list["is_buy"] = 1;
					}
				}
				$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
				if ($user && ($user["shop"] == 1 || $user["shop"] == 2)) {
					$list["is_buy"] = 1;
				}
				$audio = pdo_getall("xc_train_audio_item", array("uniacid" => $uniacid, "pid" => $list["id"], "status" => 1), array(), '', "sort DESC,id DESC");
				$curr = 0;
				if ($audio) {
					foreach ($audio as &$a) {
						$a["audio"] = tomedia($a["audio"]);
						$a["simg"] = tomedia($a["simg"]);
						if (empty($request["curr"])) {
							if ($a["id"] == $_GPC["id"]) {
								$request["curr"] = $curr;
							} else {
								$curr = $curr + 1;
							}
						}
					}
					$list["audio_list"] = $audio;
				}
				$list["is_mark"] = -1;
				$mark = pdo_get("xc_train_mark", array("uniacid" => $uniacid, "openid" => $_W["openid"], "pid" => $list["id"], "status" => 1));
				if ($mark) {
					$list["is_mark"] = 1;
				}
				$request["list"] = $list;
				$request["can_share"] = -1;
				$config = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "audio_poster"));
				if ($config) {
					$config["content"] = json_decode($config["content"], true);
					if (!empty($config["content"]) && $config["content"]["status"] == 1) {
						$request["can_share"] = 1;
					}
				}
				return $this->result(0, "操作成功", $request);
			} else {
				return $this->result(1, "课程已下架");
			}
		} else {
			return $this->result(1, "课程已下架");
		}
		break;
	case "mark":
		$mark = pdo_get("xc_train_mark", array("uniacid" => $uniacid, "openid" => $_W["openid"], "pid" => $_GPC["id"]));
		$status = -1;
		if ($mark) {
			$status = -$mark["status"];
			$request = pdo_update("xc_train_mark", array("status" => -$mark["status"], "createtime" => date("Y-m-d H:i:s")), array("id" => $mark["id"], "uniacid" => $uniacid));
		} else {
			$status = 1;
			$request = pdo_insert("xc_train_mark", array("uniacid" => $uniacid, "openid" => $_W["openid"], "pid" => $_GPC["id"], "status" => 1, "createtime" => date("Y-m-d H:i:s")));
		}
		if ($request) {
			$sql = "UPDATE " . tablename("xc_train_audio") . " SET mark=mark+:mark WHERE id=:id AND uniacid=:uniacid";
			pdo_query($sql, array(":mark" => $status, ":id" => $_GPC["id"], ":uniacid" => $uniacid));
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "history":
		$request = pdo_update("xc_train_history", array("status" => -1), array("id" => $_GPC["id"], "uniacid" => $uniacid, "type" => $_GPC["type"]));
		if ($request) {
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "group":
		$request = pdo_getall($xcmodule . "_service_group", array("status" => 1, "uniacid" => $_W["uniacid"]), array(), '', "sort DESC,id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			$service = pdo_getall($xcmodule . "_service", array("status" => 1, "uniacid" => $_W["uniacid"]));
			$service_data = array();
			if ($service) {
				foreach ($service as $s) {
					$service_data[$s["id"]] = $s;
				}
			}
			foreach ($request as &$x) {
				$x["simg"] = tomedia($x["simg"]);
				if (!empty($service_data[$x["pid"]])) {
					$x["service_name"] = $service_data[$x["pid"]]["name"];
				}
				if (!empty($x["format"])) {
					$x["format"] = json_decode($x["format"], true);
				}
				if (intval($x["member_on"]) >= intval($x["member"])) {
					$x["status"] = 2;
				}
				if (time() > strtotime($x["end_time"])) {
					$x["status"] = 2;
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "group_detail":
		pdo_update($xcmodule . "_service_group", array("click +=" => 1), array("status" => 1, "uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
		$request = pdo_get($xcmodule . "_service_group", array("status" => 1, "uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
		if ($request) {
			$service = pdo_get($xcmodule . "_service", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $request["pid"]));
			if ($service) {
				$request["service_name"] = $service["name"];
			}
			if (intval($request["member_on"]) >= intval($request["member"])) {
				$request["status"] = 2;
			}
			if (time() > strtotime($request["end_time"])) {
				$request["status"] = 2;
			}
			if (!empty($request["bimg"])) {
				$request["bimg"] = explode(",", $request["bimg"]);
				foreach ($request["bimg"] as &$b) {
					$b = tomedia($b);
				}
			}
			if (!empty($request["format"])) {
				$request["format"] = json_decode($request["format"], true);
			}
			$zan = pdo_get($xcmodule . "_zan", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "cid" => $_GPC["id"], "status" => 1, "type" => 3));
			if ($zan) {
				$request["zan_user"] = 1;
			} else {
				$request["zan_user"] = -1;
			}
			$request["zan"] = 0;
			$zan = pdo_count($xcmodule . "_zan", array("uniacid" => $_W["uniacid"], "cid" => $_GPC["id"], "status" => 1, "type" => 3));
			if ($zan) {
				$request["zan"] = $zan;
			}
			$group = pdo_getall($xcmodule . "_group_service", array("uniacid" => $_W["uniacid"], "status" => -1, "service" => $request["id"]), array(), '', "id DESC");
			if ($group) {
				$ids = array();
				foreach ($group as $gg) {
					$ids[] = $gg["openid"];
				}
				$user = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid IN" => $ids));
				$user_data = array();
				if ($user) {
					foreach ($user as $u) {
						$user_data[$u["openid"]] = $u;
					}
				}
				foreach ($group as &$g) {
					$g["avatar"] = $user_data[$g["openid"]]["avatar"];
					$g["fail"] = strtotime($g["failtime"]) - time();
					$g["hour"] = 0;
					$g["min"] = 0;
					$g["second"] = 0;
				}
				$request["group_list"] = $group;
			}
			$request["discuss"] = 0;
			$discuss = pdo_count($xcmodule . "_discuss", array("uniacid" => $_W["uniacid"], "pid" => $_GPC["id"], "status" => 1, "type" => 5));
			if ($discuss) {
				$request["discuss"] = $discuss;
			}
			$request["order"] = -1;
			$order = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "pid" => $_GPC["id"], "order_type" => 7, "status" => 1, "group_status" => 1));
			if ($order) {
				$request["order"] = 1;
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "已下架", array("redirect" => "../../index/index"));
		}
		break;
	case "group_zan":
		$request = pdo_insert($xcmodule . "_zan", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "cid" => $_GPC["id"], "status" => 1, "type" => 3, "createtime" => date("Y-m-d H:i:s")));
		if ($request) {
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "group_order":
		$condition["uniacid"] = $_W["uniacid"];
		$condition["openid"] = $_W["openid"];
		$condition["order_type"] = 7;
		if ($_GPC["curr"] == 1) {
			$condition["status"] = 1;
			$condition["group_status"] = -1;
		} elseif ($_GPC["curr"] == 2) {
			$condition["status"] = 1;
			$condition["group_status"] = 1;
			$condition["order_status"] = -1;
		} elseif ($_GPC["curr"] == 3) {
			$condition["status"] = 1;
			$condition["group_status"] = 1;
			$condition["order_status"] = 1;
		} elseif ($_GPC["curr"] == 4) {
			$condition["status"] = 2;
		} else {
			$condition["status IN"] = array(1, 2);
		}
		$request = pdo_getall($xcmodule . "_order", $condition, array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			$ids = array();
			foreach ($request as $xx) {
				$ids[] = $xx["group_id"];
			}
			$order = pdo_getall($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "status IN" => array(1, 2), "order_type" => 7, "group_id IN" => $ids), array(), '', "id asc");
			$order_data = array();
			if ($order) {
				$openid = array();
				foreach ($order as $oo) {
					$openid[] = $oo["openid"];
				}
				$user = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid IN" => $openid));
				$user_data = array();
				if ($user) {
					foreach ($user as $u) {
						$user_data[$u["openid"]] = $u;
					}
				}
				foreach ($order as &$o) {
					$o["avatar"] = $user_data[$o["openid"]]["avatar"];
					$order_data[$o["group_id"]][] = $o;
				}
			}
			$group = pdo_getall($xcmodule . "_group_service", array("uniacid" => $_W["uniacid"], "id IN" => $ids));
			$group_data = array();
			if ($group) {
				foreach ($group as $g) {
					$g["empty"] = array();
					if ($g["status"] != 1) {
						for ($i = 0; $i < $g["member"] - $g["member_on"]; $i++) {
							$g["empty"][] = $i;
						}
					}
					$g["order"] = $order_data[$g["id"]];
					$group_data[$g["id"]] = $g;
				}
			}
			foreach ($request as &$x) {
				if ($x["status"] == 1 && $x["group_status"] == -1) {
					if (time() > strtotime($x["group_end"])) {
						$x["status"] = 2;
						$x["group_status"] = 2;
					} else {
						$x["fail"] = strtotime($x["group_end"]) - time();
						$x["hour"] = 0;
						$x["min"] = 0;
						$x["second"] = 0;
					}
				}
				if (!empty($group_data[$x["group_id"]])) {
					$x["group_list"] = $group_data[$x["group_id"]];
				}
				if (!empty($x["group_data"])) {
					$x["group_data"] = json_decode($x["group_data"], true);
					$x["group_data"]["simg"] = tomedia($x["group_data"]["simg"]);
				}
				if (!empty($x["check_code"])) {
					$x["check_code"] = tomedia($x["check_code"]);
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "group_share":
		$request = pdo_get($xcmodule . "_group_service", array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
		if ($request) {
			if ($request["status"] == -1) {
				if (time() > strtotime($request["failtime"])) {
					$request["status"] = 2;
				} else {
					$request["fail"] = strtotime($request["failtime"]) - time();
					$request["hour"] = 0;
					$request["min"] = 0;
					$request["second"] = 0;
				}
			}
			$service = pdo_get($xcmodule . "_service_group", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $request["service"]));
			if ($service) {
				$class = pdo_get($xcmodule . "_service", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $service["pid"]));
				if ($class) {
					$service["service_name"] = $class["name"];
				}
				$service["simg"] = tomedia($service["simg"]);
				$request["service_data"] = $service;
			}
			if (!empty($_GPC["order"])) {
				$order_user = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "status IN" => array(1, 2), "order_type" => 7, "group_id" => $request["id"]));
				if ($order_user) {
					$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $order_user["openid"]));
					if ($user) {
						$user["nick"] = base64_decode($user["nick"]);
						$request["user"] = $user;
					}
				}
			}
			if (empty($request["user"])) {
				$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $request["openid"]));
				if ($user) {
					$user["nick"] = base64_decode($user["nick"]);
					$request["user"] = $user;
				}
			}
			$order = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status" => 1, "order_type" => 7, "group_id" => $request["id"]));
			if ($order) {
				$request["order"] = $order;
			}
			$member = pdo_getall($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "status IN" => array(1, 2), "order_type" => 7, "group_id" => $request["id"]), array(), '', "id asc");
			if ($member) {
				$ids = array();
				foreach ($member as $mm) {
					$ids[] = $mm["openid"];
				}
				$userinfo = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid IN" => $ids));
				$user_data = array();
				if ($userinfo) {
					foreach ($userinfo as $u) {
						$user_data[$u["openid"]] = $u;
					}
				}
				foreach ($member as &$m) {
					$m["avatar"] = $user_data[$m["openid"]]["avatar"];
				}
				$request["member_data"] = $member;
			}
			$request["empty"] = array();
			if ($request["member"] > $request["member_on"]) {
				for ($i = 0; $i < $request["member"] - $request["member_on"]; $i++) {
					$request["empty"][] = $i;
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
}