<?php

//decode by http://www.zx-xcx.com/
defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "userinfo";
$uniacid = $_W["uniacid"];
switch ($op) {
	case "userinfo":
		$request = pdo_get("xc_train_userinfo", array("status" => 1, "uniacid" => $uniacid, "openid" => $_W["openid"]));
		if ($request) {
			$request["nick"] = base64_decode($request["nick"]);
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "user":
		$request = array();
		$map = pdo_get($xcmodule . "_config", array("xkey" => "map", "uniacid" => $uniacid));
		if ($map) {
			$map = json_decode($map["content"], true);
			if (!empty($map["bimg"])) {
				$map["bimg"] = tomedia($map["bimg"]);
			}
			$request["map"] = $map;
		}
		$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($user) {
			$user["nick"] = base64_decode($user["nick"]);
			$request["user"] = $user;
		}
		$share = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		if ($share) {
			$share = json_decode($share["content"], true);
			if (!empty($share) && $share["status"] == 1) {
				if ($share["apply_status"] == 1) {
					$share["apply_on"] = -1;
					$apply = pdo_get($xcmodule . "_apply", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status IN" => array(-1, 1, 2)));
					if ($apply) {
						$share["apply"] = $apply;
						if ($apply["status"] == 1) {
							$share["apply_on"] = 1;
						}
					}
				}
				if (!empty($share["apply_img"])) {
					$share["apply_img"] = tomedia($share["apply_img"]);
				}
				$request["share"] = $share;
			}
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "map":
		$request = pdo_get("xc_train_config", array("xkey" => "map", "uniacid" => $uniacid));
		if ($request) {
			$request["content"] = json_decode($request["content"], true);
			if (!empty($request["content"]["bimg"])) {
				$request["content"]["bimg"] = tomedia($request["content"]["bimg"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "set_coupon":
		$coupon = pdo_get("xc_train_coupon", array("status" => 1, "id" => $_GPC["id"], "uniacid" => $uniacid));
		if ($coupon) {
			if ($coupon["total"] == 0) {
				return $this->result(1, "优惠券被领光了！");
			}
			$request = pdo_insert("xc_train_user_coupon", array("uniacid" => $uniacid, "openid" => $_W["openid"], "cid" => $_GPC["id"], "status" => -1));
			if ($request) {
				if ($coupon["total"] != -1) {
					pdo_update("xc_train_coupon", array("total" => intval($coupon["total"]) - 1), array("status" => 1, "id" => $_GPC["id"], "uniacid" => $uniacid));
					return $this->result(0, "操作成功", array("status" => 1));
				}
			} else {
				return $this->result(1, "操作失败");
			}
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "sign":
		if (!empty($_GPC["cut"])) {
			$request = pdo_get("xc_train_cut", array("id" => $_GPC["cut"], "uniacid" => $uniacid));
			if ($request) {
				$service = pdo_get("xc_train_service", array("id" => $request["pid"], "uniacid" => $uniacid));
				if ($service) {
					$request["title"] = $service["name"] . "【" . $request["mark"] . "】";
				}
				$order = pdo_get("xc_train_cut_order", array("openid" => $_W["openid"], "cid" => $request["id"], "uniacid" => $uniacid));
				if ($order) {
					$request["amount"] = $order["price"];
				} else {
					$request["amount"] = $request["price"];
				}
				return $this->result(0, "操作成功", $request);
			} else {
				return $this->result(0, "操作失败");
			}
		} elseif (!empty($_GPC["group_service"])) {
			$service_group = pdo_get($xcmodule . "_service_group", array("status" => 1, "uniacid" => $_W["uniacid"], "id" => $_GPC["group_service"]));
			if ($service_group && time() < strtotime($service_group["end_time"]) && $service_group["member"] > $service_group["member_on"]) {
				$service = pdo_get($xcmodule . "_service", array("id" => $service_group["pid"], "uniacid" => $_W["uniacid"]));
				if ($service) {
					$service_group["title"] = $service["name"] . "【" . $service_group["mark"] . "】";
				}
				if (!empty($_GPC["group_id"])) {
					$group = pdo_get($xcmodule . "_group_service", array("uniacid" => $_W["uniacid"], "id" => $_GPC["group_id"], "status" => -1, "failtime >" => date("Y-m-d H:i:s")));
					if ($group) {
						$service_group["amount"] = $group["group_price"];
					} else {
						return $this->result(0, "操作失败");
					}
				} else {
					$service_group["amount"] = 0;
					if (!empty($service_group["format"])) {
						$service_group["format"] = json_decode($service_group["format"], true);
						if (!empty($service_group["format"][$_GPC["group_param"]])) {
							$service_group["amount"] = $service_group["format"][$_GPC["group_param"]]["price"];
						}
					}
				}
				return $this->result(0, "操作成功", $service_group);
			} else {
				return $this->result(0, "操作失败");
			}
		} else {
			$pid = '';
			if (empty($_GPC["pid"])) {
				$order = pdo_getall("xc_train_order", array("order_type" => 1, "uniacid" => $uniacid, "openid" => $_W["openid"]));
				if ($order) {
					$pid = $order[0]["pid"];
				}
			} else {
				$pid = $_GPC["pid"];
			}
			if (!empty($pid)) {
				$sql = "SELECT * FROM " . tablename("xc_train_service_team") . " WHERE status=1 AND uniacid=:uniacid AND UNIX_TIMESTAMP(end_time)>:times AND more_member>member AND id=:id";
				$request = pdo_fetch($sql, array(":uniacid" => $uniacid, ":times" => time(), "id" => $pid));
				if ($request) {
					$service = pdo_get("xc_train_service", array("status" => 1, "id" => $request["pid"], "uniacid" => $uniacid));
					if ($service) {
						$request["title"] = $service["name"] . "【" . $request["mark"] . "】";
						if (!empty($service["price"])) {
							$request["amount"] = $service["price"];
						} else {
							$request["amount"] = 0;
						}
						$request["list"] = array();
						$coupon = pdo_getall("xc_train_coupon", array("status" => 1, "uniacid" => $uniacid));
						if ($coupon) {
							$coupon_id = array();
							$datalist = array();
							foreach ($coupon as $c) {
								$datalist[$c["id"]] = $c;
								$coupon_id[] = $c["id"];
							}
							$user_coupon = pdo_getall("xc_train_user_coupon", array("status" => -1, "uniacid" => $uniacid, "openid" => $_W["openid"]));
							$user_id = array();
							if ($user_coupon) {
								foreach ($user_coupon as $u) {
									$user_id[] = $u["cid"];
								}
							}
							$condition["status"] = -1;
							$condition["uniacid"] = $uniacid;
							$condition["cid IN"] = $coupon_id;
							$condition["openid"] = $_W["openid"];
							$list = pdo_getall("xc_train_user_coupon", $condition, array(), '', "createtime DESC");
							if ($list) {
								foreach ($list as $key => $x) {
									$x["times"] = $datalist[$x["cid"]]["times"];
									$x["times"] = json_decode($x["times"], true);
									if (strtotime($x["times"]["start"]) < time() && time() < strtotime($x["times"]["end"])) {
										$list[$key]["fail"] = date("Y/m/d", strtotime($x["times"]["end"]));
										$list[$key]["name"] = $datalist[$x["cid"]]["name"];
										$list[$key]["condition"] = $datalist[$x["cid"]]["condition"];
										if (floatval($list[$key]["condition"]) > floatval($request["amount"])) {
											unset($list[$key]);
										}
									} else {
										unset($list[$key]);
									}
								}
							}
							$request["list"] = $list;
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
		}
		break;
	case "active":
		$page = (intval($_GPC["page"]) - 1) * intval($_GPC["pagesize"]);
		$pagesize = $_GPC["pagesize"];
		$request = pdo_fetchall("SELECT * FROM " . tablename("xc_train_active") . " WHERE status=:status AND uniacid=:uniacid AND UNIX_TIMESTAMP(start_time)<:plan_date AND UNIX_TIMESTAMP(end_time)>:plan_date ORDER BY sort DESC,createtime DESC LIMIT {$page},{$pagesize}", array(":status" => 1, ":uniacid" => $uniacid, ":plan_date" => time()));
		if ($request) {
			$userinfo = pdo_getall("xc_train_userinfo", array("uniacid" => $uniacid));
			$datalist = array();
			if ($userinfo) {
				foreach ($userinfo as $u) {
					$datalist[$u["openid"]] = $u;
				}
			}
			foreach ($request as &$x) {
				$x["simg"] = tomedia($x["simg"]);
				$x["bimg"] = tomedia($x["bimg"]);
				$x["start_time"] = date("Y-m-d", strtotime($x["start_time"]));
				$x["end_time"] = date("Y-m-d", strtotime($x["end_time"]));
				$x["share_img"] = tomedia($x["share_img"]);
				$x["list"] = pdo_getall("xc_train_prize", array("status" => 1, "uniacid" => $uniacid, "cid" => $x["id"], "type" => $x["type"]), array(), '', "prizetime DESC,createtime DESC,id DESC", array(1, 10));
				if ($x["list"]) {
					foreach ($x["list"] as &$y) {
						$y["simg"] = $datalist[$y["openid"]]["avatar"];
						$y["nick"] = base64_decode($datalist[$y["openid"]]["nick"]);
					}
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "active_detail":
		$request = pdo_get("xc_train_active", array("status" => 1, "id" => $_GPC["id"], "uniacid" => $uniacid));
		if ($request) {
			$request["simg"] = tomedia($request["simg"]);
			$request["bimg"] = tomedia($request["bimg"]);
			$request["gua_img"] = tomedia($request["gua_img"]);
			if ($request["type"] == 1) {
				$request["prize_name"] = $request["prize"];
				$request["share_img"] = tomedia($request["share_img"]);
			}
			$request["share_type"] = 2;
			$request["list"] = pdo_getall("xc_train_prize", array("status" => 1, "uniacid" => $uniacid, "cid" => $request["id"], "type" => $request["type"]), array(), '', "prizetime DESC", array(1, 10));
			if ($request["list"]) {
				$userinfo = pdo_getall("xc_train_userinfo", array("uniacid" => $uniacid));
				$datalist = array();
				if ($userinfo) {
					foreach ($userinfo as $u) {
						$datalist[$u["openid"]] = $u;
					}
				}
				foreach ($request["list"] as &$y) {
					$y["simg"] = $datalist[$y["openid"]]["avatar"];
					$y["nick"] = base64_decode($datalist[$y["openid"]]["nick"]);
				}
			}
			$request["opengid"] = array();
			$request["prize"] = pdo_get("xc_train_prize", array("openid" => $_W["openid"], "cid" => $request["id"], "uniacid" => $uniacid, "type" => $request["type"]));
			$request["you_xiao"] = 0;
			if ($request["prize"]) {
				$request["prize"]["opengid"] = json_decode($request["prize"]["opengid"], true);
				if (!empty($request["prize"]["opengid"])) {
					foreach ($request["prize"]["opengid"] as $p) {
						if (!empty($p)) {
							$request["you_xiao"] = $request["you_xiao"] + 1;
						}
					}
				}
				if ($request["prize"]["type"] == 2 && !empty($request["prize"]["pid"])) {
					$gua_list = pdo_get("xc_train_gua", array("id" => $request["prize"]["pid"], "uniacid" => $uniacid));
					if ($gua_list) {
						$request["prize_bimg"] = tomedia($gua_list["bimg"]);
					}
				}
				$request["opengid"] = $request["prize"]["opengid"];
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "prize":
		$request = pdo_getall("xc_train_prize", array("status" => 1, "openid" => $_W["openid"], "uniacid" => $uniacid), array(), '', "prizetime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			$prize = pdo_getall("xc_train_active", array("uniacid" => $uniacid));
			$datalist = array();
			if ($prize) {
				foreach ($prize as $p) {
					$datalist[$p["id"]] = $p;
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
				if ($x["type"] == 1) {
					$x["simg"] = tomedia($datalist[$x["cid"]]["bimg"]);
				} elseif ($x["type"] == 2) {
					$x["simg"] = tomedia($gua_list[$x["pid"]]["bimg"]);
				}
				$x["code"] = base64_encode($x["id"]);
				if (!empty($x["check_code"])) {
					$x["check_code"] = tomedia($x["check_code"]);
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "active_status":
		$prize = pdo_get("xc_train_prize", array("cid" => $_GPC["id"], "uniacid" => $uniacid, "type" => 2, "openid" => $_W["openid"]));
		$request = pdo_update("xc_train_prize", array("status" => 1, "prizetime" => date("Y-m-d H:i:s")), array("openid" => $_W["openid"], "cid" => $_GPC["id"], "uniacid" => $uniacid, "type" => 2));
		if ($request) {
			return $this->result(0, "操作成功", array("name" => $prize["prize"]));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "address":
		$request = pdo_getall("xc_train_address", array("openid" => $_W["openid"], "uniacid" => $uniacid), array(), '', "id DESC");
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "address_edit":
		$condition = array("uniacid" => $uniacid, "openid" => $_W["openid"], "name" => $_GPC["name"], "mobile" => $_GPC["mobile"], "sex" => $_GPC["sex"], "latitude" => $_GPC["latitude"], "longitude" => $_GPC["longitude"]);
		if (!empty($_GPC["address"])) {
			$condition["address"] = $_GPC["address"];
		}
		if (!empty($_GPC["latitude"])) {
			$condition["latitude"] = $_GPC["latitude"];
		}
		if (!empty($_GPC["longitude"])) {
			$condition["longitude"] = $_GPC["longitude"];
		}
		if (!empty($_GPC["content"])) {
			$condition["content"] = $_GPC["content"];
		}
		if (!empty($_GPC["id"])) {
			$request = pdo_update("xc_train_address", $condition, array("id" => $_GPC["id"], "uniacid" => $uniacid));
		} else {
			pdo_update("xc_train_address", array("status" => -1), array("openid" => $_W["openid"], "uniacid" => $uniacid));
			$request = pdo_insert("xc_train_address", $condition);
		}
		if ($request) {
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "address_del":
		$request = pdo_delete("xc_train_address", array("id" => $_GPC["id"], "uniacid" => $uniacid));
		if ($request) {
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "address_status":
		pdo_update("xc_train_address", array("status" => -1), array("openid" => $_W["openid"], "uniacid" => $uniacid));
		$request = pdo_update("xc_train_address", array("status" => 1), array("id" => $_GPC["id"], "openid" => $_W["openid"], "uniacid" => $uniacid));
		if ($request) {
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "getCode":
		$service = pdo_get("xc_train_service", array("uniacid" => $uniacid, "status" => 1, "id" => $_GPC["id"]));
		if ($service) {
			$code = '';
			if (!empty($service["code"])) {
				$code = $service["code"];
			} else {
				$account_api = WeAccount::create();
				$token = $account_api->getAccessToken();
				if (is_array($token)) {
					return $this->result(1, $token["errno"] . ":" . $token["message"], $token);
				} else {
					$filename = "service_" . $service["id"] . ".jpg";
					$fileurl = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
					if (!is_dir($fileurl)) {
						mkdir($fileurl, 0700, true);
					}
					$fileurl = $fileurl . "/" . date("m") . "/";
					if (!is_dir($fileurl)) {
						mkdir($fileurl, 0700, true);
					}
					$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
					$post_data = array("path" => "xc_train/pages/base/base", "scene" => "service_" . $service["id"]);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
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
						$code = "https://" . $_SERVER["HTTP_HOST"] . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $filename;
						pdo_update("xc_train_service", array("code" => $code), array("id" => $_GPC["id"], "uniacid" => $uniacid));
					}
				}
			}
			$url = usercode($code, $service, $_W);
			return $this->result(0, "操作成功", array("code" => $url));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "online":
		if (!empty($_W["openid"])) {
			$request = array("admin" => -1);
			$userinfo = pdo_get("xc_train_userinfo", array("openid" => $_W["openid"], "uniacid" => $uniacid));
			if ($userinfo && $userinfo["shop"] == 1) {
				$request["admin"] = 1;
			}
			$user_condition["uniacid"] = $uniacid;
			if (!empty($_GPC["search"])) {
				$user_condition["nick LIKE"] = "%" . base64_encode($_GPC["search"]) . "%";
			}
			$user = pdo_getall("xc_train_userinfo", $user_condition);
			$datalist = array();
			$openid = [];
			if ($user) {
				foreach ($user as $u) {
					$u["nick"] = base64_decode($u["nick"]);
					$datalist[$u["openid"]] = $u;
					if (!empty($_GPC["search"])) {
						$openid[] = $u["openid"];
					}
				}
			}
			$condition["uniacid"] = $uniacid;
			if (!empty($openid)) {
				$condition["openid IN"] = $openid;
			}
			$list = pdo_getall("xc_train_online", $condition, array(), '', "updatetime DESC,id DESC", array($_GPC["page"], $_GPC["pagesize"]));
			if ($list) {
				foreach ($list as &$x) {
					if ($x["type"] == 1) {
						$x["content"] = base64_decode($x["content"]);
						$x["content"] = emoji($x["content"], $_GPC["m"]);
					}
					$x["nick"] = $datalist[$x["openid"]]["nick"];
					$x["avatar"] = $datalist[$x["openid"]]["avatar"];
				}
				$request["list"] = $list;
			}
			if ($request) {
				return $this->result(0, "操作成功", $request);
			} else {
				return $this->result(0, "操作失败");
			}
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "online_detail":
		$request = array("refresh" => 0);
		$userinfo = pdo_get("xc_train_userinfo", array("openid" => $_W["openid"], "uniacid" => $uniacid));
		if ($userinfo) {
			$request["user"] = $userinfo;
		}
		$user = pdo_getall("xc_train_userinfo", array("uniacid" => $uniacid));
		$datalist = array();
		if ($user) {
			foreach ($user as $u) {
				$u["nick"] = base64_decode($u["nick"]);
				$datalist[$u["openid"]] = $u;
			}
		}
		pdo_update("xc_train_online", array("member" => 0), array("id" => $_GPC["id"], "uniacid" => $uniacid));
		$list = pdo_getall("xc_train_online", array("uniacid" => $uniacid, "id" => $_GPC["id"]), array(), '', "updatetime DESC,id DESC");
		if ($list) {
			foreach ($list as &$x) {
				$x["nick"] = $datalist[$x["openid"]]["nick"];
				$x["avatar"] = $datalist[$x["openid"]]["avatar"];
			}
			$request["list"] = $list;
		}
		$condition["pid"] = $_GPC["id"];
		$condition["uniacid"] = $uniacid;
		if (!empty($_GPC["prev_id"])) {
			$condition["id <"] = $_GPC["prev_id"];
		}
		$detail = pdo_getall("xc_train_online_log", $condition, array(), '', "id DESC", array(1, $_GPC["pagesize"]));
		if ($detail) {
			foreach ($detail as &$d) {
				if ($d["type"] == 1) {
					$d["content"] = base64_decode($d["content"]);
					if ($d["duty"] == 1 || $d["duty"] == 2 && $d["openid"] != $_W["openid"]) {
						$d["content"] = emoji($d["content"], $_GPC["m"]);
					}
				}
				$d["nick"] = $datalist[$d["openid"]]["nick"];
				$d["avatar"] = $datalist[$d["openid"]]["avatar"];
				$d["on"] = -1;
				if ($d["duty"] == 2 && $d["openid"] == $_W["openid"]) {
					$d["on"] = 1;
				}
			}
			$request["detail"] = array_reverse($detail);
		}
		$config = pdo_get("xc_train_config", array("xkey" => "online", "uniacid" => $uniacid));
		if ($config) {
			$config["content"] = json_decode($config["content"], true);
			if (!empty($config["content"]) && !empty($config["content"]["refresh"])) {
				$request["refresh"] = intval($config["content"]["refresh"]) * 1000;
			}
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "online_on":
		$online = pdo_get("xc_train_online", array("id" => $_GPC["pid"], "uniacid" => $uniacid));
		$account_api = WeAccount::create();
		$token = $account_api->getAccessToken();
		$data = array();
		if ($_GPC["type"] == 1) {
			$data = array("touser" => $online["openid"], "msgtype" => "text", "text" => array("content" => $_GPC["content"]));
		} elseif ($_GPC["type"] == 2) {
			$url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $token . "&type=image";
			$res = wxUpload($url, array("media" => "@" . $_GPC["upload"]));
			$res = json_decode($res, true);
			if (!empty($res["media_id"])) {
				$data = array("touser" => $online["openid"], "msgtype" => "image", "image" => array("media_id" => $res["media_id"]));
			} else {
				return $this->result(1, $res["errmsg"]);
			}
		}
		$json = json_encode($data, JSON_UNESCAPED_UNICODE);
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $token;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($json)) {
			curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		if (curl_errno($curl)) {
			return $this->result(1, "Errno" . curl_error($curl));
		}
		curl_close($curl);
		if ($output == 0) {
			$condition["uniacid"] = $uniacid;
			$condition["pid"] = $_GPC["pid"];
			$condition["openid"] = $_W["openid"];
			$condition["type"] = $_GPC["type"];
			if ($_GPC["type"] == 1) {
				$condition["content"] = base64_encode($_GPC["content"]);
			} else {
				$condition["content"] = $_GPC["content"];
			}
			$condition["duty"] = 2;
			$request = pdo_insert("xc_train_online_log", $condition);
			if ($request) {
				$id = pdo_insertid();
				return $this->result(0, "操作成功", array("id" => $id, "createtime" => date("Y-m-d H:i:s")));
			} else {
				return $this->result(1, "发送失败");
			}
		} else {
			return $this->result(1, "Errno" . $output);
		}
		break;
	case "online_refresh":
		pdo_update("xc_train_online", array("member" => 0), array("id" => $_GPC["pid"], "uniacid" => $uniacid));
		$request = pdo_getall("xc_train_online_log", array("id >" => $_GPC["id"], "pid" => $_GPC["pid"], "duty" => 1), array(), '', "id");
		if ($request) {
			$user = pdo_getall("xc_train_userinfo", array("uniacid" => $uniacid));
			$datalist = array();
			if ($user) {
				foreach ($user as $u) {
					$u["nick"] = base64_decode($u["nick"]);
					$datalist[$u["openid"]] = $u;
				}
			}
			foreach ($request as &$d) {
				if ($d["type"] == 1) {
					$d["content"] = base64_decode($d["content"]);
					$d["content"] = emoji($d["content"], $_GPC["m"]);
				}
				$d["nick"] = $datalist[$d["openid"]]["nick"];
				$d["avatar"] = $datalist[$d["openid"]]["avatar"];
				$d["on"] = -1;
				if ($d["duty"] == 2 && $d["openid"] == $_W["openid"]) {
					$d["on"] = 1;
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "online_refresh2":
		$request = pdo_getall("xc_train_online", array("id !=" => $_GPC["id"], "member >" => 0, "uniacid" => $_W["uniacid"]), array(), '', "updatetime DESC,id DESC");
		if ($request) {
			$user = pdo_getall("xc_train_userinfo", array("uniacid" => $uniacid));
			$datalist = array();
			if ($user) {
				foreach ($user as $u) {
					$u["nick"] = base64_decode($u["nick"]);
					$datalist[$u["openid"]] = $u;
				}
			}
			foreach ($request as &$d) {
				if ($d["type"] == 1) {
					$d["content"] = base64_decode($d["content"]);
					$d["content"] = emoji($d["content"], $_GPC["m"]);
				}
				$d["nick"] = $datalist[$d["openid"]]["nick"];
				$d["avatar"] = $datalist[$d["openid"]]["avatar"];
				$d["on"] = -1;
				if ($d["duty"] == 2 && $d["openid"] == $_W["openid"]) {
					$d["on"] = 1;
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "audioCode":
		$service = pdo_get("xc_train_audio", array("uniacid" => $uniacid, "status" => 1, "id" => $_GPC["id"]));
		if ($service) {
			$code = '';
			if (!empty($service["code"])) {
				$code = tomedia($service["code"]);
			} else {
				$filename = "audio_" . $service["id"] . ".jpg";
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
				$post_data = array("path" => "xc_train/pages/base/base", "scene" => "audio_" . $service["id"]);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
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
					pdo_update("xc_train_audio", array("code" => $code), array("id" => $_GPC["id"], "uniacid" => $uniacid));
					$code = tomedia($code);
				}
			}
			$url = audiocode($code, $service, $_W);
			return $this->result(0, "操作成功", array("code" => $url));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "sign_config":
		$request = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "form"));
		if ($request) {
			$request["content"] = json_decode($request["content"], true);
			if (!empty($request["content"])) {
				$xc = $request["content"];
				if (empty($xc["name_status"])) {
					$xc["name_status"] = 1;
				}
				if (empty($xc["mobile_status"])) {
					$xc["mobile_status"] = 1;
				}
				if (empty($xc["coupon_status"])) {
					$xc["coupon_status"] = 1;
				}
				if (empty($xc["content_status"])) {
					$xc["content_status"] = 1;
				}
				if (empty($xc["tui_status"])) {
					$xc["tui_status"] = 1;
				}
				return $this->result(0, "操作成功", $xc);
			} else {
				return $this->result(0, "操作失败");
			}
		} else {
			return $this->result(0, "操作失败");
		}
		break;
}