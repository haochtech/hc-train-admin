<?php

defined("IN_IA") or exit("Access Denied");
global $xcmodule;
$xcmodule = "xc_train";
include_once IA_ROOT . "/addons/xc_train/common/function.php";
class Xc_trainModuleWxapp extends WeModuleWxapp
{
	public function doPagePrize()
	{
		global $_GPC, $_W;
		$uniacid = $_W["uniacid"];
		if (!empty($_GPC["openid"])) {
			if ($_GPC["openid"] == $_W["openid"]) {
				return $this->result(1, "自己不能助力");
			}
		}
		$active = pdo_get("xc_train_active", array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
		if ($active) {
			if (intval($active["total"]) == intval($active["is_total"])) {
				return $this->result(1, "活动已经结束了！");
			} else {
				require_once "../addons/" . $_GPC["m"] . "/resource/share/wxBizDataCrypt.php";
				$appid = $_W["account"]["key"];
				$sessionKey = $_SESSION["session_key"];
				$encryptedData = $_GPC["encryptedData"];
				$iv = $_GPC["iv"];
				$pc = new WXBizDataCrypt($appid, $sessionKey);
				$errCode = $pc->decryptData($encryptedData, $iv, $data);
				if ($errCode == 0) {
					$data = json_decode($data, true);
					$opengid = $data["openGId"];
					if (!empty($_GPC["openid"])) {
						$prize = pdo_get("xc_train_prize", array("status" => -1, "type" => $active["type"], "cid" => $_GPC["id"], "openid" => $_GPC["openid"], "uniacid" => $uniacid));
					} else {
						$prize = pdo_get("xc_train_prize", array("status" => -1, "type" => $active["type"], "cid" => $_GPC["id"], "openid" => $_W["openid"], "uniacid" => $uniacid));
					}
					if ($prize) {
						$prize["opengid"] = json_decode($prize["opengid"], true);
						$common = -1;
						$is_total = 0;
						for ($i = 0; $i < count($prize["opengid"]); $i++) {
							if ($prize["opengid"][$i] == $opengid) {
								$common = 1;
							}
							if (!empty($prize["opengid"][$i])) {
								$is_total = $is_total + 1;
							}
						}
						if ($common == 1) {
							if (!empty($_GPC["openid"])) {
								return $this->result(1, "该群已助力");
							} else {
								return $this->result(1, "该群已分享");
							}
						} else {
							$prize_bimg = '';
							$condition["opengid"] = $prize["opengid"];
							$condition["opengid"][intval($_GPC["share"]) - 1] = $opengid;
							$condition["opengid"] = json_encode($condition["opengid"]);
							if ($is_total + 1 == intval($active["share"])) {
								if ($active["type"] == 1) {
									$condition["status"] = 1;
									$condition["prizetime"] = date("Y-m-d H:i:s");
									$condition["prize"] = $active["prize"];
								} elseif ($active["type"] == 2) {
									if (!empty($active["list"])) {
										$id = array();
										$list = json_decode($active["list"], true);
										foreach ($list as $l) {
											$id[] = $l["id"];
										}
										$gua = pdo_getall("xc_train_gua", array("status" => 1, "uniacid" => $uniacid, "id IN" => $id));
										if ($gua) {
											$total_times = 0;
											foreach ($gua as &$g) {
												$g["min"] = $total_times;
												$total_times = intval($g["times"]) + $total_times;
												$g["max"] = $total_times;
											}
											$num = rand(0, $total_times * 100);
											$num = $num / 100;
											foreach ($gua as $gg) {
												if (floatval($gg["min"]) < floatval($num) && floatval($num) < floatval($gg["max"])) {
													$condition["prize"] = $gg["name"];
													$condition["pid"] = $gg["id"];
													$prize_bimg = tomedia($gg["bimg"]);
												}
											}
										}
									}
								}
							}
							if (!empty($_GPC["openid"])) {
								$request = pdo_update("xc_train_prize", $condition, array("status" => -1, "cid" => $_GPC["id"], "openid" => $_GPC["openid"], "uniacid" => $uniacid, "type" => $active["type"]));
							} else {
								$request = pdo_update("xc_train_prize", $condition, array("status" => -1, "cid" => $_GPC["id"], "openid" => $_W["openid"], "uniacid" => $uniacid, "type" => $active["type"]));
							}
							if ($request) {
								if (!empty($condition["status"])) {
									pdo_update("xc_train_active", array("is_total" => intval($active["is_total"]) + 1), array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
									return $this->result(0, "分享成功", array("status" => 2, "opengid" => json_decode($condition["opengid"], true)));
								} else {
									$dddddd = array("status" => 1, "opengid" => json_decode($condition["opengid"], true));
									if (!empty($prize_bimg)) {
										$dddddd["bimg"] = $prize_bimg;
										pdo_update("xc_train_active", array("is_total" => intval($active["is_total"]) + 1), array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
									}
									return $this->result(0, "分享成功", $dddddd);
								}
							} else {
								if (!empty($_GPC["openid"])) {
									return $this->result(1, "助力失败");
								} else {
									return $this->result(1, "分享失败");
								}
							}
						}
					} else {
						$condition["uniacid"] = $uniacid;
						if (!empty($_GPC["openid"])) {
							$condition["openid"] = $_GPC["openid"];
						} else {
							$condition["openid"] = $_W["openid"];
						}
						$condition["title"] = $active["name"];
						$condition["cid"] = $_GPC["id"];
						$condition["opengid"] = array();
						for ($i = 0; $i < intval($active["share"]); $i++) {
							$condition["opengid"][] = '';
						}
						$condition["opengid"][intval($_GPC["share"]) - 1] = $opengid;
						$condition["opengid"] = json_encode($condition["opengid"]);
						$condition["type"] = $active["type"];
						$request = pdo_insert("xc_train_prize", $condition);
						if ($request) {
							return $this->result(0, "分享成功", array("status" => 1, "opengid" => json_decode($condition["opengid"], true)));
						} else {
							if (!empty($_GPC["openid"])) {
								return $this->result(1, "助力失败2");
							} else {
								return $this->result(1, "分享失败");
							}
						}
					}
				} else {
					if (!empty($_GPC["openid"])) {
						return $this->result(1, "助力失败1");
					} else {
						return $this->result(1, "分享失败");
					}
				}
			}
		} else {
			return $this->result(1, "活动不存在");
		}
	}
	public function doPageSetOrder()
	{
		global $_GPC, $_W, $xcmodule;
		$uniacid = $_W["uniacid"];
		$share_status = -1;
		$share_one = '';
		$share_two = '';
		$share_three = '';
		$share_config = pdo_get("xc_train_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		$share_config = json_decode($share_config["content"], true);
		if (!empty($share_config)) {
			$share_status = $share_config["status"];
			if ($share_config["type"] >= 1 && !empty($share_config["referrer_one"])) {
				$share_one = $share_config["referrer_one"];
			}
			if ($share_config["type"] >= 2 && !empty($share_config["referrer_two"])) {
				$share_two = $share_config["referrer_two"];
			}
			if ($share_config["type"] >= 3 && !empty($share_config["referrer_three"])) {
				$share_three = $share_config["referrer_three"];
			}
		}
		$condition["can_use"] = 1;
		if (!empty($_GPC["pid"])) {
			$service = pdo_get("xc_train_service_team", array("status" => 1, "id" => $_GPC["pid"], "uniacid" => $uniacid));
			if ($service) {
				if (time() > strtotime($service["end_time"])) {
					return $this->result(1, "课程报名/预约已截止");
				}
				if (intval($service["member"]) + $_GPC["total"] > intval($service["more_member"])) {
					return $this->result(1, "人数超过最大人数");
				}
				$class = pdo_get("xc_train_service", array("status" => 1, "id" => $service["pid"], "uniacid" => $uniacid));
				if ($class) {
					$condition["amount"] = $class["price"];
					$condition["title"] = $class["name"] . "【" . $service["mark"] . "】";
					$condition["can_use"] = $class["can_use"];
					if (!empty($class["share_one"])) {
						$share_one = $class["share_one"];
					}
					if (!empty($class["share_two"])) {
						$share_two = $class["share_two"];
					}
					if (!empty($class["share_three"])) {
						$share_three = $class["share_three"];
					}
				}
			} else {
				return $this->result(1, "课程不存在");
			}
		} elseif (!empty($_GPC["cut"])) {
			$sql = "SELECT * FROM " . tablename("xc_train_cut") . " WHERE status=1 AND uniacid=:uniacid AND id=:id AND is_member<member AND unix_timestamp(end_time)>:times";
			$service = pdo_fetch($sql, array(":uniacid" => $uniacid, ":id" => $_GPC["cut"], ":times" => time()));
			if ($service) {
				$class = pdo_get("xc_train_service", array("status" => 1, "id" => $service["pid"], "uniacid" => $uniacid));
				if ($class) {
					$condition["amount"] = $class["price"];
					$condition["title"] = $class["name"] . "【" . $service["mark"] . "】";
					$condition["can_use"] = $class["can_use"];
					if (!empty($class["share_one"])) {
						$share_one = $class["share_one"];
					}
					if (!empty($class["share_two"])) {
						$share_two = $class["share_two"];
					}
					if (!empty($class["share_three"])) {
						$share_three = $class["share_three"];
					}
				}
				$order = pdo_get("xc_train_cut_order", array("openid" => $_W["openid"], "cid" => $service["id"], "status" => -1));
				if ($order) {
					$condition["amount"] = $order["price"];
				} else {
					$condition["amount"] = $service["price"];
				}
			} else {
				return $this->result(1, "已结束");
			}
		} elseif (!empty($_GPC["group_service"])) {
			$sql = "SELECT * FROM " . tablename("xc_train_service_group") . " WHERE status=1 AND uniacid=:uniacid AND id=:id AND member_on<member AND unix_timestamp(end_time)>:times";
			$service = pdo_fetch($sql, array(":uniacid" => $uniacid, ":id" => $_GPC["group_service"], ":times" => time()));
			if ($service) {
				if (!empty($service["format"])) {
					$service["format"] = json_decode($service["format"], true);
				}
				$class = pdo_get("xc_train_service", array("status" => 1, "id" => $service["pid"], "uniacid" => $uniacid));
				if ($class) {
					$condition["title"] = $class["name"] . "【" . $service["mark"] . "】";
					$condition["can_use"] = $class["can_use"];
					if (!empty($class["share_one"])) {
						$share_one = $class["share_one"];
					}
					if (!empty($class["share_two"])) {
						$share_two = $class["share_two"];
					}
					if (!empty($class["share_three"])) {
						$share_three = $class["share_three"];
					}
				}
				if (!empty($_GPC["group_id"])) {
					$group = pdo_get("xc_train_group_service", array("uniacid" => $_W["uniacid"], "id" => $_GPC["group_id"], "status" => -1, "failtime >" => date("Y-m-d H:i:s")));
					if ($group) {
						$condition["group_id"] = $_GPC["group_id"];
						$condition["amount"] = $group["group_price"];
						$condition["group_member"] = $group["member"];
						$condition["group_price"] = $group["group_price"];
						$condition["group_end"] = $group["failtime"];
					} else {
						return $this->result(1, "已结束");
					}
				} else {
					$condition["amount"] = $service["format"][$_GPC["group_param"]]["price"];
					$condition["group_member"] = $service["format"][$_GPC["group_param"]]["member"];
					$condition["group_price"] = $service["format"][$_GPC["group_param"]]["price"];
					$condition["group_end"] = date("Y-m-d H:i:s", time() + intval($service["group_times"]) * 3600);
				}
				$condition["group_data"] = json_encode($service);
			} else {
				return $this->result(1, "已结束");
			}
		}
		$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
		if ($config) {
			$config["content"] = json_decode($config["content"], true);
			if (!empty($config["content"]["online_limit"])) {
				if (intval($_GPC["total"]) > intval($config["content"]["online_limit"])) {
					return $this->result(1, "单次预约最多" . $config["content"]["online_limit"] . "人");
				}
			}
			if (!empty($config["content"]["sign_limit"])) {
				$sign_count = 0;
				$sign_order = pdo_count("xc_train_order", array("uniacid" => $uniacid, "openid" => $_W["openid"], "order_type IN" => array(1, 2), "pid" => $service["id"], "status" => 1));
				if ($sign_order) {
					$sign_count = $sign_order;
				}
				if (intval($sign_count) >= intval($config["content"]["sign_limit"])) {
					return $this->result(1, "报名次数已达上限");
				}
			}
		}
		$condition["uniacid"] = $uniacid;
		$condition["openid"] = $_W["openid"];
		$condition["out_trade_no"] = setcode();
		if (!empty($_GPC["pid"])) {
			$condition["pid"] = $_GPC["pid"];
			$condition["cut_status"] = -1;
		} else {
			if ($_GPC["cut"]) {
				$condition["pid"] = $_GPC["cut"];
				$condition["cut_status"] = 1;
			} elseif ($_GPC["group_service"]) {
				$condition["pid"] = $_GPC["group_service"];
			}
		}
		$condition["order_type"] = $_GPC["order_type"];
		$condition["total"] = $_GPC["total"];
		$condition["name"] = $_GPC["name"];
		$condition["mobile"] = $_GPC["mobile"];
		if (!empty($_GPC["mobile2"])) {
			$condition["mobile2"] = $_GPC["mobile2"];
		}
		if (!empty($_GPC["content"])) {
			$condition["content"] = $_GPC["content"];
		}
		if (!empty($_GPC["tui"])) {
			$condition["tui"] = $_GPC["tui"];
		}
		if (!empty($_GPC["store"])) {
			$condition["store"] = $_GPC["store"];
		}
		$condition["o_amount"] = $condition["amount"];
		if (!empty($_GPC["coupon_id"])) {
			$coupon = pdo_get("xc_train_coupon", array("id" => $_GPC["coupon_id"], "uniacid" => $uniacid));
			if ($coupon) {
				if (floatval($condition["amount"]) >= floatval($coupon["condition"]) && !empty($condition["amount"])) {
					$condition["coupon_id"] = $_GPC["coupon_id"];
					$condition["coupon_price"] = $coupon["name"];
					$condition["o_amount"] = round(floatval($condition["amount"]) - floatval($coupon["name"]), 2);
					if (floatval($condition["o_amount"]) < 0) {
						$condition["o_amount"] = 0;
					}
				}
			}
		}
		if (!empty($_GPC["form"])) {
			$condition["sign"] = htmlspecialchars_decode($_GPC["form"]);
		}
		$share_one_openid = '';
		$share_one_fee = '';
		$share_two_openid = '';
		$share_two_fee = '';
		$share_three_openid = '';
		$share_three_fee = '';
		$user = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($share_status == 1) {
			if (!empty($share_one) && !empty($user["share"])) {
				$share_one_openid = $user["share"];
				$share_one_fee = round(floatval($condition["o_amount"]) * floatval($share_one) / 100, 2);
				$user_one = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user["share"]));
				if ($user_one && !empty($user_one["share"]) && !empty($share_two)) {
					$share_two_openid = $user_one["share"];
					$share_two_fee = round(floatval($condition["o_amount"]) * floatval($share_two) / 100, 2);
					$user_two = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user_one["share"]));
					if ($user_two && !empty($user_two["share"]) && !empty($share_three)) {
						$share_three_openid = $user_two["share"];
						$share_three_fee = round(floatval($condition["o_amount"]) * floatval($share_three) / 100, 2);
					}
				}
			}
		}
		if (!empty($share_one_openid) && !empty($share_one_fee)) {
			$condition["share_one_openid"] = $share_one_openid;
			$condition["share_one_fee"] = $share_one_fee;
		}
		if (!empty($share_two_openid) && !empty($share_two_fee)) {
			$condition["share_two_openid"] = $share_two_openid;
			$condition["share_two_fee"] = $share_two_fee;
		}
		if (!empty($share_three_openid) && !empty($share_three_fee)) {
			$condition["share_three_openid"] = $share_three_openid;
			$condition["share_three_fee"] = $share_three_fee;
		}
		$request = pdo_insert("xc_train_order", $condition);
		if ($request) {
			$id = pdo_insertid();
			$moban_id = array();
			$moban = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "moban_pay"));
			if ($moban) {
				$moban = json_decode($moban["content"], true);
				if ($moban && $moban["status"] == 1 && !empty($moban["template_id"])) {
					$moban_id[] = $moban["template_id"];
				}
			}
			if ($condition["o_amount"] <= 0) {
				pdo_update("xc_train_order", array("status" => 1), array("uniacid" => $uniacid, "openid" => $_W["openid"], "out_trade_no" => $condition["out_trade_no"]));
				if ($condition["order_type"] == 7) {
					if (!empty($condition["group_id"])) {
						$group = pdo_get("xc_train_group_service", array("uniacid" => $_W["uniacid"], "id" => $condition["group_id"], "status" => -1, "failtime >" => date("Y-m-d H:i:s")));
						if ($group) {
							if (intval($group["member_on"]) + 1 == intval($group["member"])) {
								pdo_update("xc_train_group_service", array("member_on +=" => 1, "status" => 1), array("uniacid" => $_W["uniacid"], "id" => $group["id"]));
								pdo_update("xc_train_order", array("group_status" => 1), array("uniacid" => $_W["uniacid"], "status" => 1, "order_type" => 7, "group_id" => $group["id"], "group_status" => -1));
							} else {
								pdo_update("xc_train_group_service", array("member_on +=" => 1), array("uniacid" => $_W["uniacid"], "id" => $group["id"]));
							}
						}
					} else {
						$group = pdo_insert("xc_train_group_service", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "service" => $condition["pid"], "member_on" => 1, "member" => $condition["group_member"], "group_price" => $condition["group_price"], "failtime" => $condition["group_end"], "createtime" => date("Y-m-d H:i:s")));
						if ($group) {
							$group_id = pdo_insertid();
							pdo_update("xc_train_order", array("group_id" => $group_id), array("uniacid" => $uniacid, "openid" => $_W["openid"], "out_trade_no" => $condition["out_trade_no"]));
						}
					}
					pdo_update("xc_train_service_group", array("member_on +=" => 1), array("uniacid" => $_W["uniacid"], "id" => $condition["pid"]));
				} else {
					if ($condition["cut_status"] == -1) {
						$sql = "UPDATE " . tablename("xc_train_service_team") . " SET member=member+:member WHERE status=1 AND id=:id AND uniacid=:uniacid";
						pdo_query($sql, array(":member" => $condition["total"], ":id" => $condition["pid"], ":uniacid" => $uniacid));
					} elseif ($condition["cut_status"] == 1) {
						$sql = "UPDATE " . tablename("xc_train_cut") . " SET is_member=is_member+:member WHERE status=1 AND id=:id AND uniacid=:uniacid";
						pdo_query($sql, array(":member" => $condition["total"], ":id" => $condition["pid"], ":uniacid" => $uniacid));
						pdo_update("xc_train_cut_order", array("status" => 1), array("openid" => $_W["openid"], "uniacid" => $uniacid, "cid" => $condition["pid"]));
					}
				}
				if (!empty($condition["coupon_id"])) {
					pdo_update("xc_train_user_coupon", array("status" => 1), array("cid" => $condition["coupon_id"], "openid" => $_W["openid"], "uniacid" => $uniacid, "status" => -1));
				}
				$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
				if ($config) {
					$config["content"] = json_decode($config["content"], true);
				}
				$sms = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "sms"));
				if ($sms) {
					$sms["content"] = json_decode($sms["content"], true);
					$send_mobile = $sms["content"]["mobile"];
					if (!empty($_GPC["store"])) {
						$store = pdo_get("xc_train_school", array("id" => $_GPC["store"], "uniacid" => $uniacid));
						if ($store && !empty($store["sms"])) {
							$send_mobile = $store["sms"];
						}
					}
					if ($sms["content"]["status"] == 1) {
						require_once IA_ROOT . "/addons/xc_train/resource/sms/sendSms.php";
						if ($sms["content"]["type"] == 1) {
							set_time_limit(0);
							header("Content-Type: text/plain; charset=utf-8");
							$templateParam = array("webnamex" => $config["content"]["title"], "trade" => $condition["out_trade_no"], "amount" => "免费", "namex" => $condition["name"], "phonex" => $condition["mobile"], "service" => $condition["title"]);
							$send = new sms();
							$mobile = explode("/", $send_mobile);
							foreach ($mobile as $m) {
								$result = $send->sendSms($sms["content"]["AccessKeyId"], $sms["content"]["AccessKeySecret"], $sms["content"]["sign"], $sms["content"]["code"], $m, $templateParam);
							}
						} elseif ($sms["content"]["type"] == 2) {
							header("content-type:text/html;charset=utf-8");
							$sendUrl = "http://v.juhe.cn/sms/send";
							$tpl_value = "#webnamex#=" . $config["content"]["title"] . "&#trade#=" . $condition["out_trade_no"] . "&#amount#=免费&#namex#=" . $condition["name"] . "&#phonex#=" . $condition["mobile"] . "&#service#=" . $condition["title"];
							$mobile = explode("/", $send_mobile);
							foreach ($mobile as $m) {
								$smsConf = array("key" => $sms["content"]["key"], "mobile" => $m, "tpl_id" => $sms["content"]["tpl_id"], "tpl_value" => $tpl_value);
								$content = juhecurl($sendUrl, $smsConf, 1);
							}
							if ($content) {
								$result = json_decode($content, true);
								$error_code = $result["error_code"];
							}
						} elseif ($sms["content"]["type"] == 3) {
							if (!empty($sms["content"]["url"])) {
								$customize = $sms["content"]["customize"];
								$post = $sms["content"]["post"];
								if (is_array($post) && !empty($post)) {
									$post = json_encode($post);
									if (is_array($customize)) {
										foreach ($customize as $x) {
											$post = str_replace("{{" . $x["attr"] . "}}", $x["value"], $post);
										}
									}
									$post = str_replace("{{webnamex}}", $config["content"]["title"], $post);
									$post = str_replace("{{trade}}", $condition["out_trade_no"], $post);
									$post = str_replace("{{amount}}", "免费", $post);
									$post = str_replace("{{namex}}", $condition["name"], $post);
									$post = str_replace("{{phonex}}", $condition["mobile"], $post);
									$post = str_replace("{{service}}", $condition["title"], $post);
									$post = json_decode($post, true);
									$data = array();
									foreach ($post as $x2) {
										$data[$x2["attr"]] = $x2["value"];
									}
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
									curl_setopt($ch, CURLOPT_POST, 1);
									curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
									$output = curl_exec($ch);
									curl_close($ch);
								}
								$get = $sms["content"]["get"];
								if (is_array($get) && !empty($get)) {
									$get = json_encode($get);
									if (is_array($customize)) {
										foreach ($customize as $x) {
											$get = str_replace("{{" . $x["attr"] . "}}", $x["value"], $get);
										}
									}
									$get = str_replace("{{webnamex}}", $config["content"]["title"], $get);
									$get = str_replace("{{trade}}", $condition["out_trade_no"], $get);
									$get = str_replace("{{amount}}", "免费", $get);
									$get = str_replace("{{namex}}", $condition["name"], $get);
									$get = str_replace("{{phonex}}", $condition["mobile"], $get);
									$get = str_replace("{{service}}", $condition["title"], $get);
									$get = json_decode($get, true);
									$url_data = '';
									foreach ($get as $x3) {
										if (empty($url_data)) {
											$url_data = urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
										} else {
											$url_data = $url_data . "&" . urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
										}
									}
									if (strpos($sms["content"]["url"], "?") !== false) {
										$sms["content"]["url"] = $sms["content"]["url"] . $url_data;
									} else {
										$sms["content"]["url"] = $sms["content"]["url"] . "?" . $url_data;
									}
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
									curl_setopt($ch, CURLOPT_HEADER, 0);
									$output = curl_exec($ch);
									curl_close($ch);
								}
							}
						}
					}
				}
				$print = pdo_get("xc_train_config", array("xkey" => "print", "uniacid" => $uniacid));
				if ($print) {
					$print["content"] = json_decode($print["content"], true);
					if ($print["content"]["status"] == 1) {
						if ($print["content"]["type"] == 1) {
							$service_name = $service["name"];
							$time = time();
							$content = '';
							$content .= "订单号：" . $condition["out_trade_no"] . "\\r\\n";
							$content .= "小程序名：" . $config["content"]["title"] . "\\r\\n";
							$content .= "人数：" . $condition["total"] . "\\r\\n";
							$content .= "课程：" . $condition["title"] . "\\r\\n";
							$content .= "价格：免费\\r\\n";
							$content .= "姓名：" . $condition["name"] . "\\r\\n";
							$content .= "手机：" . $condition["mobile"] . "\\r\\n";
							$sign = strtoupper(md5($print["content"]["apikey"] . "machine_code" . $print["content"]["machine_code"] . "partner" . $print["content"]["partner"] . "time" . $time . $print["content"]["msign"]));
							$requestUrl = "http://open.10ss.net:8888";
							$requestAll = ["partner" => $print["content"]["partner"], "machine_code" => $print["content"]["machine_code"], "time" => $time, "content" => $content, "sign" => $sign];
							$requestInfo = http_build_query($requestAll);
							$request = push($requestInfo, $requestUrl);
						} elseif ($print["content"]["type"] == 2) {
							include dirname(__FILE__) . "/resource/HttpClient.class.php";
							define("USER", $print["content"]["user"]);
							define("UKEY", $print["content"]["ukey"]);
							define("SN", $print["content"]["sn"]);
							define("IP", "api.feieyun.cn");
							define("PORT", 80);
							define("PATH", "/Api/Open/");
							define("STIME", time());
							define("SIG", sha1(USER . UKEY . STIME));
							$orderInfo = "<CB>订单</CB><BR>";
							$orderInfo .= "订单号：" . $condition["out_trade_no"] . "<BR>";
							$orderInfo .= "小程序名：" . $config["content"]["title"] . "<BR>";
							$orderInfo .= "--------------------------------<BR>";
							$orderInfo .= "课程：" . $condition["title"] . "<BR>";
							$orderInfo .= "--------------------------------<BR>";
							$orderInfo .= "人数：" . $condition["total"] . "<BR>";
							$orderInfo .= "价格：免费<BR>";
							$orderInfo .= "姓名：" . $condition["name"] . "<BR>";
							$orderInfo .= "手机：" . $condition["mobile"] . "<BR>";
							$request = wp_print(SN, $orderInfo, 1);
						}
					}
				}
				$postdata = array();
				$postdata["status"] = 1;
				$postdata["id"] = $id;
				if (!empty($moban_id)) {
					$postdata["template_id"] = $moban_id;
				}
				return $this->result(0, "操作成功", $postdata);
			} else {
				if ($condition["o_amount"] > 0) {
					$sub_status = -1;
					$sub_appid = '';
					$sub_mch_id = '';
					$sub_key = '';
					$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $_W["uniacid"]));
					if ($config) {
						$config["content"] = json_decode($config["content"], true);
						if (!empty($config["content"]) && $config["content"]["sub_status"] && !empty($config["content"]["sub_appid"]) && !empty($config["content"]["sub_mch_id"]) && !empty($config["content"]["sub_key"])) {
							$sub_status = $config["content"]["sub_status"];
							$sub_appid = $config["content"]["sub_appid"];
							$sub_mch_id = $config["content"]["sub_mch_id"];
							$sub_key = $config["content"]["sub_key"];
						}
					}
					if ($sub_status == 1 && !empty($sub_appid) && !empty($sub_mch_id) && !empty($sub_key)) {
						$data["title"] = $condition["title"];
						$data["tid"] = $condition["out_trade_no"];
						$data["fee"] = $condition["o_amount"];
						$data["sub_appid"] = $sub_appid;
						$data["sub_mch_id"] = $sub_mch_id;
						$data["sub_key"] = $sub_key;
						$postdata = sub_pay($data, $_GPC, $_W);
					} else {
						$data["title"] = $condition["title"];
						$data["tid"] = $condition["out_trade_no"];
						$data["fee"] = $condition["o_amount"];
						$postdata = $this->pay($data);
					}
					$postdata["status"] = 2;
					$postdata["id"] = $id;
					if (!empty($moban_id)) {
						$postdata["template_id"] = $moban_id;
					}
					return $this->result(0, "操作成功", $postdata);
				}
			}
		} else {
			return $this->result(1, "生成订单失败");
		}
	}
	public function doPageBuyVideo()
	{
		global $_GPC, $_W;
		$uniacid = $_W["uniacid"];
		$share_status = -1;
		$share_one = '';
		$share_two = '';
		$share_three = '';
		$share_config = pdo_get("xc_train_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		$share_config = json_decode($share_config["content"], true);
		if (!empty($share_config)) {
			$share_status = $share_config["status"];
			if ($share_config["type"] >= 1 && !empty($share_config["referrer_one"])) {
				$share_one = $share_config["referrer_one"];
			}
			if ($share_config["type"] >= 2 && !empty($share_config["referrer_two"])) {
				$share_two = $share_config["referrer_two"];
			}
			if ($share_config["type"] >= 3 && !empty($share_config["referrer_three"])) {
				$share_three = $share_config["referrer_three"];
			}
		}
		$video = pdo_get("xc_train_video", array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
		if ($video) {
			if (!empty($video["share_one"])) {
				$share_one = $video["share_one"];
			}
			if (!empty($video["share_two"])) {
				$share_two = $video["share_two"];
			}
			if (!empty($video["share_three"])) {
				$share_three = $video["share_three"];
			}
			$condition["uniacid"] = $uniacid;
			$condition["openid"] = $_W["openid"];
			$condition["out_trade_no"] = setcode();
			$condition["pid"] = $_GPC["id"];
			$condition["order_type"] = 3;
			$condition["amount"] = $video["price"];
			$condition["title"] = $video["name"];
			$share_one_openid = '';
			$share_one_fee = '';
			$share_two_openid = '';
			$share_two_fee = '';
			$share_three_openid = '';
			$share_three_fee = '';
			$user = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
			if ($share_status == 1) {
				if (!empty($share_one) && !empty($user["share"])) {
					$share_one_openid = $user["share"];
					$share_one_fee = round(floatval($condition["amount"]) * floatval($share_one) / 100, 2);
					$user_one = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user["share"]));
					if ($user_one && !empty($user_one["share"]) && !empty($share_two)) {
						$share_two_openid = $user_one["share"];
						$share_two_fee = round(floatval($condition["amount"]) * floatval($share_two) / 100, 2);
						$user_two = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user_one["share"]));
						if ($user_two && !empty($user_two["share"]) && !empty($share_three)) {
							$share_three_openid = $user_two["share"];
							$share_three_fee = round(floatval($condition["amount"]) * floatval($share_three) / 100, 2);
						}
					}
				}
			}
			if (!empty($share_one_openid) && !empty($share_one_fee)) {
				$condition["share_one_openid"] = $share_one_openid;
				$condition["share_one_fee"] = $share_one_fee;
			}
			if (!empty($share_two_openid) && !empty($share_two_fee)) {
				$condition["share_two_openid"] = $share_two_openid;
				$condition["share_two_fee"] = $share_two_fee;
			}
			if (!empty($share_three_openid) && !empty($share_three_fee)) {
				$condition["share_three_openid"] = $share_three_openid;
				$condition["share_three_fee"] = $share_three_fee;
			}
			$request = pdo_insert("xc_train_order", $condition);
			if ($request) {
				$sub_status = -1;
				$sub_appid = '';
				$sub_mch_id = '';
				$sub_key = '';
				$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $_W["uniacid"]));
				if ($config) {
					$config["content"] = json_decode($config["content"], true);
					if (!empty($config["content"]) && $config["content"]["sub_status"] && !empty($config["content"]["sub_appid"]) && !empty($config["content"]["sub_mch_id"]) && !empty($config["content"]["sub_key"])) {
						$sub_status = $config["content"]["sub_status"];
						$sub_appid = $config["content"]["sub_appid"];
						$sub_mch_id = $config["content"]["sub_mch_id"];
						$sub_key = $config["content"]["sub_key"];
					}
				}
				if ($sub_status == 1 && !empty($sub_appid) && !empty($sub_mch_id) && !empty($sub_key)) {
					$data["title"] = $video["name"];
					$data["tid"] = $condition["out_trade_no"];
					$data["fee"] = $condition["amount"];
					$data["sub_appid"] = $sub_appid;
					$data["sub_mch_id"] = $sub_mch_id;
					$data["sub_key"] = $sub_key;
					$postdata = sub_pay($data, $_GPC, $_W);
				} else {
					$data["title"] = $video["name"];
					$data["tid"] = $condition["out_trade_no"];
					$data["fee"] = $condition["amount"];
					$postdata = $this->pay($data);
				}
				$postdata["status"] = 1;
				return $this->result(0, "操作成功", $postdata);
			} else {
				return $this->result(1, "生成订单失败");
			}
		} else {
			return $this->result(1, "该视频不存在");
		}
	}
	public function doPageBuyAudio()
	{
		global $_GPC, $_W;
		$uniacid = $_W["uniacid"];
		$share_status = -1;
		$share_one = '';
		$share_two = '';
		$share_three = '';
		$share_config = pdo_get("xc_train_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		$share_config = json_decode($share_config["content"], true);
		if (!empty($share_config)) {
			$share_status = $share_config["status"];
			if ($share_config["type"] >= 1 && !empty($share_config["referrer_one"])) {
				$share_one = $share_config["referrer_one"];
			}
			if ($share_config["type"] >= 2 && !empty($share_config["referrer_two"])) {
				$share_two = $share_config["referrer_two"];
			}
			if ($share_config["type"] >= 3 && !empty($share_config["referrer_three"])) {
				$share_three = $share_config["referrer_three"];
			}
		}
		$video = pdo_get("xc_train_audio", array("status" => 1, "uniacid" => $uniacid, "id" => $_GPC["id"]));
		if ($video) {
			if (!empty($video["share_one"])) {
				$share_one = $video["share_one"];
			}
			if (!empty($video["share_two"])) {
				$share_two = $video["share_two"];
			}
			if (!empty($video["share_three"])) {
				$share_three = $video["share_three"];
			}
			$condition["uniacid"] = $uniacid;
			$condition["openid"] = $_W["openid"];
			$condition["out_trade_no"] = setcode();
			$condition["pid"] = $_GPC["id"];
			$condition["order_type"] = 5;
			$condition["amount"] = $video["price"];
			$condition["title"] = $video["name"];
			$share_one_openid = '';
			$share_one_fee = '';
			$share_two_openid = '';
			$share_two_fee = '';
			$share_three_openid = '';
			$share_three_fee = '';
			$user = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
			if ($share_status == 1) {
				if (!empty($share_one) && !empty($user["share"])) {
					$share_one_openid = $user["share"];
					$share_one_fee = round(floatval($condition["amount"]) * floatval($share_one) / 100, 2);
					$user_one = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user["share"]));
					if ($user_one && !empty($user_one["share"]) && !empty($share_two)) {
						$share_two_openid = $user_one["share"];
						$share_two_fee = round(floatval($condition["amount"]) * floatval($share_two) / 100, 2);
						$user_two = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user_one["share"]));
						if ($user_two && !empty($user_two["share"]) && !empty($share_three)) {
							$share_three_openid = $user_two["share"];
							$share_three_fee = round(floatval($condition["amount"]) * floatval($share_three) / 100, 2);
						}
					}
				}
			}
			if (!empty($share_one_openid) && !empty($share_one_fee)) {
				$condition["share_one_openid"] = $share_one_openid;
				$condition["share_one_fee"] = $share_one_fee;
			}
			if (!empty($share_two_openid) && !empty($share_two_fee)) {
				$condition["share_two_openid"] = $share_two_openid;
				$condition["share_two_fee"] = $share_two_fee;
			}
			if (!empty($share_three_openid) && !empty($share_three_fee)) {
				$condition["share_three_openid"] = $share_three_openid;
				$condition["share_three_fee"] = $share_three_fee;
			}
			$request = pdo_insert("xc_train_order", $condition);
			if ($request) {
				$sql = "UPDATE " . tablename("xc_train_audio") . " SET sold=sold+1 WHERE uniacid=:uniacid AND id=:id";
				pdo_query($sql, array(":uniacid" => $uniacid, ":id" => $condition["pid"]));
				if (floatval($condition["amount"]) == 0) {
					pdo_update("xc_train_order", array("status" => 1), array("uniacid" => $uniacid, "out_trade_no" => $condition["out_trade_no"]));
					$postdata["status"] = 1;
				} else {
					$sub_status = -1;
					$sub_appid = '';
					$sub_mch_id = '';
					$sub_key = '';
					$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $_W["uniacid"]));
					if ($config) {
						$config["content"] = json_decode($config["content"], true);
						if (!empty($config["content"]) && $config["content"]["sub_status"] && !empty($config["content"]["sub_appid"]) && !empty($config["content"]["sub_mch_id"]) && !empty($config["content"]["sub_key"])) {
							$sub_status = $config["content"]["sub_status"];
							$sub_appid = $config["content"]["sub_appid"];
							$sub_mch_id = $config["content"]["sub_mch_id"];
							$sub_key = $config["content"]["sub_key"];
						}
					}
					if ($sub_status == 1 && !empty($sub_appid) && !empty($sub_mch_id) && !empty($sub_key)) {
						$data["title"] = $video["name"];
						$data["tid"] = $condition["out_trade_no"];
						$data["fee"] = $condition["amount"];
						$data["sub_appid"] = $sub_appid;
						$data["sub_mch_id"] = $sub_mch_id;
						$data["sub_key"] = $sub_key;
						$postdata = sub_pay($data, $_GPC, $_W);
					} else {
						$data["title"] = $video["name"];
						$data["tid"] = $condition["out_trade_no"];
						$data["fee"] = $condition["amount"];
						$postdata = $this->pay($data);
					}
					$postdata["status"] = 2;
				}
				return $this->result(0, "操作成功", $postdata);
			} else {
				return $this->result(1, "生成订单失败");
			}
		} else {
			return $this->result(1, "音频不存在");
		}
	}
	public function payResult($params)
	{
		global $_GPC, $_W;
		$uniacid = $_W["uniacid"];
		load()->func("logging");
		logging_run("记录字符串日志数据");
		logging_run($params);
		if ($params["result"] == "success" && $params["from"] == "notify") {
			$order = pdo_get("xc_train_order", array("uniacid" => $uniacid, "out_trade_no" => $params["tid"]));
			if ($order) {
				if ($order["order_type"] == 3 || $order["order_type"] == 5) {
					if (floatval($order["amount"]) == $params["fee"]) {
						$request = pdo_update("xc_train_order", array("status" => 1, "wx_out_trade_no" => $params["uniontid"]), array("id" => $order["id"], "uniacid" => $uniacid));
						if ($request) {
							if (!empty($order["share_one_openid"]) && !empty($order["share_one_fee"])) {
								pdo_update("xc_train_userinfo", array("share_fee +=" => floatval($order["share_one_fee"])), array("uniacid" => $_W["uniacid"], "openid" => $order["share_one_openid"]));
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_one_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 1, "amount" => $order["share_one_fee"], "order_amount" => $order["amount"], "status" => 1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
							if (!empty($order["share_two_openid"]) && !empty($order["share_two_fee"])) {
								pdo_update("xc_train_userinfo", array("share_fee +=" => floatval($order["share_two_fee"])), array("uniacid" => $_W["uniacid"], "openid" => $order["share_two_openid"]));
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_two_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 2, "amount" => $order["share_two_fee"], "order_amount" => $order["amount"], "status" => 1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
							if (!empty($order["share_three_openid"]) && !empty($order["share_three_fee"])) {
								pdo_update("xc_train_userinfo", array("share_fee +=" => floatval($order["share_three_fee"])), array("uniacid" => $_W["uniacid"], "openid" => $order["share_three_openid"]));
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_three_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 3, "amount" => $order["share_three_fee"], "order_amount" => $order["amount"], "status" => 1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
						}
					}
				} elseif ($order["order_type"] == 4) {
					if (floatval($order["o_amount"]) == $params["fee"]) {
						if (!empty($order["coupon_id"])) {
							pdo_update("xc_train_user_coupon", array("status" => 1), array("cid" => $order["coupon_id"], "openid" => $order["openid"], "uniacid" => $uniacid, "status" => -1));
						}
						$request = pdo_update("xc_train_order", array("status" => 1, "wx_out_trade_no" => $params["uniontid"]), array("id" => $order["id"], "uniacid" => $uniacid));
						if ($request) {
							if ($order["mall_type"] == 2) {
								if (!empty($order["group_id"])) {
									$group = pdo_get("xc_train_group", array("id" => $order["group_id"], "uniacid" => $uniacid));
									if ($group) {
										$group["group"] = json_decode($group["group"], true);
										$group["group"][] = $order["openid"];
										$group_data["is_member"] = intval($group["is_member"]) + 1;
										if ($group_data["is_member"] == $group["member"]) {
											$group_data["status"] = 1;
										}
										$group_data["group"] = json_encode($group["group"]);
										$group_request = pdo_update("xc_train_group", $group_data, array("id" => $order["group_id"], "uniacid" => $uniacid));
										if ($group_request) {
											if (!empty($group_data["status"])) {
												pdo_update("xc_train_order", array("group_status" => 1), array("group_id" => $order["group_id"], "status" => 1, "order_type" => 4, "mall_type" => 2));
												$group_order = pdo_getall("xc_train_order", array("group_id" => $order["group_id"], "status" => 1, "order_type" => 4, "mall_type" => 2));
												if ($group_order) {
													foreach ($group_order as $go) {
														if (!empty($go["share_one_openid"]) && !empty($go["share_one_fee"])) {
															pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $go["share_one_openid"], "out_trade_no" => $go["out_trade_no"], "type" => 1, "amount" => $go["share_one_fee"], "order_amount" => $go["amount"], "status" => -1, "share" => $go["openid"], "createtime" => date("Y-m-d H:i:s")));
														}
														if (!empty($go["share_two_openid"]) && !empty($go["share_two_fee"])) {
															pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $go["share_two_openid"], "out_trade_no" => $go["out_trade_no"], "type" => 2, "amount" => $go["share_two_fee"], "order_amount" => $go["amount"], "status" => -1, "share" => $go["openid"], "createtime" => date("Y-m-d H:i:s")));
														}
														if (!empty($go["share_three_openid"]) && !empty($go["share_three_fee"])) {
															pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $go["share_three_openid"], "out_trade_no" => $go["out_trade_no"], "type" => 3, "amount" => $go["share_three_fee"], "order_amount" => $go["amount"], "status" => -1, "share" => $go["openid"], "createtime" => date("Y-m-d H:i:s")));
														}
													}
												}
											}
										}
									}
								} else {
									$service = pdo_get("xc_train_mall", array("id" => $order["pid"], "uniacid" => $uniacid));
									pdo_insert("xc_train_group", array("uniacid" => $uniacid, "openid" => $order["openid"], "service" => $order["pid"], "is_member" => 1, "member" => $service["group_member"], "failtime" => date("Y-m-d H:i:s", time() + intval($service["group_fail"]) * 60 * 60), "group" => json_encode(array($order["openid"]))));
									$id = pdo_insertid();
									pdo_update("xc_train_order", array("group_id" => $id), array("out_trade_no" => $order["out_trade_no"], "uniacid" => $uniacid));
								}
							} else {
								if (!empty($order["share_one_openid"]) && !empty($order["share_one_fee"])) {
									pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_one_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 1, "amount" => $order["share_one_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
								}
								if (!empty($order["share_two_openid"]) && !empty($order["share_two_fee"])) {
									pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_two_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 2, "amount" => $order["share_two_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
								}
								if (!empty($order["share_three_openid"]) && !empty($order["share_three_fee"])) {
									pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_three_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 3, "amount" => $order["share_three_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
								}
							}
							$address = json_decode($order["userinfo"], true);
							$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
							if ($config) {
								$config["content"] = json_decode($config["content"], true);
							}
							$sms = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "sms"));
							if ($sms) {
								$sms["content"] = json_decode($sms["content"], true);
								if ($sms["content"]["status"] == 1) {
									$send_mobile = $sms["content"]["mobile"];
									if (!empty($order["store"])) {
										$store = pdo_get("xc_train_school", array("id" => $order["store"], "uniacid" => $uniacid));
										if ($store && !empty($store["sms"])) {
											$send_mobile = $store["sms"];
										}
									}
									require_once IA_ROOT . "/addons/xc_train/resource/sms/sendSms.php";
									if ($sms["content"]["type"] == 1) {
										set_time_limit(0);
										header("Content-Type: text/plain; charset=utf-8");
										$templateParam = array("webnamex" => $config["content"]["title"], "trade" => $order["out_trade_no"], "amount" => $order["o_amount"], "namex" => $address["name"], "phonex" => $address["mobile"], "service" => $order["title"]);
										$send = new sms();
										$mobile = explode("/", $send_mobile);
										foreach ($mobile as $m) {
											$result = $send->sendSms($sms["content"]["AccessKeyId"], $sms["content"]["AccessKeySecret"], $sms["content"]["sign"], $sms["content"]["code"], $m, $templateParam);
										}
									} elseif ($sms["content"]["type"] == 2) {
										header("content-type:text/html;charset=utf-8");
										$sendUrl = "http://v.juhe.cn/sms/send";
										$tpl_value = "#webnamex#=" . $config["content"]["title"] . "&#trade#=" . $order["out_trade_no"] . "&#amount#=" . $order["o_amount"] . "&#namex#=" . $address["name"] . "&#phonex#=" . $address["mobile"] . "&#service#=" . $order["title"];
										$mobile = explode("/", $send_mobile);
										foreach ($mobile as $m) {
											$smsConf = array("key" => $sms["content"]["key"], "mobile" => $m, "tpl_id" => $sms["content"]["tpl_id"], "tpl_value" => $tpl_value);
											$content = juhecurl($sendUrl, $smsConf, 1);
										}
										if ($content) {
											$result = json_decode($content, true);
											$error_code = $result["error_code"];
										}
									} elseif ($sms["content"]["type"] == 3) {
										if (!empty($sms["content"]["url"])) {
											$customize = $sms["content"]["customize"];
											$post = $sms["content"]["post"];
											if (is_array($post) && !empty($post)) {
												$post = json_encode($post);
												if (is_array($customize)) {
													foreach ($customize as $x) {
														$post = str_replace("{{" . $x["attr"] . "}}", $x["value"], $post);
													}
												}
												$post = str_replace("{{webnamex}}", $config["content"]["title"], $post);
												$post = str_replace("{{trade}}", $order["out_trade_no"], $post);
												$post = str_replace("{{amount}}", $order["o_amount"], $post);
												$post = str_replace("{{namex}}", $address["name"], $post);
												$post = str_replace("{{phonex}}", $address["mobile"], $post);
												$post = str_replace("{{service}}", $order["title"], $post);
												$post = json_decode($post, true);
												$data = array();
												foreach ($post as $x2) {
													$data[$x2["attr"]] = $x2["value"];
												}
												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
												curl_setopt($ch, CURLOPT_POST, 1);
												curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
												$output = curl_exec($ch);
												curl_close($ch);
											}
											$get = $sms["content"]["get"];
											if (is_array($get) && !empty($get)) {
												$get = json_encode($get);
												if (is_array($customize)) {
													foreach ($customize as $x) {
														$get = str_replace("{{" . $x["attr"] . "}}", $x["value"], $get);
													}
												}
												$get = str_replace("{{webnamex}}", $config["content"]["title"], $get);
												$get = str_replace("{{trade}}", $order["out_trade_no"], $get);
												$get = str_replace("{{amount}}", $order["o_amount"], $get);
												$get = str_replace("{{namex}}", $address["name"], $get);
												$get = str_replace("{{phonex}}", $address["mobile"], $get);
												$get = str_replace("{{service}}", $order["title"], $get);
												$get = json_decode($get, true);
												$url_data = '';
												foreach ($get as $x3) {
													if (empty($url_data)) {
														$url_data = urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
													} else {
														$url_data = $url_data . "&" . urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
													}
												}
												if (strpos($sms["content"]["url"], "?") !== false) {
													$sms["content"]["url"] = $sms["content"]["url"] . $url_data;
												} else {
													$sms["content"]["url"] = $sms["content"]["url"] . "?" . $url_data;
												}
												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
												curl_setopt($ch, CURLOPT_HEADER, 0);
												$output = curl_exec($ch);
												curl_close($ch);
											}
										}
									}
								}
							}
							$print = pdo_get("xc_train_config", array("xkey" => "print", "uniacid" => $uniacid));
							if ($print) {
								$print["content"] = json_decode($print["content"], true);
								if ($print["content"]["status"] == 1) {
									if ($print["content"]["type"] == 1) {
										$time = time();
										$content = '';
										$content .= "订单号：" . $order["out_trade_no"] . "\\r\\n";
										$content .= "小程序名：" . $config["content"]["title"] . "\\r\\n";
										$content .= "人数：" . $order["total"] . "\\r\\n";
										$content .= "商品：" . $order["title"] . "\\r\\n";
										$content .= "价格：" . $order["o_amount"] . "\\r\\n";
										$content .= "姓名：" . $address["name"] . "\\r\\n";
										$content .= "手机：" . $address["mobile"] . "\\r\\n";
										$sign = strtoupper(md5($print["content"]["apikey"] . "machine_code" . $print["content"]["machine_code"] . "partner" . $print["content"]["partner"] . "time" . $time . $print["content"]["msign"]));
										$requestUrl = "http://open.10ss.net:8888";
										$requestAll = ["partner" => $print["content"]["partner"], "machine_code" => $print["content"]["machine_code"], "time" => $time, "content" => $content, "sign" => $sign];
										$requestInfo = http_build_query($requestAll);
										$request = push($requestInfo, $requestUrl);
									} elseif ($print["content"]["type"] == 2) {
										include IA_ROOT . "/addons/xc_train/resource/HttpClient.class.php";
										define("USER", $print["content"]["user"]);
										define("UKEY", $print["content"]["ukey"]);
										define("SN", $print["content"]["sn"]);
										define("IP", "api.feieyun.cn");
										define("PORT", 80);
										define("PATH", "/Api/Open/");
										define("STIME", time());
										define("SIG", sha1(USER . UKEY . STIME));
										$orderInfo = "<CB>订单</CB><BR>";
										$orderInfo .= "订单号：" . $order["out_trade_no"] . "<BR>";
										$orderInfo .= "小程序名：" . $config["content"]["title"] . "<BR>";
										$orderInfo .= "--------------------------------<BR>";
										$orderInfo .= "商品：" . $order["title"] . "<BR>";
										$orderInfo .= "--------------------------------<BR>";
										$orderInfo .= "人数：" . $order["total"] . "<BR>";
										$orderInfo .= "价格：" . $order["o_amount"] . "<BR>";
										$orderInfo .= "姓名：" . $address["name"] . "<BR>";
										$orderInfo .= "手机：" . $address["mobile"] . "<BR>";
										$request = wp_print(SN, $orderInfo, 1);
									}
								}
							}
						}
					}
				} elseif ($order["order_type"] == 6) {
					if (floatval($order["amount"]) == $params["fee"]) {
						$request = pdo_update("xc_train_order", array("status" => 1, "wx_out_trade_no" => $params["uniontid"]), array("id" => $order["id"], "uniacid" => $uniacid));
						if ($request) {
							$line = json_decode($order["line_data"], true);
							if ($line) {
								if (!empty($line["video"])) {
									$line["video"] = json_decode($line["video"], true);
									foreach ($line["video"] as $lv) {
										pdo_insert("xc_train_line_order", array("uniacid" => $_W["uniacid"], "openid" => $order["openid"], "out_trade_no" => $order["out_trade_no"], "line" => $order["pid"], "type" => 1, "pid" => $lv["id"], "createtime" => date("Y-m-d H:i:s")));
									}
								}
								if (!empty($line["audio"])) {
									$line["audio"] = json_decode($line["audio"], true);
									foreach ($line["audio"] as $la) {
										pdo_insert("xc_train_line_order", array("uniacid" => $_W["uniacid"], "openid" => $order["openid"], "out_trade_no" => $order["out_trade_no"], "line" => $order["pid"], "type" => 2, "pid" => $la["id"], "createtime" => date("Y-m-d H:i:s")));
									}
								}
							}
							if (!empty($order["share_one_openid"]) && !empty($order["share_one_fee"])) {
								pdo_update("xc_train_userinfo", array("share_fee +=" => floatval($order["share_one_fee"])), array("uniacid" => $_W["uniacid"], "openid" => $order["share_one_openid"]));
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_one_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 1, "amount" => $order["share_one_fee"], "order_amount" => $order["amount"], "status" => 1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
							if (!empty($order["share_two_openid"]) && !empty($order["share_two_fee"])) {
								pdo_update("xc_train_userinfo", array("share_fee +=" => floatval($order["share_two_fee"])), array("uniacid" => $_W["uniacid"], "openid" => $order["share_two_openid"]));
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_two_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 2, "amount" => $order["share_two_fee"], "order_amount" => $order["amount"], "status" => 1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
							if (!empty($order["share_three_openid"]) && !empty($order["share_three_fee"])) {
								pdo_update("xc_train_userinfo", array("share_fee +=" => floatval($order["share_three_fee"])), array("uniacid" => $_W["uniacid"], "openid" => $order["share_three_openid"]));
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_three_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 3, "amount" => $order["share_three_fee"], "order_amount" => $order["amount"], "status" => 1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
						}
					}
				} elseif ($order["order_type"] == 9) {
					if (floatval($order["amount"]) == $params["fee"]) {
						$request = pdo_update("xc_train_order", array("status" => 1, "wx_out_trade_no" => $params["uniontid"]), array("id" => $order["id"], "uniacid" => $uniacid));
						if ($request) {
							pdo_update("xc_train_move", array("member_on +=" => $order["total"]), array("uniacid" => $_W["uniacid"], "id" => $order["pid"]));
							if (!empty($order["share_one_openid"]) && !empty($order["share_one_fee"])) {
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_one_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 1, "amount" => $order["share_one_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
							if (!empty($order["share_two_openid"]) && !empty($order["share_two_fee"])) {
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_two_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 2, "amount" => $order["share_two_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
							if (!empty($order["share_three_openid"]) && !empty($order["share_three_fee"])) {
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_three_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 3, "amount" => $order["share_three_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
							$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
							if ($config) {
								$config["content"] = json_decode($config["content"], true);
							}
							$sms = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "sms"));
							if ($sms) {
								$sms["content"] = json_decode($sms["content"], true);
								if ($sms["content"]["status"] == 1) {
									$send_mobile = $sms["content"]["mobile"];
									require_once IA_ROOT . "/addons/xc_train/resource/sms/sendSms.php";
									if ($sms["content"]["type"] == 1) {
										set_time_limit(0);
										header("Content-Type: text/plain; charset=utf-8");
										$templateParam = array("webnamex" => $config["content"]["title"], "trade" => $order["out_trade_no"], "amount" => $order["o_amount"], "namex" => $order["name"], "phonex" => $order["mobile"], "service" => $order["service_name"]);
										$send = new sms();
										$mobile = explode("/", $send_mobile);
										foreach ($mobile as $m) {
											$result = $send->sendSms($sms["content"]["AccessKeyId"], $sms["content"]["AccessKeySecret"], $sms["content"]["sign"], $sms["content"]["code"], $m, $templateParam);
										}
									} elseif ($sms["content"]["type"] == 2) {
										header("content-type:text/html;charset=utf-8");
										$sendUrl = "http://v.juhe.cn/sms/send";
										$tpl_value = "#webnamex#=" . $config["content"]["title"] . "&#trade#=" . $order["out_trade_no"] . "&#amount#=" . $order["o_amount"] . "&#namex#=" . $order["name"] . "&#phonex#=" . $order["mobile"] . "&#service#=" . $order["service_name"];
										$mobile = explode("/", $send_mobile);
										foreach ($mobile as $m) {
											$smsConf = array("key" => $sms["content"]["key"], "mobile" => $m, "tpl_id" => $sms["content"]["tpl_id"], "tpl_value" => $tpl_value);
											$content = juhecurl($sendUrl, $smsConf, 1);
										}
										if ($content) {
											$result = json_decode($content, true);
											$error_code = $result["error_code"];
										}
									} elseif ($sms["content"]["type"] == 3) {
										if (!empty($sms["content"]["url"])) {
											$customize = $sms["content"]["customize"];
											$post = $sms["content"]["post"];
											if (is_array($post) && !empty($post)) {
												$post = json_encode($post);
												if (is_array($customize)) {
													foreach ($customize as $x) {
														$post = str_replace("{{" . $x["attr"] . "}}", $x["value"], $post);
													}
												}
												$post = str_replace("{{webnamex}}", $config["content"]["title"], $post);
												$post = str_replace("{{trade}}", $order["out_trade_no"], $post);
												$post = str_replace("{{amount}}", $order["o_amount"], $post);
												$post = str_replace("{{namex}}", $order["name"], $post);
												$post = str_replace("{{phonex}}", $order["mobile"], $post);
												$post = str_replace("{{service}}", $order["service_name"], $post);
												$post = json_decode($post, true);
												$data = array();
												foreach ($post as $x2) {
													$data[$x2["attr"]] = $x2["value"];
												}
												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
												curl_setopt($ch, CURLOPT_POST, 1);
												curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
												$output = curl_exec($ch);
												curl_close($ch);
											}
											$get = $sms["content"]["get"];
											if (is_array($get) && !empty($get)) {
												$get = json_encode($get);
												if (is_array($customize)) {
													foreach ($customize as $x) {
														$get = str_replace("{{" . $x["attr"] . "}}", $x["value"], $get);
													}
												}
												$get = str_replace("{{webnamex}}", $config["content"]["title"], $get);
												$get = str_replace("{{trade}}", $order["out_trade_no"], $get);
												$get = str_replace("{{amount}}", $order["o_amount"], $get);
												$get = str_replace("{{namex}}", $order["name"], $get);
												$get = str_replace("{{phonex}}", $order["mobile"], $get);
												$get = str_replace("{{service}}", $order["service_name"], $get);
												$get = json_decode($get, true);
												$url_data = '';
												foreach ($get as $x3) {
													if (empty($url_data)) {
														$url_data = urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
													} else {
														$url_data = $url_data . "&" . urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
													}
												}
												if (strpos($sms["content"]["url"], "?") !== false) {
													$sms["content"]["url"] = $sms["content"]["url"] . $url_data;
												} else {
													$sms["content"]["url"] = $sms["content"]["url"] . "?" . $url_data;
												}
												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
												curl_setopt($ch, CURLOPT_HEADER, 0);
												$output = curl_exec($ch);
												curl_close($ch);
											}
										}
									}
								}
							}
							$print = pdo_get("xc_train_config", array("xkey" => "print", "uniacid" => $uniacid));
							if ($print) {
								$print["content"] = json_decode($print["content"], true);
								if ($print["content"]["status"] == 1) {
									if ($print["content"]["type"] == 1) {
										$time = time();
										$content = '';
										$content .= "订单号：" . $order["out_trade_no"] . "\\r\\n";
										$content .= "小程序名：" . $config["content"]["title"] . "\\r\\n";
										$content .= "人数：" . $order["total"] . "\\r\\n";
										$content .= "活动：" . $order["service_name"] . "\\r\\n";
										$content .= "价格：" . $order["o_amount"] . "\\r\\n";
										$content .= "姓名：" . $order["name"] . "\\r\\n";
										$content .= "手机：" . $order["mobile"] . "\\r\\n";
										$sign = strtoupper(md5($print["content"]["apikey"] . "machine_code" . $print["content"]["machine_code"] . "partner" . $print["content"]["partner"] . "time" . $time . $print["content"]["msign"]));
										$requestUrl = "http://open.10ss.net:8888";
										$requestAll = ["partner" => $print["content"]["partner"], "machine_code" => $print["content"]["machine_code"], "time" => $time, "content" => $content, "sign" => $sign];
										$requestInfo = http_build_query($requestAll);
										$request = push($requestInfo, $requestUrl);
									} elseif ($print["content"]["type"] == 2) {
										include dirname(__FILE__) . "/resource/HttpClient.class.php";
										define("USER", $print["content"]["user"]);
										define("UKEY", $print["content"]["ukey"]);
										define("SN", $print["content"]["sn"]);
										define("IP", "api.feieyun.cn");
										define("PORT", 80);
										define("PATH", "/Api/Open/");
										define("STIME", time());
										define("SIG", sha1(USER . UKEY . STIME));
										$orderInfo = "<CB>订单</CB><BR>";
										$orderInfo .= "订单号：" . $order["out_trade_no"] . "<BR>";
										$orderInfo .= "小程序名：" . $config["content"]["title"] . "<BR>";
										$orderInfo .= "--------------------------------<BR>";
										$orderInfo .= "活动：" . $order["service_name"] . "<BR>";
										$orderInfo .= "--------------------------------<BR>";
										$orderInfo .= "人数：" . $order["total"] . "<BR>";
										$orderInfo .= "价格：" . $order["o_amount"] . "<BR>";
										$orderInfo .= "姓名：" . $order["name"] . "<BR>";
										$orderInfo .= "手机：" . $order["mobile"] . "<BR>";
										$request = wp_print(SN, $orderInfo, 1);
									}
								}
							}
						}
					}
				} elseif ($order["order_type"] == 10) {
					if (floatval($order["o_amount"]) == $params["fee"]) {
						if (!empty($order["coupon_id"])) {
							pdo_update("xc_train_user_coupon", array("status" => 1), array("cid" => $order["coupon_id"], "openid" => $order["openid"], "uniacid" => $uniacid, "status" => -1));
						}
						$request = pdo_update("xc_train_order", array("status" => 1, "wx_out_trade_no" => $params["uniontid"]), array("id" => $order["id"], "uniacid" => $uniacid));
						if ($request) {
							pdo_update("xc_train_mall_team", array("kucun -=" => $order["total"]), array("uniacid" => $_W["uniacid"], "id" => $order["pid"]));
							if (!empty($order["group_id"])) {
								pdo_update("xc_train_team_group", array("member +=" => 1), array("uniacid" => $_W["uniacid"], "id" => $order["group_id"]));
							} else {
								pdo_insert("xc_train_team_group", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "service" => $order["pid"], "member" => 1, "start_time" => $order["start_time"], "end_time" => $order["end_time"], "createtime" => date("Y-m-d H:i:s")));
								$group_id = pdo_insertid();
								pdo_update("xc_train_order", array("group_id" => $group_id), array("out_trade_no" => $order["out_trade_no"], "uniacid" => $uniacid));
							}
							if (!empty($order["share_one_openid"]) && !empty($order["share_one_fee"])) {
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_one_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 1, "amount" => $order["share_one_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
							if (!empty($order["share_two_openid"]) && !empty($order["share_two_fee"])) {
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_two_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 2, "amount" => $order["share_two_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
							if (!empty($order["share_three_openid"]) && !empty($order["share_three_fee"])) {
								pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_three_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 3, "amount" => $order["share_three_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
							}
							$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
							if ($config) {
								$config["content"] = json_decode($config["content"], true);
							}
							$sms = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "sms"));
							if ($sms) {
								$sms["content"] = json_decode($sms["content"], true);
								if ($sms["content"]["status"] == 1) {
									$send_mobile = $sms["content"]["mobile"];
									if (!empty($order["store"])) {
										$store = pdo_get("xc_train_school", array("id" => $order["store"], "uniacid" => $uniacid));
										if ($store && !empty($store["sms"])) {
											$send_mobile = $store["sms"];
										}
									}
									require_once IA_ROOT . "/addons/xc_train/resource/sms/sendSms.php";
									if ($sms["content"]["type"] == 1) {
										set_time_limit(0);
										header("Content-Type: text/plain; charset=utf-8");
										$templateParam = array("webnamex" => $config["content"]["title"], "trade" => $order["out_trade_no"], "amount" => $order["o_amount"], "namex" => $order["name"], "phonex" => $order["mobile"], "service" => $order["title"]);
										$send = new sms();
										$mobile = explode("/", $send_mobile);
										foreach ($mobile as $m) {
											$result = $send->sendSms($sms["content"]["AccessKeyId"], $sms["content"]["AccessKeySecret"], $sms["content"]["sign"], $sms["content"]["code"], $m, $templateParam);
										}
									} elseif ($sms["content"]["type"] == 2) {
										header("content-type:text/html;charset=utf-8");
										$sendUrl = "http://v.juhe.cn/sms/send";
										$tpl_value = "#webnamex#=" . $config["content"]["title"] . "&#trade#=" . $order["out_trade_no"] . "&#amount#=" . $order["o_amount"] . "&#namex#=" . $order["name"] . "&#phonex#=" . $order["mobile"] . "&#service#=" . $order["title"];
										$mobile = explode("/", $send_mobile);
										foreach ($mobile as $m) {
											$smsConf = array("key" => $sms["content"]["key"], "mobile" => $m, "tpl_id" => $sms["content"]["tpl_id"], "tpl_value" => $tpl_value);
											$content = juhecurl($sendUrl, $smsConf, 1);
										}
										if ($content) {
											$result = json_decode($content, true);
											$error_code = $result["error_code"];
										}
									} elseif ($sms["content"]["type"] == 3) {
										if (!empty($sms["content"]["url"])) {
											$customize = $sms["content"]["customize"];
											$post = $sms["content"]["post"];
											if (is_array($post) && !empty($post)) {
												$post = json_encode($post);
												if (is_array($customize)) {
													foreach ($customize as $x) {
														$post = str_replace("{{" . $x["attr"] . "}}", $x["value"], $post);
													}
												}
												$post = str_replace("{{webnamex}}", $config["content"]["title"], $post);
												$post = str_replace("{{trade}}", $order["out_trade_no"], $post);
												$post = str_replace("{{amount}}", $order["o_amount"], $post);
												$post = str_replace("{{namex}}", $order["name"], $post);
												$post = str_replace("{{phonex}}", $order["mobile"], $post);
												$post = str_replace("{{service}}", $order["title"], $post);
												$post = json_decode($post, true);
												$data = array();
												foreach ($post as $x2) {
													$data[$x2["attr"]] = $x2["value"];
												}
												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
												curl_setopt($ch, CURLOPT_POST, 1);
												curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
												$output = curl_exec($ch);
												curl_close($ch);
											}
											$get = $sms["content"]["get"];
											if (is_array($get) && !empty($get)) {
												$get = json_encode($get);
												if (is_array($customize)) {
													foreach ($customize as $x) {
														$get = str_replace("{{" . $x["attr"] . "}}", $x["value"], $get);
													}
												}
												$get = str_replace("{{webnamex}}", $config["content"]["title"], $get);
												$get = str_replace("{{trade}}", $order["out_trade_no"], $get);
												$get = str_replace("{{amount}}", $order["o_amount"], $get);
												$get = str_replace("{{namex}}", $order["name"], $get);
												$get = str_replace("{{phonex}}", $order["mobile"], $get);
												$get = str_replace("{{service}}", $order["title"], $get);
												$get = json_decode($get, true);
												$url_data = '';
												foreach ($get as $x3) {
													if (empty($url_data)) {
														$url_data = urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
													} else {
														$url_data = $url_data . "&" . urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
													}
												}
												if (strpos($sms["content"]["url"], "?") !== false) {
													$sms["content"]["url"] = $sms["content"]["url"] . $url_data;
												} else {
													$sms["content"]["url"] = $sms["content"]["url"] . "?" . $url_data;
												}
												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
												curl_setopt($ch, CURLOPT_HEADER, 0);
												$output = curl_exec($ch);
												curl_close($ch);
											}
										}
									}
								}
							}
							$print = pdo_get("xc_train_config", array("xkey" => "print", "uniacid" => $uniacid));
							if ($print) {
								$print["content"] = json_decode($print["content"], true);
								if ($print["content"]["status"] == 1) {
									if ($print["content"]["type"] == 1) {
										$time = time();
										$content = '';
										$content .= "订单号：" . $order["out_trade_no"] . "\\r\\n";
										$content .= "小程序名：" . $config["content"]["title"] . "\\r\\n";
										$content .= "人数：" . $order["total"] . "\\r\\n";
										$content .= "商品：" . $order["title"] . "\\r\\n";
										$content .= "价格：" . $order["o_amount"] . "\\r\\n";
										$content .= "姓名：" . $order["name"] . "\\r\\n";
										$content .= "手机：" . $order["mobile"] . "\\r\\n";
										$sign = strtoupper(md5($print["content"]["apikey"] . "machine_code" . $print["content"]["machine_code"] . "partner" . $print["content"]["partner"] . "time" . $time . $print["content"]["msign"]));
										$requestUrl = "http://open.10ss.net:8888";
										$requestAll = ["partner" => $print["content"]["partner"], "machine_code" => $print["content"]["machine_code"], "time" => $time, "content" => $content, "sign" => $sign];
										$requestInfo = http_build_query($requestAll);
										$request = push($requestInfo, $requestUrl);
									} elseif ($print["content"]["type"] == 2) {
										include IA_ROOT . "/addons/xc_train/resource/HttpClient.class.php";
										define("USER", $print["content"]["user"]);
										define("UKEY", $print["content"]["ukey"]);
										define("SN", $print["content"]["sn"]);
										define("IP", "api.feieyun.cn");
										define("PORT", 80);
										define("PATH", "/Api/Open/");
										define("STIME", time());
										define("SIG", sha1(USER . UKEY . STIME));
										$orderInfo = "<CB>订单</CB><BR>";
										$orderInfo .= "订单号：" . $order["out_trade_no"] . "<BR>";
										$orderInfo .= "小程序名：" . $config["content"]["title"] . "<BR>";
										$orderInfo .= "--------------------------------<BR>";
										$orderInfo .= "商品：" . $order["title"] . "<BR>";
										$orderInfo .= "--------------------------------<BR>";
										$orderInfo .= "人数：" . $order["total"] . "<BR>";
										$orderInfo .= "价格：" . $order["o_amount"] . "<BR>";
										$orderInfo .= "姓名：" . $order["name"] . "<BR>";
										$orderInfo .= "手机：" . $order["mobile"] . "<BR>";
										$request = wp_print(SN, $orderInfo, 1);
									}
								}
							}
						}
					}
				} elseif (floatval($order["o_amount"]) == $params["fee"]) {
					if (!empty($order["coupon_id"])) {
						pdo_update("xc_train_user_coupon", array("status" => 1), array("cid" => $order["coupon_id"], "openid" => $order["openid"], "uniacid" => $uniacid, "status" => -1));
					}
					$request = pdo_update("xc_train_order", array("status" => 1, "wx_out_trade_no" => $params["uniontid"]), array("id" => $order["id"], "uniacid" => $uniacid));
					if ($request) {
						if ($order["order_type"] == 7) {
							if (!empty($order["group_id"])) {
								$group = pdo_get("xc_train_group_service", array("uniacid" => $_W["uniacid"], "id" => $order["group_id"], "status" => -1, "failtime >" => date("Y-m-d H:i:s")));
								if ($group) {
									if (intval($group["member_on"]) + 1 == intval($group["member"])) {
										pdo_update("xc_train_group_service", array("member_on +=" => 1, "status" => 1), array("uniacid" => $_W["uniacid"], "id" => $group["id"]));
										pdo_update("xc_train_order", array("group_status" => 1), array("uniacid" => $_W["uniacid"], "status" => 1, "order_type" => 7, "group_id" => $group["id"], "group_status" => -1));
									} else {
										pdo_update("xc_train_group_service", array("member_on +=" => 1), array("uniacid" => $_W["uniacid"], "id" => $group["id"]));
									}
								}
							} else {
								$group = pdo_insert("xc_train_group_service", array("uniacid" => $_W["uniacid"], "openid" => $order["openid"], "service" => $order["pid"], "member_on" => 1, "member" => $order["group_member"], "group_price" => $order["group_price"], "failtime" => $order["group_end"], "createtime" => date("Y-m-d H:i:s")));
								if ($group) {
									$group_id = pdo_insertid();
									pdo_update("xc_train_order", array("group_id" => $group_id), array("uniacid" => $uniacid, "openid" => $order["openid"], "out_trade_no" => $order["out_trade_no"]));
								}
							}
							pdo_update("xc_train_service_group", array("member_on +=" => 1), array("uniacid" => $_W["uniacid"], "id" => $order["pid"]));
						} else {
							if ($order["cut_status"] == -1) {
								$sql = "UPDATE " . tablename("xc_train_service_team") . " SET member=member+:member WHERE status=1 AND id=:id AND uniacid=:uniacid";
								pdo_query($sql, array(":member" => $order["total"], ":id" => $order["pid"], ":uniacid" => $uniacid));
							} elseif ($order["cut_status"] == 1) {
								$sql = "UPDATE " . tablename("xc_train_cut") . " SET is_member=is_member+:member WHERE status=1 AND id=:id AND uniacid=:uniacid";
								pdo_query($sql, array(":member" => $order["total"], ":id" => $order["pid"], ":uniacid" => $uniacid));
								pdo_update("xc_train_cut_order", array("status" => 1), array("openid" => $_W["openid"], "uniacid" => $uniacid, "cid" => $order["pid"]));
							}
						}
						if (!empty($order["share_one_openid"]) && !empty($order["share_one_fee"])) {
							pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_one_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 1, "amount" => $order["share_one_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
						}
						if (!empty($order["share_two_openid"]) && !empty($order["share_two_fee"])) {
							pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_two_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 2, "amount" => $order["share_two_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
						}
						if (!empty($order["share_three_openid"]) && !empty($order["share_three_fee"])) {
							pdo_insert("xc_train_share_order", array("uniacid" => $_W["uniacid"], "openid" => $order["share_three_openid"], "out_trade_no" => $order["out_trade_no"], "type" => 3, "amount" => $order["share_three_fee"], "order_amount" => $order["amount"], "status" => -1, "share" => $order["openid"], "createtime" => date("Y-m-d H:i:s")));
						}
						if ($order["can_use"] <= 0 && ($order["order_type"] == 1 || $order["order_type"] == 2)) {
							$share_order = pdo_getall("xc_train_share_order", array("uniacid" => $_W["uniacid"], "out_trade_no" => $order["out_trade_no"], "status" => -1));
							if ($share_order) {
								foreach ($share_order as $so) {
									pdo_update("xc_train_userinfo", array("share_fee +=" => floatval($so["amount"])), array("uniacid" => $_W["uniacid"], "openid" => $so["openid"]));
									pdo_update("xc_train_share_order", array("status" => 1), array("uniacid" => $_W["uniacid"], "id" => $so["id"]));
								}
							}
						}
						$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
						if ($config) {
							$config["content"] = json_decode($config["content"], true);
						}
						$sms = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "sms"));
						if ($sms) {
							$sms["content"] = json_decode($sms["content"], true);
							if ($sms["content"]["status"] == 1) {
								$send_mobile = $sms["content"]["mobile"];
								if (!empty($order["store"])) {
									$store = pdo_get("xc_train_school", array("id" => $order["store"], "uniacid" => $uniacid));
									if ($store && !empty($store["sms"])) {
										$send_mobile = $store["sms"];
									}
								}
								require_once IA_ROOT . "/addons/xc_train/resource/sms/sendSms.php";
								if ($sms["content"]["type"] == 1) {
									set_time_limit(0);
									header("Content-Type: text/plain; charset=utf-8");
									$templateParam = array("webnamex" => $config["content"]["title"], "trade" => $order["out_trade_no"], "amount" => $order["o_amount"], "namex" => $order["name"], "phonex" => $order["mobile"], "service" => $order["title"]);
									$send = new sms();
									$mobile = explode("/", $send_mobile);
									foreach ($mobile as $m) {
										$result = $send->sendSms($sms["content"]["AccessKeyId"], $sms["content"]["AccessKeySecret"], $sms["content"]["sign"], $sms["content"]["code"], $m, $templateParam);
									}
								} elseif ($sms["content"]["type"] == 2) {
									header("content-type:text/html;charset=utf-8");
									$sendUrl = "http://v.juhe.cn/sms/send";
									$tpl_value = "#webnamex#=" . $config["content"]["title"] . "&#trade#=" . $order["out_trade_no"] . "&#amount#=" . $order["o_amount"] . "&#namex#=" . $order["name"] . "&#phonex#=" . $order["mobile"] . "&#service#=" . $order["title"];
									$mobile = explode("/", $send_mobile);
									foreach ($mobile as $m) {
										$smsConf = array("key" => $sms["content"]["key"], "mobile" => $m, "tpl_id" => $sms["content"]["tpl_id"], "tpl_value" => $tpl_value);
										$content = juhecurl($sendUrl, $smsConf, 1);
									}
									if ($content) {
										$result = json_decode($content, true);
										$error_code = $result["error_code"];
									}
								} elseif ($sms["content"]["type"] == 3) {
									if (!empty($sms["content"]["url"])) {
										$customize = $sms["content"]["customize"];
										$post = $sms["content"]["post"];
										if (is_array($post) && !empty($post)) {
											$post = json_encode($post);
											if (is_array($customize)) {
												foreach ($customize as $x) {
													$post = str_replace("{{" . $x["attr"] . "}}", $x["value"], $post);
												}
											}
											$post = str_replace("{{webnamex}}", $config["content"]["title"], $post);
											$post = str_replace("{{trade}}", $order["out_trade_no"], $post);
											$post = str_replace("{{amount}}", $order["o_amount"], $post);
											$post = str_replace("{{namex}}", $order["name"], $post);
											$post = str_replace("{{phonex}}", $order["mobile"], $post);
											$post = str_replace("{{service}}", $order["title"], $post);
											$post = json_decode($post, true);
											$data = array();
											foreach ($post as $x2) {
												$data[$x2["attr"]] = $x2["value"];
											}
											$ch = curl_init();
											curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
											curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
											curl_setopt($ch, CURLOPT_POST, 1);
											curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
											$output = curl_exec($ch);
											curl_close($ch);
										}
										$get = $sms["content"]["get"];
										if (is_array($get) && !empty($get)) {
											$get = json_encode($get);
											if (is_array($customize)) {
												foreach ($customize as $x) {
													$get = str_replace("{{" . $x["attr"] . "}}", $x["value"], $get);
												}
											}
											$get = str_replace("{{webnamex}}", $config["content"]["title"], $get);
											$get = str_replace("{{trade}}", $order["out_trade_no"], $get);
											$get = str_replace("{{amount}}", $order["o_amount"], $get);
											$get = str_replace("{{namex}}", $order["name"], $get);
											$get = str_replace("{{phonex}}", $order["mobile"], $get);
											$get = str_replace("{{service}}", $order["title"], $get);
											$get = json_decode($get, true);
											$url_data = '';
											foreach ($get as $x3) {
												if (empty($url_data)) {
													$url_data = urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
												} else {
													$url_data = $url_data . "&" . urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
												}
											}
											if (strpos($sms["content"]["url"], "?") !== false) {
												$sms["content"]["url"] = $sms["content"]["url"] . $url_data;
											} else {
												$sms["content"]["url"] = $sms["content"]["url"] . "?" . $url_data;
											}
											$ch = curl_init();
											curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
											curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
											curl_setopt($ch, CURLOPT_HEADER, 0);
											$output = curl_exec($ch);
											curl_close($ch);
										}
									}
								}
							}
						}
						$print = pdo_get("xc_train_config", array("xkey" => "print", "uniacid" => $uniacid));
						if ($print) {
							$print["content"] = json_decode($print["content"], true);
							if ($print["content"]["status"] == 1) {
								if ($print["content"]["type"] == 1) {
									$time = time();
									$content = '';
									$content .= "订单号：" . $order["out_trade_no"] . "\\r\\n";
									$content .= "小程序名：" . $config["content"]["title"] . "\\r\\n";
									$content .= "人数：" . $order["total"] . "\\r\\n";
									$content .= "课程：" . $order["title"] . "\\r\\n";
									$content .= "价格：" . $order["o_amount"] . "\\r\\n";
									$content .= "姓名：" . $order["name"] . "\\r\\n";
									$content .= "手机：" . $order["mobile"] . "\\r\\n";
									$sign = strtoupper(md5($print["content"]["apikey"] . "machine_code" . $print["content"]["machine_code"] . "partner" . $print["content"]["partner"] . "time" . $time . $print["content"]["msign"]));
									$requestUrl = "http://open.10ss.net:8888";
									$requestAll = ["partner" => $print["content"]["partner"], "machine_code" => $print["content"]["machine_code"], "time" => $time, "content" => $content, "sign" => $sign];
									$requestInfo = http_build_query($requestAll);
									$request = push($requestInfo, $requestUrl);
								} elseif ($print["content"]["type"] == 2) {
									include dirname(__FILE__) . "/resource/HttpClient.class.php";
									define("USER", $print["content"]["user"]);
									define("UKEY", $print["content"]["ukey"]);
									define("SN", $print["content"]["sn"]);
									define("IP", "api.feieyun.cn");
									define("PORT", 80);
									define("PATH", "/Api/Open/");
									define("STIME", time());
									define("SIG", sha1(USER . UKEY . STIME));
									$orderInfo = "<CB>订单</CB><BR>";
									$orderInfo .= "订单号：" . $order["out_trade_no"] . "<BR>";
									$orderInfo .= "小程序名：" . $config["content"]["title"] . "<BR>";
									$orderInfo .= "--------------------------------<BR>";
									$orderInfo .= "课程：" . $order["title"] . "<BR>";
									$orderInfo .= "--------------------------------<BR>";
									$orderInfo .= "人数：" . $order["total"] . "<BR>";
									$orderInfo .= "价格：" . $order["o_amount"] . "<BR>";
									$orderInfo .= "姓名：" . $order["name"] . "<BR>";
									$orderInfo .= "手机：" . $order["mobile"] . "<BR>";
									$request = wp_print(SN, $orderInfo, 1);
								}
							}
						}
					}
				}
			}
		}
	}
	public function doPageMallOrder()
	{
		global $_GPC, $_W, $xcmodule;
		$uniacid = $_W["uniacid"];
		$share_status = -1;
		$share_one = '';
		$share_two = '';
		$share_three = '';
		$share_config = pdo_get("xc_train_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		$share_config = json_decode($share_config["content"], true);
		if (!empty($share_config)) {
			$share_status = $share_config["status"];
			if ($share_config["type"] >= 1 && !empty($share_config["referrer_one"])) {
				$share_one = $share_config["referrer_one"];
			}
			if ($share_config["type"] >= 2 && !empty($share_config["referrer_two"])) {
				$share_two = $share_config["referrer_two"];
			}
			if ($share_config["type"] >= 3 && !empty($share_config["referrer_three"])) {
				$share_three = $share_config["referrer_three"];
			}
		}
		$total = 0;
		$price = '';
		$amount = 0;
		$title = '';
		$shop_data = array();
		if (!empty($_GPC["shop_data"])) {
			$title = array();
			$shop_data = json_decode(htmlspecialchars_decode($_GPC["shop_data"]), true);
			foreach ($shop_data as $sd) {
				$title[] = $sd["name"] . $sd["format_name"];
				$total = intval($total) + intval($sd["member"]);
				$amount = round(floatval($amount) + floatval($sd["price"]) * intval($sd["member"]), 2);
			}
			$title = implode(",", $title);
		} else {
			$total = $_GPC["member"];
			$service = pdo_get("xc_train_mall", array("id" => $_GPC["id"], "uniacid" => $uniacid));
			if ($service) {
				$service["format"] = json_decode($service["format"], true);
				if ($service["type"] == 3) {
					if (strtotime($service["start_time"]) < time() && time() < strtotime($service["end_time"])) {
						$service["failtime"] = strtotime($service["end_time"]) - time();
					} else {
						$service["type"] = 1;
					}
				}
				$title = $service["name"];
				if ($_GPC["format"] == -1) {
					$price = $service["price"];
				} else {
					if ($service["type"] == 1 || $service["type"] == 2) {
						if ($service["type"] == 2 && !empty($_GPC["group"])) {
							$price = $service["format"][$_GPC["format"]]["group_price"];
							$condition["mall_type"] = 2;
						} else {
							$price = $service["format"][$_GPC["format"]]["price"];
							$condition["mall_type"] = 1;
						}
					} else {
						if ($service["type"] == 3) {
							$price = $service["format"][$_GPC["format"]]["limit_price"];
							$condition["mall_type"] = 3;
						}
					}
					$title = $title . " " . $service["format"][$_GPC["format"]]["name"];
					$condition["format"] = $service["format"][$_GPC["format"]]["name"];
				}
				if (!empty($service["share_one"])) {
					$share_one = $service["share_one"];
				}
				if (!empty($service["share_two"])) {
					$share_two = $service["share_two"];
				}
				if (!empty($service["share_three"])) {
					$share_three = $service["share_three"];
				}
			}
			$amount = round(floatval($price) * $total, 2);
		}
		$condition["uniacid"] = $uniacid;
		$condition["openid"] = $_W["openid"];
		$condition["pid"] = $_GPC["id"];
		$condition["order_type"] = 4;
		$condition["total"] = $total;
		$condition["amount"] = $amount;
		$condition["o_amount"] = $condition["amount"];
		$condition["title"] = $title;
		if (!empty($_GPC["coupon"])) {
			$coupon = pdo_get("xc_train_coupon", array("uniacid" => $uniacid, "id" => $_GPC["coupon"]));
			if ($coupon) {
				$condition["coupon_id"] = $_GPC["coupon"];
				$condition["coupon_price"] = $coupon["name"];
				$condition["o_amount"] = round(floatval($condition["amount"]) - floatval($condition["coupon_price"]), 2);
			}
		}
		if (floatval($condition["o_amount"]) < 0) {
			$condition["o_amount"] = 0;
		}
		if (!empty($_GPC["content"])) {
			$condition["content"];
		}
		$condition["can_use"] = $condition["total"];
		$condition["userinfo"] = htmlspecialchars_decode($_GPC["address"]);
		if (!empty($_GPC["store"])) {
			$condition["store"] = $_GPC["store"];
		}
		if (!empty($_GPC["group_id"])) {
			$group = pdo_get("xc_train_group", array("id" => $_GPC["group_id"], "uniacid" => $uniacid));
			if ($group) {
				if ($group["status"] == -1 && strtotime($group["failtime"]) > time()) {
					$condition["group_id"] = $_GPC["group_id"];
				} else {
					return $this->result(1, "拼团已结束");
				}
			} else {
				return $this->result(1, "拼团已结束");
			}
		}
		$condition["pei_type"] = $_GPC["pei_type"];
		if ($condition["pei_type"] == 1) {
			$fee = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
			if ($fee) {
				$fee["content"] = json_decode($fee["content"], true);
				if (!empty($fee["content"]["fee"])) {
					$condition["amount"] = round(floatval($condition["amount"]) + floatval($fee["content"]["fee"]), 2);
					$condition["o_amount"] = round(floatval($condition["o_amount"]) + floatval($fee["content"]["fee"]), 2);
					$condition["fee"] = $fee["content"]["fee"];
				}
			}
		}
		if (!empty($_GPC["content"])) {
			$condition["content"] = $_GPC["content"];
		}
		if (!empty($_GPC["shop_data"])) {
			$condition["shop_data"] = htmlspecialchars_decode($_GPC["shop_data"]);
		}
		$condition["out_trade_no"] = setcode();
		$share_one_openid = '';
		$share_one_fee = '';
		$share_two_openid = '';
		$share_two_fee = '';
		$share_three_openid = '';
		$share_three_fee = '';
		$user = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($share_status == 1) {
			if (!empty($share_one) && !empty($user["share"])) {
				$share_one_openid = $user["share"];
				$share_one_fee = round(floatval($condition["o_amount"]) * floatval($share_one) / 100, 2);
				$user_one = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user["share"]));
				if ($user_one && !empty($user_one["share"]) && !empty($share_two)) {
					$share_two_openid = $user_one["share"];
					$share_two_fee = round(floatval($condition["o_amount"]) * floatval($share_two) / 100, 2);
					$user_two = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user_one["share"]));
					if ($user_two && !empty($user_two["share"]) && !empty($share_three)) {
						$share_three_openid = $user_two["share"];
						$share_three_fee = round(floatval($condition["o_amount"]) * floatval($share_three) / 100, 2);
					}
				}
			}
		}
		if (!empty($share_one_openid) && !empty($share_one_fee)) {
			$condition["share_one_openid"] = $share_one_openid;
			$condition["share_one_fee"] = $share_one_fee;
		}
		if (!empty($share_two_openid) && !empty($share_two_fee)) {
			$condition["share_two_openid"] = $share_two_openid;
			$condition["share_two_fee"] = $share_two_fee;
		}
		if (!empty($share_three_openid) && !empty($share_three_fee)) {
			$condition["share_three_openid"] = $share_three_openid;
			$condition["share_three_fee"] = $share_three_fee;
		}
		$request = pdo_insert("xc_train_order", $condition);
		if ($request) {
			$order_id = pdo_insertid();
			$moban_id = array();
			$moban = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "moban_pay"));
			if ($moban) {
				$moban = json_decode($moban["content"], true);
				if ($moban && $moban["status"] == 1 && !empty($moban["template_id"])) {
					$moban_id[] = $moban["template_id"];
				}
			}
			if (!empty($shop_data)) {
				foreach ($shop_data as $sd) {
					$sql = "UPDATE " . tablename("xc_train_mall") . " SET sold=sold+:sold WHERE id=:id AND uniacid=:uniacid";
					pdo_query($sql, array(":sold" => $sd["member"], ":id" => $sd["pid"], ":uniacid" => $uniacid));
				}
			} else {
				$sql = "UPDATE " . tablename("xc_train_mall") . " SET sold=sold+:sold WHERE id=:id AND uniacid=:uniacid";
				pdo_query($sql, array(":sold" => $condition["total"], ":id" => $condition["pid"], ":uniacid" => $uniacid));
			}
			if (floatval($condition["o_amount"]) == 0) {
				$request = pdo_update("xc_train_order", array("status" => 1), array("out_trade_no" => $condition["out_trade_no"], "uniacid" => $uniacid));
				if ($request) {
					if (!empty($condition["coupon_id"])) {
						pdo_update("xc_train_user_coupon", array("status" => 1), array("cid" => $condition["coupon_id"], "openid" => $_W["openid"], "uniacid" => $uniacid, "status" => -1));
					}
					if ($condition["mall_type"] == 2) {
						if (!empty($condition["group_id"])) {
							$group = pdo_get("xc_train_group", array("id" => $condition["group_id"], "uniacid" => $uniacid));
							if ($group) {
								$group["group"] = json_decode($group["group"], true);
								$group["group"][] = $_W["openid"];
								$group_data["is_member"] = intval($group["is_member"]) + 1;
								if ($group_data["is_member"] == $group["member"]) {
									$group_data["status"] = 1;
								}
								$group_data["group"] = json_encode($group["group"]);
								$group_request = pdo_update("xc_train_group", $group_data, array("id" => $condition["group_id"], "uniacid" => $uniacid));
								if ($group_request) {
									if (!empty($group_data["status"])) {
										pdo_update("xc_train_order", array("group_status" => 1), array("group_id" => $condition["group_id"], "status" => 1, "order_type" => 4, "mall_type" => 2));
									}
								}
							}
						} else {
							pdo_insert("xc_train_group", array("uniacid" => $uniacid, "openid" => $_W["openid"], "service" => $condition["pid"], "is_member" => 1, "member" => $service["group_member"], "failtime" => date("Y-m-d H:i:s", time() + intval($service["group_fail"]) * 60 * 60), "group" => json_encode(array($_W["openid"]))));
							$id = pdo_insertid();
							$condition["group_id"] = $id;
							pdo_update("xc_train_order", array("group_id" => $id), array("out_trade_no" => $condition["out_trade_no"], "uniacid" => $uniacid));
						}
					}
					$address = json_decode($condition["userinfo"], true);
					$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
					if ($config) {
						$config["content"] = json_decode($config["content"], true);
					}
					if ($condition["mall_type"] != 2) {
						$sms = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "sms"));
						if ($sms) {
							$sms["content"] = json_decode($sms["content"], true);
							$send_mobile = $sms["content"]["mobile"];
							if (!empty($condition["store"])) {
								$store = pdo_get("xc_train_school", array("id" => $condition["store"], "uniacid" => $uniacid));
								if ($store && !empty($store["sms"])) {
									$send_mobile = $store["sms"];
								}
							}
							if ($sms["content"]["status"] == 1) {
								require_once IA_ROOT . "/addons/xc_train/resource/sms/sendSms.php";
								if ($sms["content"]["type"] == 1) {
									set_time_limit(0);
									header("Content-Type: text/plain; charset=utf-8");
									$templateParam = array("webnamex" => $config["content"]["title"], "trade" => $condition["out_trade_no"], "amount" => $condition["o_amount"], "namex" => $address["name"], "phonex" => $address["mobile"], "service" => $condition["title"]);
									$send = new sms();
									$mobile = explode("/", $send_mobile);
									foreach ($mobile as $m) {
										$result = $send->sendSms($sms["content"]["AccessKeyId"], $sms["content"]["AccessKeySecret"], $sms["content"]["sign"], $sms["content"]["code"], $m, $templateParam);
									}
								} elseif ($sms["content"]["type"] == 2) {
									header("content-type:text/html;charset=utf-8");
									$sendUrl = "http://v.juhe.cn/sms/send";
									$tpl_value = "#webnamex#=" . $config["content"]["title"] . "&#trade#=" . $condition["out_trade_no"] . "&#amount#=免费&#namex#=" . $address["name"] . "&#phonex#=" . $address["mobile"] . "&#service#=" . $condition["title"];
									$mobile = explode("/", $send_mobile);
									foreach ($mobile as $m) {
										$smsConf = array("key" => $sms["content"]["key"], "mobile" => $m, "tpl_id" => $sms["content"]["tpl_id"], "tpl_value" => $tpl_value);
										$content = juhecurl($sendUrl, $smsConf, 1);
									}
									if ($content) {
										$result = json_decode($content, true);
										$error_code = $result["error_code"];
									}
								} elseif ($sms["content"]["type"] == 3) {
									if (!empty($sms["content"]["url"])) {
										$customize = $sms["content"]["customize"];
										$post = $sms["content"]["post"];
										if (is_array($post) && !empty($post)) {
											$post = json_encode($post);
											if (is_array($customize)) {
												foreach ($customize as $x) {
													$post = str_replace("{{" . $x["attr"] . "}}", $x["value"], $post);
												}
											}
											$post = str_replace("{{webnamex}}", $config["content"]["title"], $post);
											$post = str_replace("{{trade}}", $condition["out_trade_no"], $post);
											$post = str_replace("{{amount}}", "免费", $post);
											$post = str_replace("{{namex}}", $address["name"], $post);
											$post = str_replace("{{phonex}}", $address["mobile"], $post);
											$post = str_replace("{{service}}", $condition["title"], $post);
											$post = json_decode($post, true);
											$data = array();
											foreach ($post as $x2) {
												$data[$x2["attr"]] = $x2["value"];
											}
											$ch = curl_init();
											curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
											curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
											curl_setopt($ch, CURLOPT_POST, 1);
											curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
											$output = curl_exec($ch);
											curl_close($ch);
										}
										$get = $sms["content"]["get"];
										if (is_array($get) && !empty($get)) {
											$get = json_encode($get);
											if (is_array($customize)) {
												foreach ($customize as $x) {
													$get = str_replace("{{" . $x["attr"] . "}}", $x["value"], $get);
												}
											}
											$get = str_replace("{{webnamex}}", $config["content"]["title"], $get);
											$get = str_replace("{{trade}}", $condition["out_trade_no"], $get);
											$get = str_replace("{{amount}}", "免费", $get);
											$get = str_replace("{{namex}}", $address["name"], $get);
											$get = str_replace("{{phonex}}", $address["mobile"], $get);
											$get = str_replace("{{service}}", $condition["title"], $get);
											$get = json_decode($get, true);
											$url_data = '';
											foreach ($get as $x3) {
												if (empty($url_data)) {
													$url_data = urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
												} else {
													$url_data = $url_data . "&" . urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
												}
											}
											if (strpos($sms["content"]["url"], "?") !== false) {
												$sms["content"]["url"] = $sms["content"]["url"] . $url_data;
											} else {
												$sms["content"]["url"] = $sms["content"]["url"] . "?" . $url_data;
											}
											$ch = curl_init();
											curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
											curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
											curl_setopt($ch, CURLOPT_HEADER, 0);
											$output = curl_exec($ch);
											curl_close($ch);
										}
									}
								}
							}
						}
						$print = pdo_get("xc_train_config", array("xkey" => "print", "uniacid" => $uniacid));
						if ($print) {
							$print["content"] = json_decode($print["content"], true);
							if ($print["content"]["status"] == 1) {
								if ($print["content"]["type"] == 1) {
									$service_name = $service["name"];
									$time = time();
									$content = '';
									$content .= "订单号：" . $condition["out_trade_no"] . "\\r\\n";
									$content .= "小程序名：" . $config["content"]["title"] . "\\r\\n";
									$content .= "人数：" . $condition["total"] . "\\r\\n";
									$content .= "商品：" . $condition["title"] . "\\r\\n";
									$content .= "价格：免费\\r\\n";
									$content .= "姓名：" . $address["name"] . "\\r\\n";
									$content .= "手机：" . $address["mobile"] . "\\r\\n";
									$sign = strtoupper(md5($print["content"]["apikey"] . "machine_code" . $print["content"]["machine_code"] . "partner" . $print["content"]["partner"] . "time" . $time . $print["content"]["msign"]));
									$requestUrl = "http://open.10ss.net:8888";
									$requestAll = ["partner" => $print["content"]["partner"], "machine_code" => $print["content"]["machine_code"], "time" => $time, "content" => $content, "sign" => $sign];
									$requestInfo = http_build_query($requestAll);
									$request = push($requestInfo, $requestUrl);
								} elseif ($print["content"]["type"] == 2) {
									include IA_ROOT . "/addons/xc_train/resource/HttpClient.class.php";
									define("USER", $print["content"]["user"]);
									define("UKEY", $print["content"]["ukey"]);
									define("SN", $print["content"]["sn"]);
									define("IP", "api.feieyun.cn");
									define("PORT", 80);
									define("PATH", "/Api/Open/");
									define("STIME", time());
									define("SIG", sha1(USER . UKEY . STIME));
									$orderInfo = "<CB>订单</CB><BR>";
									$orderInfo .= "订单号：" . $condition["out_trade_no"] . "<BR>";
									$orderInfo .= "小程序名：" . $config["content"]["title"] . "<BR>";
									$orderInfo .= "--------------------------------<BR>";
									$orderInfo .= "商品：" . $condition["title"] . "<BR>";
									$orderInfo .= "--------------------------------<BR>";
									$orderInfo .= "人数：" . $condition["total"] . "<BR>";
									$orderInfo .= "价格：免费<BR>";
									$orderInfo .= "姓名：" . $address["name"] . "<BR>";
									$orderInfo .= "手机：" . $address["mobile"] . "<BR>";
									$request = wp_print(SN, $orderInfo, 1);
								}
							}
						}
					}
				}
				$postdata["status"] = 2;
			} else {
				$sub_status = -1;
				$sub_appid = '';
				$sub_mch_id = '';
				$sub_key = '';
				$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $_W["uniacid"]));
				if ($config) {
					$config["content"] = json_decode($config["content"], true);
					if (!empty($config["content"]) && $config["content"]["sub_status"] && !empty($config["content"]["sub_appid"]) && !empty($config["content"]["sub_mch_id"]) && !empty($config["content"]["sub_key"])) {
						$sub_status = $config["content"]["sub_status"];
						$sub_appid = $config["content"]["sub_appid"];
						$sub_mch_id = $config["content"]["sub_mch_id"];
						$sub_key = $config["content"]["sub_key"];
					}
				}
				if ($sub_status == 1 && !empty($sub_appid) && !empty($sub_mch_id) && !empty($sub_key)) {
					$data["title"] = $condition["title"];
					$data["tid"] = $condition["out_trade_no"];
					$data["fee"] = $condition["o_amount"];
					$data["sub_appid"] = $sub_appid;
					$data["sub_mch_id"] = $sub_mch_id;
					$data["sub_key"] = $sub_key;
					$postdata = sub_pay($data, $_GPC, $_W);
				} else {
					$data["title"] = $condition["title"];
					$data["tid"] = $condition["out_trade_no"];
					$data["fee"] = $condition["o_amount"];
					$postdata = $this->pay($data);
				}
				$postdata["status"] = 1;
			}
			if (!empty($condition["group_id"])) {
				$postdata["group_id"] = $condition["group_id"];
			}
			$postdata["id"] = $order_id;
			if (!empty($moban_id)) {
				$postdata["template_id"] = $moban_id;
			}
			return $this->result(0, "操作成功", $postdata);
		} else {
			return $this->result(1, "生成订单失败");
		}
	}
	public function doPageGroupRefund()
	{
		global $_GPC, $_W;
		$xcmodule = "xc_train";
		$uniacid = $_W["uniacid"];
		set_time_limit(0);
		ignore_user_abort(true);
		$config = pdo_get($xcmodule . "_config", array("xkey" => "group", "uniacid" => $uniacid));
		if ($config) {
			if ($config["content"] != date("Y-m-d")) {
				pdo_update($xcmodule . "_config", array("content" => date("Y-m-d")), array("xkey" => "group", "uniacid" => $uniacid));
			}
		} else {
			pdo_insert($xcmodule . "_config", array("xkey" => "group", "uniacid" => $uniacid, "content" => date("Y-m-d")));
		}
		$group = pdo_getall($xcmodule . "_group", array("status" => -1, "uniacid" => $uniacid, "failtime <" => date("Y-m-d H:i:s")));
		if ($group) {
			$group_id = array();
			foreach ($group as &$g) {
				if (strtotime($g["failtime"]) < time()) {
					$group_id[] = $g["id"];
				}
			}
			if (!empty($group_id)) {
				pdo_update($xcmodule . "_group", array("status" => 2), array("uniacid" => $uniacid, "id IN" => $group_id));
				pdo_update($xcmodule . "_order", array("group_status" => 2, "status" => 2), array("uniacid" => $uniacid, "group_id IN" => $group_id, "status" => 1, "group_status" => -1));
			}
		}
		$service_group = pdo_getall($xcmodule . "_group_service", array("status" => -1, "uniacid" => $uniacid, "failtime <" => date("Y-m-d H:i:s")));
		if ($service_group) {
			$ids = array();
			foreach ($service_group as $sg) {
				$ids[] = $sg["id"];
			}
			pdo_update($xcmodule . "_group_service", array("status" => 2), array("uniacid" => $_W["uniacid"], "id IN" => $ids));
			$order = pdo_getall($xcmodule . "_order", array("uniacid" => $uniacid, "group_id IN" => $ids, "status" => 1, "group_status" => -1));
			if ($order) {
				foreach ($order as $o) {
					pdo_update($xcmodule . "_service_group", array("member_on -=" => 1), array("uniacid" => $_W["uniacid"], "id" => $o["pid"]));
					pdo_update($xcmodule . "_share_order", array("status" => 2), array("uniacid" => $_W["uniacid"], "out_trade_no" => $o["out_trade_no"]));
				}
				pdo_update($xcmodule . "_order", array("group_status" => 2, "status" => 2), array("uniacid" => $uniacid, "group_id IN" => $ids, "status" => 1, "group_status" => -1));
			}
		}
	}
	public function doPageShareUpload()
	{
		ini_set("display_errors", 0);
		error_reporting(E_ALL ^ E_NOTICE);
		error_reporting(E_ALL ^ E_WARNING);
		$http_type = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" || isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https" ? "https://" : "http://";
		global $_GPC, $_W;
		$uniacid = $_W["uniacid"];
		$imgname = $_FILES["file"]["name"];
		$tmp = $_FILES["file"]["tmp_name"];
		$filepath = "../attachment/images/" . $uniacid . "/" . date("Y") . "/" . date("m") . "/";
		if (!file_exists("../attachment/images/" . $uniacid . "/" . date("Y") . "/")) {
			mkdir("../attachment/images/" . $uniacid . "/" . date("Y") . "/");
		}
		if (!file_exists($filepath)) {
			mkdir($filepath);
		}
		if (move_uploaded_file($tmp, $filepath . $imgname)) {
			$url = "images/" . $uniacid . "/" . date("Y") . "/" . date("m") . "/" . $imgname;
			if (!empty($_W["setting"]["remote"]["type"]) && $_W["setting"]["remote"]["type"] == 3) {
				load()->func("file");
				file_remote_upload("images/" . $uniacid . "/" . date("Y") . "/" . date("m") . "/" . $imgname);
			}
			return $this->result(1, "操作成功", array("code" => tomedia($url), "share_code" => $url));
		} else {
			return $this->result(1, "上传失败");
		}
	}
	public function doPageLineOrder()
	{
		global $_GPC, $_W;
		$uniacid = $_W["uniacid"];
		$share_status = -1;
		$share_one = '';
		$share_two = '';
		$share_three = '';
		$share_config = pdo_get("xc_train_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		$share_config = json_decode($share_config["content"], true);
		if (!empty($share_config)) {
			$share_status = $share_config["status"];
			if ($share_config["type"] >= 1 && !empty($share_config["referrer_one"])) {
				$share_one = $share_config["referrer_one"];
			}
			if ($share_config["type"] >= 2 && !empty($share_config["referrer_two"])) {
				$share_two = $share_config["referrer_two"];
			}
			if ($share_config["type"] >= 3 && !empty($share_config["referrer_three"])) {
				$share_three = $share_config["referrer_three"];
			}
		}
		$where = " uniacid=:uniacid AND id=:id AND status=1 AND unix_timestamp(start_time)<:times AND unix_timestamp(end_time)>:times ";
		$condition[":uniacid"] = $_W["uniacid"];
		$condition[":id"] = $_GPC["id"];
		$condition[":times"] = time();
		$sql = "SELECT * FROM " . tablename("xc_train_line") . " WHERE " . $where;
		$line = pdo_fetch($sql, $condition);
		if ($line) {
			if (!empty($line["share_one"])) {
				$share_one = $line["share_one"];
			}
			if (!empty($line["share_two"])) {
				$share_two = $line["share_two"];
			}
			if (!empty($line["share_three"])) {
				$share_three = $line["share_three"];
			}
			$order_data["uniacid"] = $_W["uniacid"];
			$order_data["openid"] = $_W["openid"];
			$order_data["out_trade_no"] = setcode();
			$order_data["pid"] = $line["id"];
			$order_data["name"] = $_GPC["name"];
			$order_data["mobile"] = $_GPC["mobile"];
			$order_data["order_type"] = 6;
			$order_data["total"] = 1;
			$order_data["amount"] = $line["price"];
			$order_data["status"] = -1;
			$order_data["createtime"] = date("Y-m-d H:i:s");
			$order_data["line_name"] = $line["name"];
			$order_data["line_img"] = $line["simg"];
			$order_data["line_data"] = json_encode($line);
			$share_one_openid = '';
			$share_one_fee = '';
			$share_two_openid = '';
			$share_two_fee = '';
			$share_three_openid = '';
			$share_three_fee = '';
			$user = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
			if ($share_status == 1) {
				if (!empty($share_one) && !empty($user["share"])) {
					$share_one_openid = $user["share"];
					$share_one_fee = round(floatval($order_data["amount"]) * floatval($share_one) / 100, 2);
					$user_one = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user["share"]));
					if ($user_one && !empty($user_one["share"]) && !empty($share_two)) {
						$share_two_openid = $user_one["share"];
						$share_two_fee = round(floatval($order_data["amount"]) * floatval($share_two) / 100, 2);
						$user_two = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user_one["share"]));
						if ($user_two && !empty($user_two["share"]) && !empty($share_three)) {
							$share_three_openid = $user_two["share"];
							$share_three_fee = round(floatval($order_data["amount"]) * floatval($share_three) / 100, 2);
						}
					}
				}
			}
			if (!empty($share_one_openid) && !empty($share_one_fee)) {
				$order_data["share_one_openid"] = $share_one_openid;
				$order_data["share_one_fee"] = $share_one_fee;
			}
			if (!empty($share_two_openid) && !empty($share_two_fee)) {
				$order_data["share_two_openid"] = $share_two_openid;
				$order_data["share_two_fee"] = $share_two_fee;
			}
			if (!empty($share_three_openid) && !empty($share_three_fee)) {
				$order_data["share_three_openid"] = $share_three_openid;
				$order_data["share_three_fee"] = $share_three_fee;
			}
			$request = pdo_insert("xc_train_order", $order_data);
			if ($request) {
				if (floatval($order_data["amount"]) > 0) {
					$sub_status = -1;
					$sub_appid = '';
					$sub_mch_id = '';
					$sub_key = '';
					$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $_W["uniacid"]));
					if ($config) {
						$config["content"] = json_decode($config["content"], true);
						if (!empty($config["content"]) && $config["content"]["sub_status"] && !empty($config["content"]["sub_appid"]) && !empty($config["content"]["sub_mch_id"]) && !empty($config["content"]["sub_key"])) {
							$sub_status = $config["content"]["sub_status"];
							$sub_appid = $config["content"]["sub_appid"];
							$sub_mch_id = $config["content"]["sub_mch_id"];
							$sub_key = $config["content"]["sub_key"];
						}
					}
					if ($sub_status == 1 && !empty($sub_appid) && !empty($sub_mch_id) && !empty($sub_key)) {
						$data["title"] = $order_data["line_name"];
						$data["tid"] = $order_data["out_trade_no"];
						$data["fee"] = $order_data["amount"];
						$data["sub_appid"] = $sub_appid;
						$data["sub_mch_id"] = $sub_mch_id;
						$data["sub_key"] = $sub_key;
						$postdata = sub_pay($data, $_GPC, $_W);
					} else {
						$data["title"] = $order_data["line_name"];
						$data["tid"] = $order_data["out_trade_no"];
						$data["fee"] = $order_data["amount"];
						$postdata = $this->pay($data);
					}
					$postdata["status"] = 1;
				} else {
					$request = pdo_update("xc_train_order", array("status" => 1), array("uniacid" => $_W["uniacid"], "out_trade_no" => $order_data["out_trade_no"]));
					if ($request) {
						if (!empty($line["video"])) {
							$line["video"] = json_decode($line["video"], true);
							foreach ($line["video"] as $lv) {
								pdo_insert("xc_train_line_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "out_trade_no" => $order_data["out_trade_no"], "line" => $order_data["pid"], "type" => 1, "pid" => $lv["id"], "createtime" => date("Y-m-d H:i:s")));
							}
						}
						if (!empty($line["audio"])) {
							$line["audio"] = json_decode($line["audio"], true);
							foreach ($line["audio"] as $la) {
								pdo_insert("xc_train_line_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "out_trade_no" => $order_data["out_trade_no"], "line" => $order_data["pid"], "type" => 2, "pid" => $la["id"], "createtime" => date("Y-m-d H:i:s")));
							}
						}
					}
					$postdata["status"] = 2;
				}
				return $this->result(0, "操作成功", $postdata);
			} else {
				return $this->result(1, "订单生成失败");
			}
		} else {
			return $this->result(1, "礼包已下架");
		}
	}
	public function doPageScoreOrder()
	{
		global $_GPC, $_W, $xcmodule;
		$uniacid = $_W["uniacid"];
		$address = pdo_get("xc_train_address", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status" => 1));
		$service = pdo_get("xc_train_score_mall", array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"], "status" => 1));
		$score = 0;
		if ($service) {
			if (intval($_GPC["member"]) > intval($service["kucun"])) {
				return $this->result(1, "库存不足");
			}
			$score = intval($_GPC["member"]) * intval($service["score"]);
		} else {
			return $this->result(1, "商品已下架");
		}
		$user = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($score > $user["score"]) {
			return $this->result(1, "积分不足");
		}
		$store = pdo_get("xc_train_school", array("uniacid" => $_W["uniacid"], "id" => $_GPC["store"]));
		$data["uniacid"] = $_W["uniacid"];
		$data["openid"] = $_W["openid"];
		$data["out_trade_no"] = setcode();
		$data["order_type"] = 8;
		$data["total"] = $_GPC["member"];
		$data["pid"] = $service["id"];
		$data["service_name"] = $service["name"];
		$data["title"] = $service["name"];
		$data["service_data"] = json_encode($service);
		$data["score"] = $score;
		$data["name"] = $address["name"];
		$data["mobile"] = $address["mobile"];
		if (!empty($address["address"])) {
			$data["address"] = $address["address"];
			if (!empty($address["content"])) {
				$data["address"] .= " " . $address["content"];
			}
		}
		if (!empty($_GPC["content"])) {
			$data["content"] = $_GPC["content"];
		}
		$data["pei_type"] = $_GPC["pei_type"];
		$data["store"] = $_GPC["store"];
		$data["store_name"] = $store["name"];
		$data["store_data"] = json_encode($store);
		$data["status"] = 1;
		$data["createtime"] = date("Y-m-d H:i:s");
		$request = pdo_insert("xc_train_order", $data);
		if ($request) {
			$id = pdo_insertid();
			$moban_id = array();
			$moban = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "moban_pay"));
			if ($moban) {
				$moban = json_decode($moban["content"], true);
				if ($moban && $moban["status"] == 1 && !empty($moban["template_id"])) {
					$moban_id[] = $moban["template_id"];
				}
			}
			pdo_update("xc_train_score_mall", array("kucun -=" => $data["total"], "sold +=" => $data["total"]), array("uniacid" => $_W["uniacid"], "id" => $service["id"]));
			pdo_update("xc_train_userinfo", array("score -=" => $score), array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
			pdo_insert("xc_train_score_record", array("uniacid" => $_W["uniacid"], "name" => $service["name"], "openid" => $_W["openid"], "pid" => $data["out_trade_no"], "type" => 2, "score" => $score, "createtime" => date("Y-m-d H:i:s")));
			$postdata = array();
			$postdata["status"] = 1;
			$postdata["id"] = $id;
			if (!empty($moban_id)) {
				$postdata["template_id"] = $moban_id;
			}
			return $this->result(0, "操作成功", $postdata);
		} else {
			return $this->result(1, "生成订单失败");
		}
	}
	public function doPageMoveOrder()
	{
		global $_GPC, $_W, $xcmodule;
		$uniacid = $_W["uniacid"];
		$share_status = -1;
		$share_one = '';
		$share_two = '';
		$share_three = '';
		$share_config = pdo_get("xc_train_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		$share_config = json_decode($share_config["content"], true);
		if (!empty($share_config)) {
			$share_status = $share_config["status"];
			if ($share_config["type"] >= 1 && !empty($share_config["referrer_one"])) {
				$share_one = $share_config["referrer_one"];
			}
			if ($share_config["type"] >= 2 && !empty($share_config["referrer_two"])) {
				$share_two = $share_config["referrer_two"];
			}
			if ($share_config["type"] >= 3 && !empty($share_config["referrer_three"])) {
				$share_three = $share_config["referrer_three"];
			}
		}
		$service = pdo_get($xcmodule . "_move", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $_GPC["id"]));
		$amount = 0;
		$total = 0;
		$data = $_GPC["data"];
		$data = json_decode(htmlspecialchars_decode($data), true);
		if ($service && $service["member"] > $service["member_on"]) {
			if (!empty($service["share_one"])) {
				$share_one = $service["share_one"];
			}
			if (!empty($service["share_two"])) {
				$share_two = $service["share_two"];
			}
			if (!empty($service["share_three"])) {
				$share_three = $service["share_three"];
			}
			foreach ($data as $d) {
				$total = $total + $d["member"];
				$amount = round($amount + $d["member"] * $d["price"], 2);
			}
		} else {
			return $this->result(1, "活动已结束");
		}
		if ($service["member_on"] + $total > $service["member"]) {
			return $this->result(1, "人数不足");
		}
		$store = array();
		if (!empty($service["store"])) {
			$store = pdo_get($xcmodule . "_school", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $service["store"]));
		}
		$condition["uniacid"] = $_W["uniacid"];
		$condition["openid"] = $_W["openid"];
		$condition["out_trade_no"] = setcode();
		$condition["order_type"] = 9;
		$condition["total"] = $total;
		$condition["amount"] = $amount;
		$condition["name"] = $_GPC["name"];
		$condition["mobile"] = $_GPC["mobile"];
		$condition["status"] = -1;
		$condition["createtime"] = date("Y-m-d H:i:s");
		$condition["o_amount"] = $amount;
		$condition["pid"] = $service["id"];
		$condition["service_name"] = $service["name"];
		$condition["title"] = $service["name"];
		$condition["service_data"] = json_encode($service);
		$condition["line_data"] = json_encode($data);
		if ($store) {
			$condition["store_name"] = $store["name"];
			$condition["store_data"] = json_encode($store);
		}
		$share_one_openid = '';
		$share_one_fee = '';
		$share_two_openid = '';
		$share_two_fee = '';
		$share_three_openid = '';
		$share_three_fee = '';
		$user = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($share_status == 1) {
			if (!empty($share_one) && !empty($user["share"])) {
				$share_one_openid = $user["share"];
				$share_one_fee = round(floatval($condition["amount"]) * floatval($share_one) / 100, 2);
				$user_one = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user["share"]));
				if ($user_one && !empty($user_one["share"]) && !empty($share_two)) {
					$share_two_openid = $user_one["share"];
					$share_two_fee = round(floatval($condition["amount"]) * floatval($share_two) / 100, 2);
					$user_two = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user_one["share"]));
					if ($user_two && !empty($user_two["share"]) && !empty($share_three)) {
						$share_three_openid = $user_two["share"];
						$share_three_fee = round(floatval($condition["amount"]) * floatval($share_three) / 100, 2);
					}
				}
			}
		}
		if (!empty($share_one_openid) && !empty($share_one_fee)) {
			$condition["share_one_openid"] = $share_one_openid;
			$condition["share_one_fee"] = $share_one_fee;
		}
		if (!empty($share_two_openid) && !empty($share_two_fee)) {
			$condition["share_two_openid"] = $share_two_openid;
			$condition["share_two_fee"] = $share_two_fee;
		}
		if (!empty($share_three_openid) && !empty($share_three_fee)) {
			$condition["share_three_openid"] = $share_three_openid;
			$condition["share_three_fee"] = $share_three_fee;
		}
		$request = pdo_insert($xcmodule . "_order", $condition);
		if ($request) {
			$id = pdo_insertid();
			$moban_id = array();
			$moban = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "moban_pay"));
			if ($moban) {
				$moban = json_decode($moban["content"], true);
				if ($moban && $moban["status"] == 1 && !empty($moban["template_id"])) {
					$moban_id[] = $moban["template_id"];
				}
			}
			$postdata = array();
			if (floatval($amount) > 0) {
				$sub_status = -1;
				$sub_appid = '';
				$sub_mch_id = '';
				$sub_key = '';
				$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $_W["uniacid"]));
				if ($config) {
					$config["content"] = json_decode($config["content"], true);
					if (!empty($config["content"]) && $config["content"]["sub_status"] && !empty($config["content"]["sub_appid"]) && !empty($config["content"]["sub_mch_id"]) && !empty($config["content"]["sub_key"])) {
						$sub_status = $config["content"]["sub_status"];
						$sub_appid = $config["content"]["sub_appid"];
						$sub_mch_id = $config["content"]["sub_mch_id"];
						$sub_key = $config["content"]["sub_key"];
					}
				}
				if ($sub_status == 1 && !empty($sub_appid) && !empty($sub_mch_id) && !empty($sub_key)) {
					$data["title"] = $condition["service_name"];
					$data["tid"] = $condition["out_trade_no"];
					$data["fee"] = $condition["amount"];
					$data["sub_appid"] = $sub_appid;
					$data["sub_mch_id"] = $sub_mch_id;
					$data["sub_key"] = $sub_key;
					$postdata = sub_pay($data, $_GPC, $_W);
				} else {
					$data["title"] = $condition["service_name"];
					$data["tid"] = $condition["out_trade_no"];
					$data["fee"] = $condition["amount"];
					$postdata = $this->pay($data);
				}
				$postdata["status"] = 1;
				$postdata["id"] = $id;
				if (!empty($moban_id)) {
					$postdata["template_id"] = $moban_id;
				}
			} else {
				pdo_update($xcmodule . "_order", array("status" => 1), array("uniacid" => $_W["uniacid"], "id" => $id));
				pdo_update($xcmodule . "_move", array("member_on +=" => $total), array("uniacid" => $_W["uniacid"], "id" => $condition["pid"]));
				$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
				if ($config) {
					$config["content"] = json_decode($config["content"], true);
				}
				$sms = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "sms"));
				if ($sms) {
					$sms["content"] = json_decode($sms["content"], true);
					$send_mobile = $sms["content"]["mobile"];
					if ($sms["content"]["status"] == 1) {
						require_once IA_ROOT . "/addons/xc_train/resource/sms/sendSms.php";
						if ($sms["content"]["type"] == 1) {
							set_time_limit(0);
							header("Content-Type: text/plain; charset=utf-8");
							$templateParam = array("webnamex" => $config["content"]["title"], "trade" => $condition["out_trade_no"], "amount" => $condition["amount"], "namex" => $condition["name"], "phonex" => $condition["mobile"], "service" => $condition["service_name"]);
							$send = new sms();
							$mobile = explode("/", $send_mobile);
							foreach ($mobile as $m) {
								$result = $send->sendSms($sms["content"]["AccessKeyId"], $sms["content"]["AccessKeySecret"], $sms["content"]["sign"], $sms["content"]["code"], $m, $templateParam);
							}
						} elseif ($sms["content"]["type"] == 2) {
							header("content-type:text/html;charset=utf-8");
							$sendUrl = "http://v.juhe.cn/sms/send";
							$tpl_value = "#webnamex#=" . $config["content"]["title"] . "&#trade#=" . $condition["out_trade_no"] . "&#amount#=免费&#namex#=" . $condition["name"] . "&#phonex#=" . $condition["mobile"] . "&#service#=" . $condition["service_name"];
							$mobile = explode("/", $send_mobile);
							foreach ($mobile as $m) {
								$smsConf = array("key" => $sms["content"]["key"], "mobile" => $m, "tpl_id" => $sms["content"]["tpl_id"], "tpl_value" => $tpl_value);
								$content = juhecurl($sendUrl, $smsConf, 1);
							}
						} elseif ($sms["content"]["type"] == 3) {
							if (!empty($sms["content"]["url"])) {
								$customize = $sms["content"]["customize"];
								$post = $sms["content"]["post"];
								if (is_array($post) && !empty($post)) {
									$post = json_encode($post);
									if (is_array($customize)) {
										foreach ($customize as $x) {
											$post = str_replace("{{" . $x["attr"] . "}}", $x["value"], $post);
										}
									}
									$post = str_replace("{{webnamex}}", $config["content"]["title"], $post);
									$post = str_replace("{{trade}}", $condition["out_trade_no"], $post);
									$post = str_replace("{{amount}}", "免费", $post);
									$post = str_replace("{{namex}}", $condition["name"], $post);
									$post = str_replace("{{phonex}}", $condition["mobile"], $post);
									$post = str_replace("{{service}}", $condition["service_name"], $post);
									$post = json_decode($post, true);
									$data = array();
									foreach ($post as $x2) {
										$data[$x2["attr"]] = $x2["value"];
									}
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
									curl_setopt($ch, CURLOPT_POST, 1);
									curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
									$output = curl_exec($ch);
									curl_close($ch);
								}
								$get = $sms["content"]["get"];
								if (is_array($get) && !empty($get)) {
									$get = json_encode($get);
									if (is_array($customize)) {
										foreach ($customize as $x) {
											$get = str_replace("{{" . $x["attr"] . "}}", $x["value"], $get);
										}
									}
									$get = str_replace("{{webnamex}}", $config["content"]["title"], $get);
									$get = str_replace("{{trade}}", $condition["out_trade_no"], $get);
									$get = str_replace("{{amount}}", "免费", $get);
									$get = str_replace("{{namex}}", $condition["name"], $get);
									$get = str_replace("{{phonex}}", $condition["mobile"], $get);
									$get = str_replace("{{service}}", $condition["service_name"], $get);
									$get = json_decode($get, true);
									$url_data = '';
									foreach ($get as $x3) {
										if (empty($url_data)) {
											$url_data = urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
										} else {
											$url_data = $url_data . "&" . urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
										}
									}
									if (strpos($sms["content"]["url"], "?") !== false) {
										$sms["content"]["url"] = $sms["content"]["url"] . $url_data;
									} else {
										$sms["content"]["url"] = $sms["content"]["url"] . "?" . $url_data;
									}
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
									curl_setopt($ch, CURLOPT_HEADER, 0);
									$output = curl_exec($ch);
									curl_close($ch);
								}
							}
						}
					}
				}
				$print = pdo_get("xc_train_config", array("xkey" => "print", "uniacid" => $uniacid));
				if ($print) {
					$print["content"] = json_decode($print["content"], true);
					if ($print["content"]["status"] == 1) {
						if ($print["content"]["type"] == 1) {
							$service_name = $service["name"];
							$time = time();
							$content = '';
							$content .= "订单号：" . $condition["out_trade_no"] . "\\r\\n";
							$content .= "小程序名：" . $config["content"]["title"] . "\\r\\n";
							$content .= "人数：" . $condition["total"] . "\\r\\n";
							$content .= "课程：" . $condition["title"] . "\\r\\n";
							$content .= "价格：免费\\r\\n";
							$content .= "姓名：" . $condition["name"] . "\\r\\n";
							$content .= "手机：" . $condition["mobile"] . "\\r\\n";
							$sign = strtoupper(md5($print["content"]["apikey"] . "machine_code" . $print["content"]["machine_code"] . "partner" . $print["content"]["partner"] . "time" . $time . $print["content"]["msign"]));
							$requestUrl = "http://open.10ss.net:8888";
							$requestAll = ["partner" => $print["content"]["partner"], "machine_code" => $print["content"]["machine_code"], "time" => $time, "content" => $content, "sign" => $sign];
							$requestInfo = http_build_query($requestAll);
							$request = push($requestInfo, $requestUrl);
						} elseif ($print["content"]["type"] == 2) {
							include dirname(__FILE__) . "/resource/HttpClient.class.php";
							define("USER", $print["content"]["user"]);
							define("UKEY", $print["content"]["ukey"]);
							define("SN", $print["content"]["sn"]);
							define("IP", "api.feieyun.cn");
							define("PORT", 80);
							define("PATH", "/Api/Open/");
							define("STIME", time());
							define("SIG", sha1(USER . UKEY . STIME));
							$orderInfo = "<CB>订单</CB><BR>";
							$orderInfo .= "订单号：" . $condition["out_trade_no"] . "<BR>";
							$orderInfo .= "小程序名：" . $config["content"]["title"] . "<BR>";
							$orderInfo .= "--------------------------------<BR>";
							$orderInfo .= "课程：" . $condition["title"] . "<BR>";
							$orderInfo .= "--------------------------------<BR>";
							$orderInfo .= "人数：" . $condition["total"] . "<BR>";
							$orderInfo .= "价格：免费<BR>";
							$orderInfo .= "姓名：" . $condition["name"] . "<BR>";
							$orderInfo .= "手机：" . $condition["mobile"] . "<BR>";
							$request = wp_print(SN, $orderInfo, 1);
						}
					}
				}
				$postdata["status"] = 2;
				$postdata["id"] = $id;
				if (!empty($moban_id)) {
					$postdata["template_id"] = $moban_id;
				}
			}
			return $this->result(0, "操作成功", $postdata);
		} else {
			return $this->result(1, "生成订单失败");
		}
	}
	public function doPageTeamOrder()
	{
		global $_GPC, $_W, $xcmodule;
		$uniacid = $_W["uniacid"];
		$share_status = -1;
		$share_one = '';
		$share_two = '';
		$share_three = '';
		$share_config = pdo_get("xc_train_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		$share_config = json_decode($share_config["content"], true);
		if (!empty($share_config)) {
			$share_status = $share_config["status"];
			if ($share_config["type"] >= 1 && !empty($share_config["referrer_one"])) {
				$share_one = $share_config["referrer_one"];
			}
			if ($share_config["type"] >= 2 && !empty($share_config["referrer_two"])) {
				$share_two = $share_config["referrer_two"];
			}
			if ($share_config["type"] >= 3 && !empty($share_config["referrer_three"])) {
				$share_three = $share_config["referrer_three"];
			}
		}
		$sql = "SELECT a.*,b.price,b.format,b.share_one,b.share_two,b.share_three FROM " . tablename($xcmodule . "_mall_team") . " a left join " . tablename($xcmodule . "_mall") . " b on a.uniacid=b.uniacid AND a.service=b.id WHERE a.uniacid=:uniacid AND a.status=1 AND a.kucun>0 AND unix_timestamp(a.start_time)<:times AND unix_timestamp(a.end_time)>:times AND a.id=:id AND b.status=1 ORDER BY sort DESC,id DESC LIMIT 1 ";
		$service = pdo_fetch($sql, array(":uniacid" => $_W["uniacid"], ":times" => time(), ":id" => $_GPC["id"]));
		$price = 0;
		$title = '';
		if ($service) {
			if (!empty($service["format"])) {
				$service["format"] = json_decode($service["format"], true);
			}
			$title = $service["name"];
			if ($_GPC["format"] == -1) {
				$price = $service["price"];
			} else {
				$price = $service["format"][$_GPC["format"]]["price"];
				$title = $title . " " . $service["format"][$_GPC["format"]]["name"];
				$condition["format"] = $service["format"][$_GPC["format"]]["name"];
			}
			if (!empty($service["share_one"])) {
				$share_one = $service["share_one"];
			}
			if (!empty($service["share_two"])) {
				$share_two = $service["share_two"];
			}
			if (!empty($service["share_three"])) {
				$share_three = $service["share_three"];
			}
			if (!empty($service["user_limit"])) {
				$user_total = $_GPC["member"];
				$sql = "SELECT sum(total) FROM " . tablename($xcmodule . "_order") . " WHERE uniacid=:uniacid AND openid=:openid AND status=1 AND order_type=10 ";
				$user_order = pdo_fetchcolumn($sql, array(":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"]));
				if ($user_order) {
					$user_total = $user_total + $user_order;
				}
				if ($user_total > $service["user_limit"]) {
					return $this->result(1, "每人限购" . $service["user_limit"]);
				}
			}
		} else {
			return $this->result(1, "活动已结束");
		}
		$store = pdo_get($xcmodule . "_school", array("uniacid" => $_W["uniacid"], "id" => $_GPC["store"], "status" => 1));
		if (!$store) {
			return $this->result(1, "学校错误");
		}
		$address = pdo_get($xcmodule . "_address", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status" => 1));
		if (!$address) {
			return $this->result(1, "地址错误");
		}
		$group_sale = '';
		$group_openid = '';
		if (!empty($_GPC["group_id"])) {
			$group = pdo_get($xcmodule . "_team_group", array("uniacid" => $_W["uniacid"], "id" => $_GPC["group_id"]));
			if ($group) {
				$user_group = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status" => 1, "order_type" => 10, "group_id" => $group["id"]));
				if ($user_group) {
					return $this->result(1, "已参团");
				} else {
					$group_openid = $group["openid"];
					if (!empty($service["member_join"]) && !empty($service["member_sale"])) {
						if ($group["member"] % $service["member_join"] == 0) {
							$group_sale = $service["member_sale"];
						}
					}
				}
			} else {
				return $this->result(1, "团错误");
			}
		} else {
			$user_group = pdo_get($xcmodule . "_team_group", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "service" => $service["id"]));
			if ($user_group) {
				return $this->result(1, "已发起接龙");
			}
		}
		$condition["uniacid"] = $uniacid;
		$condition["openid"] = $_W["openid"];
		$condition["pid"] = $_GPC["id"];
		$condition["service_name"] = $service["name"];
		$condition["service_data"] = json_encode($service);
		$condition["service_price"] = $price;
		$condition["start_time"] = $service["start_time"];
		$condition["end_time"] = $service["end_time"];
		$condition["order_type"] = 10;
		$condition["total"] = $_GPC["member"];
		$condition["amount"] = round(intval($condition["total"]) * floatval($price), 2);
		$condition["o_amount"] = $condition["amount"];
		if (!empty($group_sale)) {
			$condition["o_amount"] = round(floatval($condition["o_amount"]) * floatval($group_sale) / 10, 2);
			$condition["group_sale"] = $group_sale;
		}
		$condition["title"] = $title;
		if (floatval($condition["o_amount"]) < 0) {
			$condition["o_amount"] = 0;
		}
		if (!empty($_GPC["content"])) {
			$condition["content"];
		}
		$condition["name"] = $address["name"];
		$condition["mobile"] = $address["mobile"];
		if (!empty($address["address"])) {
			$condition["address"] = $address["address"];
			if (!empty($address["content"])) {
				$condition["address"] .= " " . $address["content"];
			}
		}
		$condition["pei_type"] = $_GPC["pei_type"];
		if ($condition["pei_type"] == 1) {
			$fee = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
			if ($fee) {
				$fee["content"] = json_decode($fee["content"], true);
				if (!empty($fee["content"]["fee"])) {
					$condition["o_amount"] = round(floatval($condition["o_amount"]) + floatval($fee["content"]["fee"]), 2);
					$condition["fee"] = $fee["content"]["fee"];
				}
			}
		}
		if (!empty($_GPC["content"])) {
			$condition["content"] = $_GPC["content"];
		}
		$condition["store"] = $store["id"];
		$condition["store_name"] = $store["name"];
		$condition["store_data"] = json_encode($store);
		$condition["out_trade_no"] = setcode();
		$share_one_openid = '';
		$share_one_fee = '';
		$share_two_openid = '';
		$share_two_fee = '';
		$share_three_openid = '';
		$share_three_fee = '';
		$user = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($share_status == 1) {
			if (!empty($share_one) && !empty($user["share"])) {
				$share_one_openid = $user["share"];
				$share_one_fee = round(floatval($condition["o_amount"]) * floatval($share_one) / 100, 2);
				$user_one = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user["share"]));
				if ($user_one && !empty($user_one["share"]) && !empty($share_two)) {
					$share_two_openid = $user_one["share"];
					$share_two_fee = round(floatval($condition["o_amount"]) * floatval($share_two) / 100, 2);
					$user_two = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user_one["share"]));
					if ($user_two && !empty($user_two["share"]) && !empty($share_three)) {
						$share_three_openid = $user_two["share"];
						$share_three_fee = round(floatval($condition["o_amount"]) * floatval($share_three) / 100, 2);
					}
				}
			}
		}
		if (!empty($share_one_openid) && !empty($share_one_fee)) {
			$condition["share_one_openid"] = $share_one_openid;
			$condition["share_one_fee"] = $share_one_fee;
		}
		if (!empty($share_two_openid) && !empty($share_two_fee)) {
			$condition["share_two_openid"] = $share_two_openid;
			$condition["share_two_fee"] = $share_two_fee;
		}
		if (!empty($share_three_openid) && !empty($share_three_fee)) {
			$condition["share_three_openid"] = $share_three_openid;
			$condition["share_three_fee"] = $share_three_fee;
		}
		if (!empty($group_openid)) {
			$condition["group_id"] = $_GPC["group_id"];
			$group_fee = 0;
			if (!empty($service["fee"])) {
				$group_fee = round(floatval($condition["o_amount"]) * floatval($service["fee"]) / 100, 2);
			}
			$condition["group_openid"] = $group_openid;
			$condition["group_fee"] = $group_fee;
		}
		$request = pdo_insert("xc_train_order", $condition);
		if ($request) {
			$order_id = pdo_insertid();
			$moban_id = array();
			$moban = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "moban_pay"));
			if ($moban) {
				$moban = json_decode($moban["content"], true);
				if ($moban && $moban["status"] == 1 && !empty($moban["template_id"])) {
					$moban_id[] = $moban["template_id"];
				}
			}
			$sql = "UPDATE " . tablename("xc_train_mall_team") . " SET sold=sold+:sold WHERE id=:id AND uniacid=:uniacid";
			pdo_query($sql, array(":sold" => $condition["total"], ":id" => $condition["pid"], ":uniacid" => $uniacid));
			if (floatval($condition["o_amount"]) > 0) {
				$sub_status = -1;
				$sub_appid = '';
				$sub_mch_id = '';
				$sub_key = '';
				$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $_W["uniacid"]));
				if ($config) {
					$config["content"] = json_decode($config["content"], true);
					if (!empty($config["content"]) && $config["content"]["sub_status"] && !empty($config["content"]["sub_appid"]) && !empty($config["content"]["sub_mch_id"]) && !empty($config["content"]["sub_key"])) {
						$sub_status = $config["content"]["sub_status"];
						$sub_appid = $config["content"]["sub_appid"];
						$sub_mch_id = $config["content"]["sub_mch_id"];
						$sub_key = $config["content"]["sub_key"];
					}
				}
				if ($sub_status == 1 && !empty($sub_appid) && !empty($sub_mch_id) && !empty($sub_key)) {
					$data["title"] = $condition["title"];
					$data["tid"] = $condition["out_trade_no"];
					$data["fee"] = $condition["o_amount"];
					$data["sub_appid"] = $sub_appid;
					$data["sub_mch_id"] = $sub_mch_id;
					$data["sub_key"] = $sub_key;
					$postdata = sub_pay($data, $_GPC, $_W);
				} else {
					$data["title"] = $condition["title"];
					$data["tid"] = $condition["out_trade_no"];
					$data["fee"] = $condition["o_amount"];
					$postdata = $this->pay($data);
				}
				$postdata["status"] = 1;
			} else {
				$request = pdo_update("xc_train_order", array("status" => 1), array("out_trade_no" => $condition["out_trade_no"], "uniacid" => $uniacid));
				if ($request) {
					pdo_update($xcmodule . "_mall_team", array("kucun -=" => $condition["total"]), array("uniacid" => $_W["uniacid"], "id" => $condition["pid"]));
					if (!empty($condition["group_id"])) {
						pdo_update($xcmodule . "_team_group", array("member +=" => 1), array("uniacid" => $_W["uniacid"], "id" => $condition["group_id"]));
					} else {
						pdo_insert($xcmodule . "_team_group", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "service" => $condition["pid"], "member" => 1, "start_time" => $service["start_time"], "end_time" => $service["end_time"], "createtime" => date("Y-m-d H:i:s")));
						$group_id = pdo_insertid();
						pdo_update($xcmodule . "_order", array("group_id" => $group_id), array("out_trade_no" => $condition["out_trade_no"], "uniacid" => $uniacid));
					}
					$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
					if ($config) {
						$config["content"] = json_decode($config["content"], true);
					}
					$sms = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "sms"));
					if ($sms) {
						$sms["content"] = json_decode($sms["content"], true);
						$send_mobile = $sms["content"]["mobile"];
						if (!empty($condition["store"])) {
							$store = pdo_get("xc_train_school", array("id" => $condition["store"], "uniacid" => $uniacid));
							if ($store && !empty($store["sms"])) {
								$send_mobile = $store["sms"];
							}
						}
						if ($sms["content"]["status"] == 1) {
							require_once IA_ROOT . "/addons/xc_train/resource/sms/sendSms.php";
							if ($sms["content"]["type"] == 1) {
								set_time_limit(0);
								header("Content-Type: text/plain; charset=utf-8");
								$templateParam = array("webnamex" => $config["content"]["title"], "trade" => $condition["out_trade_no"], "amount" => $condition["o_amount"], "namex" => $condition["name"], "phonex" => $condition["mobile"], "service" => $condition["title"]);
								$send = new sms();
								$mobile = explode("/", $send_mobile);
								foreach ($mobile as $m) {
									$result = $send->sendSms($sms["content"]["AccessKeyId"], $sms["content"]["AccessKeySecret"], $sms["content"]["sign"], $sms["content"]["code"], $m, $templateParam);
								}
							} elseif ($sms["content"]["type"] == 2) {
								header("content-type:text/html;charset=utf-8");
								$sendUrl = "http://v.juhe.cn/sms/send";
								$tpl_value = "#webnamex#=" . $config["content"]["title"] . "&#trade#=" . $condition["out_trade_no"] . "&#amount#=免费&#namex#=" . $condition["name"] . "&#phonex#=" . $condition["mobile"] . "&#service#=" . $condition["title"];
								$mobile = explode("/", $send_mobile);
								foreach ($mobile as $m) {
									$smsConf = array("key" => $sms["content"]["key"], "mobile" => $m, "tpl_id" => $sms["content"]["tpl_id"], "tpl_value" => $tpl_value);
									$content = juhecurl($sendUrl, $smsConf, 1);
								}
							} elseif ($sms["content"]["type"] == 3) {
								if (!empty($sms["content"]["url"])) {
									$customize = $sms["content"]["customize"];
									$post = $sms["content"]["post"];
									if (is_array($post) && !empty($post)) {
										$post = json_encode($post);
										if (is_array($customize)) {
											foreach ($customize as $x) {
												$post = str_replace("{{" . $x["attr"] . "}}", $x["value"], $post);
											}
										}
										$post = str_replace("{{webnamex}}", $config["content"]["title"], $post);
										$post = str_replace("{{trade}}", $condition["out_trade_no"], $post);
										$post = str_replace("{{amount}}", $condition["o_amount"], $post);
										$post = str_replace("{{namex}}", $condition["name"], $post);
										$post = str_replace("{{phonex}}", $condition["mobile"], $post);
										$post = str_replace("{{service}}", $condition["title"], $post);
										$post = json_decode($post, true);
										$data = array();
										foreach ($post as $x2) {
											$data[$x2["attr"]] = $x2["value"];
										}
										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
										curl_setopt($ch, CURLOPT_POST, 1);
										curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
										$output = curl_exec($ch);
										curl_close($ch);
									}
									$get = $sms["content"]["get"];
									if (is_array($get) && !empty($get)) {
										$get = json_encode($get);
										if (is_array($customize)) {
											foreach ($customize as $x) {
												$get = str_replace("{{" . $x["attr"] . "}}", $x["value"], $get);
											}
										}
										$get = str_replace("{{webnamex}}", $config["content"]["title"], $get);
										$get = str_replace("{{trade}}", $condition["out_trade_no"], $get);
										$get = str_replace("{{amount}}", $condition["o_amount"], $get);
										$get = str_replace("{{namex}}", $condition["name"], $get);
										$get = str_replace("{{phonex}}", $condition["mobile"], $get);
										$get = str_replace("{{service}}", $condition["title"], $get);
										$get = json_decode($get, true);
										$url_data = '';
										foreach ($get as $x3) {
											if (empty($url_data)) {
												$url_data = urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
											} else {
												$url_data = $url_data . "&" . urlencode($x3["attr"]) . "=" . urlencode($x3["value"]);
											}
										}
										if (strpos($sms["content"]["url"], "?") !== false) {
											$sms["content"]["url"] = $sms["content"]["url"] . $url_data;
										} else {
											$sms["content"]["url"] = $sms["content"]["url"] . "?" . $url_data;
										}
										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, $sms["content"]["url"]);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
										curl_setopt($ch, CURLOPT_HEADER, 0);
										$output = curl_exec($ch);
										curl_close($ch);
									}
								}
							}
						}
					}
					$print = pdo_get("xc_train_config", array("xkey" => "print", "uniacid" => $uniacid));
					if ($print) {
						$print["content"] = json_decode($print["content"], true);
						if ($print["content"]["status"] == 1) {
							if ($print["content"]["type"] == 1) {
								$service_name = $service["name"];
								$time = time();
								$content = '';
								$content .= "订单号：" . $condition["out_trade_no"] . "\\r\\n";
								$content .= "小程序名：" . $config["content"]["title"] . "\\r\\n";
								$content .= "人数：" . $condition["total"] . "\\r\\n";
								$content .= "商品：" . $condition["title"] . "\\r\\n";
								$content .= "价格：" . $condition["o_amount"] . "\\r\\n";
								$content .= "姓名：" . $condition["name"] . "\\r\\n";
								$content .= "手机：" . $condition["mobile"] . "\\r\\n";
								$sign = strtoupper(md5($print["content"]["apikey"] . "machine_code" . $print["content"]["machine_code"] . "partner" . $print["content"]["partner"] . "time" . $time . $print["content"]["msign"]));
								$requestUrl = "http://open.10ss.net:8888";
								$requestAll = ["partner" => $print["content"]["partner"], "machine_code" => $print["content"]["machine_code"], "time" => $time, "content" => $content, "sign" => $sign];
								$requestInfo = http_build_query($requestAll);
								$request = push($requestInfo, $requestUrl);
							} elseif ($print["content"]["type"] == 2) {
								include IA_ROOT . "/addons/xc_train/resource/HttpClient.class.php";
								define("USER", $print["content"]["user"]);
								define("UKEY", $print["content"]["ukey"]);
								define("SN", $print["content"]["sn"]);
								define("IP", "api.feieyun.cn");
								define("PORT", 80);
								define("PATH", "/Api/Open/");
								define("STIME", time());
								define("SIG", sha1(USER . UKEY . STIME));
								$orderInfo = "<CB>订单</CB><BR>";
								$orderInfo .= "订单号：" . $condition["out_trade_no"] . "<BR>";
								$orderInfo .= "小程序名：" . $config["content"]["title"] . "<BR>";
								$orderInfo .= "--------------------------------<BR>";
								$orderInfo .= "商品：" . $condition["title"] . "<BR>";
								$orderInfo .= "--------------------------------<BR>";
								$orderInfo .= "人数：" . $condition["total"] . "<BR>";
								$orderInfo .= "价格：" . $condition["o_amount"] . "<BR>";
								$orderInfo .= "姓名：" . $condition["name"] . "<BR>";
								$orderInfo .= "手机：" . $condition["mobile"] . "<BR>";
								$request = wp_print(SN, $orderInfo, 1);
							}
						}
					}
				}
				$postdata["status"] = 2;
			}
			$postdata["order_id"] = $order_id;
			if (!empty($moban_id)) {
				$postdata["template_id"] = $moban_id;
			}
			return $this->result(0, "操作成功", $postdata);
		} else {
			return $this->result(1, "生成订单失败");
		}
	}
}