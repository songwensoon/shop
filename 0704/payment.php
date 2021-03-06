<?php
/**
 * @copyright Copyright(c) 2011 jooyea.cn
 * @file payment.php
 * @brief 支付方式 操作类
 * @author kane
 * @date 2011-01-20
 * @version 0.6
 * @note
 */

/**
 * @class Payment
 * @brief 支付方式 操作类
 */
//支付状态：支付失败
define ( "PAY_FAILED", - 1);
//支付状态：支付超时
define ( "PAY_TIMEOUT", 0);
//支付状态：支付成功
define ( "PAY_SUCCESS", 1);
//支付状态：支付取消
define ( "PAY_CANCEL", 2);
//支付状态：支付错误
define ( "PAY_ERROR", 3);
//支付状态：支付进行
define ( "PAY_PROGRESS", 4);
//支付状态：支付无效
define ( "PAY_INVALID", 5);

class Payment
{
	/**
	 * @brief 创建支付类实例
	 * @param $payment_id int 支付方式ID
	 * @return 返回支付插件类对象
	 */
	public static function createPaymentInstance($payment_id)
	{
		$paymentRow = self::getPaymentById($payment_id);

		if($paymentRow && isset($paymentRow['class_name']) && $paymentRow['class_name'])
		{
			$class_name = $paymentRow['class_name'];
			$classPath  = IWeb::$app->getBasePath().'plugins/payments/pay_'.$class_name.'/'.$class_name.'.php';
			if(file_exists($classPath))
			{
				require_once($classPath);
				return new $class_name($payment_id);
			}
			else
			{
				IError::show(403,'支付接口类'.$class_name.'没有找到');
			}
		}
		else
		{
			IError::show(403,'支付方式不存在');
		}
	}

	/**
	 * @brief 根据支付方式配置编号  获取该插件的详细配置信息
	 * @param $payment_id int    支付方式ID
	 * @param $key        string 字段
	 * @return 返回支付插件类对象
	 */
	public static function getPaymentById($payment_id,$key = '')
	{
		$paymentDB  = new IModel('payment');
		$paymentRow = $paymentDB->getObj('id = '.$payment_id);

		if($key)
		{
			return isset($paymentRow[$key]) ? $paymentRow[$key] : '';
		}
		return $paymentRow;
	}

	/**
	 * @brief 获取订单中的支付信息 M:必要信息; R表示店铺; P表示用户;
	 * @param $payment_id int    支付方式ID
	 * @param $type       string 信息获取方式 order:订单支付;recharge:在线充值;
	 * @param $argument   mix    参数
	 * @return array 支付提交信息
	 */
	public static function getPaymentInfo($payment_id,$type,$argument)
	{
		//最终返回值
		$payment = array();

		//获取公共信息
		$paymentRow = self::getPaymentById($payment_id);
		$payment['M_PartnerId']  = $paymentRow['partner_id'];
		$payment['M_PartnerKey'] = $paymentRow['partner_key'];

		if($type == 'order')
		{
			$order_id = $argument;

			//获取订单信息
			$orderObj = new IModel('order');
			$orderRow = $orderObj->getObj('id = '.$order_id.' and status = 1');
			if(empty($orderRow))
			{
				IError::show(403,'订单信息不正确，不能进行支付');
			}

			$payment['M_Remark']    = $orderRow['postscript'];
			$payment['M_OrderId']   = $orderRow['id'];
			$payment['M_OrderNO']   = $orderRow['order_no'];
			$payment['M_Amount']    = $orderRow['order_amount'];

			//用户信息
			$payment['P_Mobile']    = $orderRow['mobile'];
			$payment['P_Name']      = $orderRow['accept_name'];
			$payment['P_PostCode']  = $orderRow['postcode'];
			$payment['P_Telephone'] = $orderRow['telphone'];
			$payment['P_Address']   = $orderRow['address'];
		}
		else if($type == 'recharge')
		{
			if(ISafe::get('user_id') == null)
			{
				IError::show(403,'请登录系统');
			}

			if(!isset($argument['account']) || $argument['account'] <= 0)
			{
				IError::show(403,'请填入正确的充值金额');
			}

			$rechargeObj = new IModel('online_recharge');
			$reData      = array(
				'user_id'     => ISafe::get('user_id'),
				'recharge_no' => Order_Class::createOrderNum(),
				'account'     => $argument['account'],
				'time'        => ITime::getDateTime(),
				'payment_name'=> $argument['paymentName'],
			);
			$rechargeObj->setData($reData);
			$r_id = $rechargeObj->add();

			//充值时用户id跟随交易号一起发送,以"_"分割
			$payment['M_OrderNO'] = 'recharge_'.$reData['recharge_no'];
			$payment['M_OrderId'] = $r_id;
			$payment['M_Amount']  = $reData['account'];
		}else if($type == 'membershipfees')
		{
			if(ISafe::get('user_id') == null)
			{
				IError::show(403,'请登录系统');
			}

			if(!isset($argument['account']) || $argument['account'] <= 0)
			{
				IError::show(403,'会员金额不正确');
			}

			$membershipObj = new IModel('membership_fees');
			$reData      = array(
				'user_id'     => ISafe::get('user_id'),
				'fees_no' => Order_Class::createOrderNum(),
				'account'     => $argument['account'],
				'time'        => ITime::getDateTime(),
				'payment_name'=> $argument['paymentName'],
			);
			$membershipObj->setData($reData);
			$r_id = $membershipObj->add();

			//充值时用户id跟随交易号一起发送,以"_"分割
			$payment['M_OrderNO'] = 'membershipfees_'.$reData['fees_no'];
			$payment['M_OrderId'] = $r_id;
			$payment['M_Amount']  = $reData['account'];
		}

		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();

		//交易信息
		$payment['M_Time']      = time();
		$payment['M_Paymentid'] = $payment_id;

		//店铺信息
		$payment['R_Address']   = isset($site_config['address']) ? $site_config['address'] : '';
		$payment['R_Name']      = isset($site_config['name'])    ? $site_config['name']    : '';
		$payment['R_Mobile']    = isset($site_config['mobile'])  ? $site_config['mobile']  : '';
		$payment['R_Telephone'] = isset($site_config['phone'])   ? $site_config['phone']   : '';

		return $payment;
	}

