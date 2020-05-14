<?php

//decode by http://www.zx-xcx.com/
defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "order";
switch ($op) {
	case "order":
		$request = pdo_getall("xc_train_order", array("status" => 1, "openid" => $_W["openid"], "uniacid" => $uniacid, "order_type" => $_GPC["order_type"]), array(), '', "createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			$service = pdo_getall("xc_train_service_team", array("uniacid" => $uniacid));
			$datalist = array();
			if ($service) {
				foreach ($service as $s) {
					$datalist[$s["id"]] = $s;
				}
			}
			foreach ($request as &$x) {
				$x["start_time"] = $datalist[$x["pid"]]["start_time"];
				$x["use_time"] = json_decode($x["use_time"], true);
				if (!empty($x["check_code"])) {
					$x["check_code"] = tomedia($x["check_code"]);
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "discuss":
		$condition["status"] = 1;
		$condition["uniacid"] = $uniacid;
		$condition["pid"] = $_GPC["id"];
		$condition["type"] = 1;
		if (!empty($_GPC["type"])) {
			$condition["type"] = $_GPC["type"];
		}
		$request = pdo_getall("xc_train_discuss", $condition, array(), '', "createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			$key_word = array();
			$config = pdo_get($xcmodule . "_config", array("uniacid" => $uniacid, "xkey" => "web"));
			if ($config) {
				$config = json_decode($config["content"], true);
				if ($config && !empty($config["comment_words"])) {
					$config["comment_words"] = explode("/", $config["comment_words"]);
					foreach ($config["comment_words"] as $cw) {
						$num = '';
						for ($i = 0; $i < mb_strlen($cw, "UTF8"); $i++) {
							$num .= "*";
						}
						$key_word[] = array("key" => $cw, "word" => $num);
					}
				}
			}
			$ids = array();
			foreach ($request as $xx) {
				$ids[] = $xx["openid"];
			}
			$userinfo = pdo_getall("xc_train_userinfo", array("uniacid" => $uniacid, "openid IN" => $ids));
			$datalist = array();
			if ($userinfo) {
				foreach ($userinfo as $u) {
					$datalist[$u["openid"]] = $u;
				}
			}
			foreach ($request as &$x) {
				$x["simg"] = $datalist[$x["openid"]]["avatar"];
				$x["nick"] = base64_decode($datalist[$x["openid"]]["nick"]);
				if (!empty($key_word)) {
					foreach ($key_word as $kw) {
						if (!empty($x["content"])) {
							$x["content"] = str_replace($kw["key"], $kw["word"], $x["content"]);
						}
						if (!empty($x["reply_content"])) {
							$x["reply_content"] = str_replace($kw["key"], $kw["word"], $x["reply_content"]);
						}
					}
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "discuss_on":
		$condition["uniacid"] = $uniacid;
		$condition["openid"] = $_W["openid"];
		$condition["pid"] = $_GPC["id"];
		$condition["content"] = $_GPC["content"];
		$condition["type"] = 1;
		if (!empty($_GPC["type"])) {
			$condition["type"] = $_GPC["type"];
		}
		if ($condition["type"] == 3) {
			$service = pdo_get("xc_train_audio", array("uniacid" => $uniacid, "id" => $condition["pid"]));
			if ($service) {
				$is_buy = -1;
				if (!empty($service["price"])) {
					$order = pdo_get("xc_train_order", array("uniacid" => $uniacid, "openid" => $_W["openid"], "order_type" => 5, "pid" => $condition["pid"], "status" => 1));
					if ($order) {
						$is_buy = 1;
					}
				} else {
					$is_buy = 1;
				}
				if ($is_buy == -1) {
					return $this->result(1, "请先购买");
				}
			}
		}
		$request = pdo_insert("xc_train_discuss", $condition);
		if ($request) {
			if ($condition["type"] == 1) {
				$service = pdo_get("xc_train_service", array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
				pdo_update("xc_train_service", array("discuss" => $service["discuss"] + 1), array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
			}
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "mall_order":
		$condition["uniacid"] = $uniacid;
		$condition["openid"] = $_W["openid"];
		$condition["order_type"] = 4;
		if ($_GPC["curr"] == 1) {
			$condition["status"] = 1;
			$condition["order_status"] = -1;
		} elseif ($_GPC["curr"] == 2) {
			$condition["status"] = 1;
			$condition["order_status"] = 1;
		} elseif ($_GPC["curr"] == 3) {
			$condition["status"] = 2;
		} else {
			$condition["status IN"] = array(1, 2);
		}
		$request = pdo_getall("xc_train_order", $condition, array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			$service = pdo_getall("xc_train_mall", array("uniacid" => $uniacid));
			$datalist = array();
			if ($service) {
				foreach ($service as $s) {
					$datalist[$s["id"]] = $s;
				}
			}
			$group = pdo_getall("xc_train_group", array("uniacid" => $uniacid));
			$group_list = array();
			if ($group) {
				foreach ($group as $g) {
					$group_list[$g["id"]] = $g;
				}
			}
			foreach ($request as &$x) {
				$x["simg"] = tomedia($datalist[$x["pid"]]["simg"]);
				if ($x["mall_type"] == 2 && $x["group_status"] == -1) {
					if (!empty($group_list[$x["group_id"]])) {
						if (time() > strtotime($group_list[$x["group_id"]]["failtime"])) {
							$x["status"] = 2;
							$x["group_status"] = 2;
						}
					}
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
	case "mall_order_status":
		$request = pdo_update("xc_train_order", array("order_status" => $_GPC["status"]), array("uniacid" => $uniacid, "id" => $_GPC["id"]));
		if ($request) {
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "mall_order_detail":
		$request = pdo_get("xc_train_order", array("id" => $_GPC["id"], "uniacid" => $uniacid));
		if ($request) {
			$service = pdo_get("xc_train_mall", array("uniacid" => $uniacid, "id" => $request["pid"]));
			if ($service) {
				$request["simg"] = tomedia($service["simg"]);
			}
			$request["userinfo"] = json_decode($request["userinfo"], true);
			if (!empty($request["store"])) {
				$store = pdo_get("xc_train_school", array("uniacid" => $uniacid, "id" => $request["store"]));
				if ($store) {
					$request["store_name"] = $store["name"];
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "mall_tui":
		$order = pdo_get($xcmodule . "_order", array("uniacid" => $uniacid, "id" => $_GPC["id"]));
		$request = pdo_update("xc_train_order", array("status" => 2, "tui_content" => $_GPC["content"]), array("uniacid" => $uniacid, "id" => $_GPC["id"]));
		if ($request) {
			if ($order && $order["order_type"] == 9) {
				pdo_update($xcmodule . "_move", array("member_on -=" => $order["total"]), array("uniacid" => $_W["uniacid"], "id" => $order["pid"]));
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "group":
		$xcmodule = "xc_train";
		$group = pdo_get($xcmodule . "_group", array("id" => $_GPC["id"], "uniacid" => $uniacid));
		if ($group) {
			$user = pdo_getall($xcmodule . "_userinfo");
			$datalist = array();
			if ($user) {
				foreach ($user as $uu) {
					$uu["nick"] = base64_decode($uu["nick"]);
					$datalist[$uu["openid"]] = $uu;
				}
			}
			$group["group"] = json_decode($group["group"]);
			$group["list"] = array();
			$group["join"] = 1;
			foreach ($group["group"] as $x) {
				if ($x == $_W["openid"]) {
					$group["join"] = -1;
				}
				$group["list"][] = array("openid" => $x, "nick" => $datalist[$x]["nick"], "avatar" => $datalist[$x]["avatar"]);
			}
			$group["u_list"] = array();
			if ($group["is_member"] < $group["member"]) {
				for ($i = 0; $i < $group["member"] - $group["is_member"]; $i++) {
					$group["u_list"][] = array();
				}
			}
			if ($group["status"] == -1) {
				$group["fail"] = strtotime($group["failtime"]) - time();
			} else {
				$group_order = pdo_getall($xcmodule . "_group", array("uniacid" => $uniacid, "status" => -1, "service" => $group["service"], "failtime >" => date("Y-m-d H;i:s")));
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
							$go["list"][] = array("openid" => $goo, "nick" => $datalist[$goo]["nick"], "avatar" => $datalist[$goo]["avatar"]);
						}
						$go["u_list"] = array();
						if ($go["is_member"] < $go["member"]) {
							for ($i = 0; $i < $go["member"] - $go["is_member"]; $i++) {
								$go["u_list"][] = array();
							}
						}
						$go["fail"] = strtotime($go["failtime"]) - time();
					}
					$group["group_order"] = $group_order;
				}
			}
			$service = pdo_get($xcmodule . "_mall", array("id" => $group["service"], "uniacid" => $uniacid));
			if ($service) {
				$service["format"] = json_decode($service["format"], true);
				$service["simg"] = tomedia($service["simg"]);
				$group["service_list"] = $service;
			}
			return $this->result(0, "操作成功", $group);
		} else {
			return $this->result(1, "团不存在");
		}
		break;
	case "audio_order":
		$request = array();
		$service = pdo_getall("xc_train_audio", array("uniacid" => $uniacid));
		$service_list = array();
		$ids = array();
		if ($service) {
			foreach ($service as $s) {
				$service_list[$s["id"]] = $s;
				$ids[] = $s["id"];
			}
		}
		if ($_GPC["curr"] == 0) {
			$request = pdo_getall("xc_train_mark", array("uniacid" => $uniacid, "openid" => $_W["openid"], "pid IN" => $ids, "status" => 1), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
			if ($request) {
				foreach ($request as &$m) {
					$m["name"] = $service_list[$m["pid"]]["name"];
					$m["simg"] = tomedia($service_list[$m["pid"]]["simg"]);
					$m["member"] = $service_list[$m["pid"]]["member"];
				}
			}
		} elseif ($_GPC["curr"] == 1) {
			$request = pdo_getall("xc_train_order", array("status" => 1, "openid" => $_W["openid"], "uniacid" => $uniacid, "order_type" => 5), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
			if ($request) {
				foreach ($request as &$x) {
					$x["simg"] = tomedia($service_list[$x["pid"]]["simg"]);
					$x["member"] = $service_list[$x["pid"]]["member"];
				}
			}
		} elseif ($_GPC["curr"] == 2) {
			$request = pdo_getall("xc_train_history", array("uniacid" => $uniacid, "openid" => $_W["openid"], "pid IN" => $ids, "status" => 1, "type" => 1), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
			if ($request) {
				foreach ($request as &$m) {
					$m["name"] = $service_list[$m["pid"]]["name"];
					$m["simg"] = tomedia($service_list[$m["pid"]]["simg"]);
					$m["member"] = $service_list[$m["pid"]]["member"];
				}
			}
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "video_order":
		$request = array();
		$service = pdo_getall("xc_train_video", array("uniacid" => $uniacid));
		$service_list = array();
		$ids = array();
		if ($service) {
			foreach ($service as $s) {
				$service_list[$s["id"]] = $s;
				$ids[] = $s["id"];
			}
		}
		if ($_GPC["curr"] == 0) {
			$request = pdo_getall("xc_train_order", array("status" => 1, "openid" => $_W["openid"], "uniacid" => $uniacid, "order_type" => 3), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
			if ($request) {
				foreach ($request as &$x) {
					$x["simg"] = tomedia($service_list[$x["pid"]]["bimg"]);
					$x["teacher_name"] = $service_list[$x["pid"]]["teacher_name"];
				}
			}
		} elseif ($_GPC["curr"] == 1) {
			$request = pdo_getall("xc_train_history", array("uniacid" => $uniacid, "openid" => $_W["openid"], "pid IN" => $ids, "status" => 1, "type" => 2), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
			if ($request) {
				foreach ($request as &$m) {
					$m["name"] = $service_list[$m["pid"]]["name"];
					$m["simg"] = tomedia($service_list[$m["pid"]]["bimg"]);
					$x["teacher_name"] = $service_list[$m["pid"]]["teacher_name"];
				}
			}
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "line_order":
		$request = pdo_getall("xc_train_order", array("uniacid" => $_W["uniacid"], "status" => 1, "openid" => $_W["openid"], "order_type" => 6), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			foreach ($request as &$x) {
				$x["line_img"] = tomedia($x["line_img"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "line_order_detail":
		$order = pdo_get("xc_train_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status" => 1, "order_type" => 6, "id" => $_GPC["id"]));
		if ($order) {
			$request = array("order" => $order);
			$video = pdo_getall("xc_train_line_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "out_trade_no" => $order["out_trade_no"], "type" => 1));
			if ($video) {
				$ids = array();
				foreach ($video as $v) {
					$ids[] = $v["pid"];
				}
				$video_data = pdo_getall("xc_train_video", array("uniacid" => $_W["uniacid"], "status" => 1, "id IN" => $ids), array(), '', "id DESC,id DESC");
				if ($video_data) {
					foreach ($video_data as &$vd) {
						$vd["bimg"] = tomedia($vd["bimg"]);
					}
				}
				$request["video"] = $video_data;
			}
			$audio = pdo_getall("xc_train_line_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "out_trade_no" => $order["out_trade_no"], "type" => 2));
			if ($audio) {
				$ids = array();
				foreach ($audio as $a) {
					$ids[] = $a["pid"];
				}
				$audio_data = pdo_getall("xc_train_audio", array("uniacid" => $_W["uniacid"], "status" => 1, "id IN" => $ids), array(), '', "id DESC,id DESC");
				if ($audio_data) {
					foreach ($audio_data as &$ad) {
						$ad["simg"] = tomedia($ad["simg"]);
					}
				}
				$request["audio"] = $audio_data;
			}
			if ($request) {
				return $this->result(0, "操作成功", $request);
			} else {
				return $this->result(0, "操作失败");
			}
		} else {
			return $this->result(1, "订单不存在");
		}
		break;
	case "check_code":
		$order = pdo_get($xcmodule . "_order", array("uniacid" => $uniacid, "id" => $_GPC["id"], "status" => 1, "openid" => $_W["openid"]));
		if ($order) {
			$code = '';
			if (!empty($order["check_code"])) {
				$code = $order["check_code"];
			} else {
				$filename = "group_" . $order["id"] . ".jpg";
				$fileurl = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
				if (!is_dir($fileurl)) {
					mkdir($fileurl, 0700, true);
				}
				$fileurl = $fileurl . "/" . date("m") . "/";
				if (!is_dir($fileurl)) {
					mkdir($fileurl, 0700, true);
				}
				$account_api = WeAccount::create();
				$token = $account_api->getAccessToken();
				$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
				$wx_data = array("path" => "xc_train/pages/base/base", "scene" => "admin_" . $order["id"]);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($wx_data));
				$output = curl_exec($ch);
				curl_close($ch);
				$request = json_decode($output, true);
				if (is_array($request) && !empty($request)) {
					return $this->result(1, "图片生成失败" . $request["errcode"] . ":" . $request["errmsg"], $request);
				} else {
					header("Content-Type: text/plain; charset=utf-8");
					header("Content-type:image/jpeg");
					$jpg = $output;
					$file = fopen($fileurl . $filename, "w");
					$reee = fwrite($file, $jpg);
					fclose($file);
					$code = "images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $filename;
					pdo_update($xcmodule . "_order", array("check_code" => $code), array("id" => $order["id"], "uniacid" => $uniacid));
				}
			}
			return $this->result(0, "操作成功", array("code" => tomedia($code)));
		} else {
			return $this->result(1, "订单不存在");
		}
		break;
	case "prize_check_code":
		$order = pdo_get($xcmodule . "_prize", array("uniacid" => $uniacid, "id" => $_GPC["id"], "openid" => $_W["openid"]));
		if ($order) {
			$code = '';
			if (!empty($order["check_code"])) {
				$code = $order["check_code"];
			} else {
				$filename = "group_" . $order["id"] . ".jpg";
				$fileurl = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
				if (!is_dir($fileurl)) {
					mkdir($fileurl, 0700, true);
				}
				$fileurl = $fileurl . "/" . date("m") . "/";
				if (!is_dir($fileurl)) {
					mkdir($fileurl, 0700, true);
				}
				$account_api = WeAccount::create();
				$token = $account_api->getAccessToken();
				$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
				$wx_data = array("path" => "xc_train/pages/base/base", "scene" => "admin_" . base64_encode($order["id"]));
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($wx_data));
				$output = curl_exec($ch);
				curl_close($ch);
				$request = json_decode($output, true);
				if (is_array($request) && !empty($request)) {
					return $this->result(1, "图片生成失败" . $request["errcode"] . ":" . $request["errmsg"], $request);
				} else {
					header("Content-Type: text/plain; charset=utf-8");
					header("Content-type:image/jpeg");
					$jpg = $output;
					$file = fopen($fileurl . $filename, "w");
					$reee = fwrite($file, $jpg);
					fclose($file);
					$code = "images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $filename;
					pdo_update($xcmodule . "_prize", array("check_code" => $code), array("id" => $order["id"], "uniacid" => $uniacid));
				}
			}
			return $this->result(0, "操作成功", array("code" => tomedia($code)));
		} else {
			return $this->result(1, "订单不存在");
		}
		break;
}