	//更新在线充值
	public static function updateRecharge($recharge_no)
	{
		$rechargeObj = new IModel('online_recharge');
		$rechargeRow = $rechargeObj->getObj('recharge_no = "'.$recharge_no.'"');
		if(empty($rechargeRow))
		{
			return false;
		}

		if($rechargeRow['status'] == 1)
		{
			return true;
		}

		$dataArray = array(
			'status' => 1
		);

		$rechargeObj->setData($dataArray);
		$result = $rechargeObj->update('recharge_no = "'.$recharge_no.'"');

		if($result == '')
		{
			return false;
		}

		$money   = $rechargeRow['account'];
		$user_id = $rechargeRow['user_id'];

		$memberObj = new IModel('member');
		$dataArray = array(
			'balance' => 'balance + '.$money
		);
		$memberObj->setData($dataArray);
		$is_success = $memberObj->update('user_id = '.$user_id,'balance');

		if($is_success)
		{
			$log = new AccountLog();
			$config=array(
				'user_id'  => $user_id,
				'event'    => 'recharge',
				'note'     => '用户['.$user_id.']在线充值',
				'num'      => $money,
			);
			$re = $log->write($config);
		}
		return $is_success;
	}

	//更新用户组 会员申请
	public static function updateMembership($fees_no)
	{
		$feesObj = new IModel('membership_fees');
		$feesRow = $feesObj->getObj('fees_no = "'.$fees_no.'"');
		if(empty($feesRow))
		{
			return false;
		}

		if($feesRow['status'] == 1)
		{
			return true;
		}
		$dataArray = array(
			'status' => 1
		);

		$feesObj->setData($dataArray);
		$result = $feesObj->update('fees_no = "'.$fees_no.'"');

		if($result == '')
		{
			return false;
		}
		$money   = $feesRow['account'];
		$user_id = $feesRow['user_id'];
		$memberObj = new IModel('member');
		
		$memberRow = $memberObj->getObj('user_id = "'.$user_id.'"');
		
		$membership_endtime = $memberRow['membership_endtime'];
		$membership_starttime = time();
		if(!empty($membership_endtime))
		{
			$membership_endtime = strtotime("+1 years",$membership_endtime);
		}else
		{
			$membership_endtime = strtotime("+1 years");
		}
		//用户的推荐会员 recommended
		$recommended = $memberRow['recommended'];
		//获取会员注册积分
		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();
		$memberpoint = $site_config['memberpoint'];
		$dataArray = array(
			'point' => 'point + '.$memberpoint,
			'membership_starttime' => $membership_starttime,
			'membership_endtime' => $membership_endtime,
			'group_id'=>2
		);
		$memberObj->setData($dataArray);
		$is_success = $memberObj->update('user_id = '.$user_id,array('membership_starttime','membership_endtime','point','group_id'));

		if($is_success)
		{
			$log = new AccountLog();
			$config=array(
				'user_id'  => $user_id,
				'event'    => 'membershipfees',
				'note'     => '用户['.$user_id.']申请会员成功，获得积分'.$memberpoint,
				'num'      => $money,
			);
			$re = $log->write($config);
		}
		
		//缴费成功后 分发推荐会员积分和计算 主管是否可以升级
		self::updateIntegral($recommended);

		return $is_success;
	}
	
	//更新推荐用户积分 主管是否可以升级
	public static function updateIntegral($recommended)
	{
		if(empty($recommended))
		{
			return false;
		}
		//获取user_id
		$userObj       = new IModel('user');
    	$where         = "username = '".$recommended."'";
    	$userRow = $userObj->getObj($where);
		$user_id = $userRow['id'];
		$group_id = $userRow['group_id'];
		//获取推荐用户数
		$memberObj       = new IModel('member');
		$where           = "recommended = '".$recommended."'";
		$memberRows = $memberObj->query($where);
		$count = count($memberRows);

		//获取推荐会员注册积分
		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();
		$recommended_points = $site_config['recommended_points'];
		//如果推荐人员大于等于5 则升级为主管
		
		$dataArray = array(
			'point' => 'point + '.$recommended_points
		);
		$m_array = array('point');
		if(($count>=5)&&($group_id==2)){
			$dataArray['group_id'] = 3;
			$m_array[] = 'group_id';
		}
		$memberObj->setData($dataArray);
		//更新推荐用户积分
		$is_success = $memberObj->update('user_id = '.$user_id,$m_array);
		
		if($is_success)
		{
			$log = new AccountLog();
			if(($count>=5)&&($group_id==2))
			{
				$note = '用户['.$user_id.']获得推荐积分,达到主管的条件，升级为主管';
			}else{
				$note = '用户['.$user_id.']获得推荐积分';
			}
			$config=array(
				'user_id'  => $user_id,
				'event'    => 'integral',
				'note'     => '用户['.$user_id.']获得推荐积分',
				'num'      => $recommended_points,
			);
			$re = $log->write($config);
		}
		return $is_success;
	}

}
