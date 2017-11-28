<?php
	$pname="landing_page";
	header('Content-Type: application/json');
	header('Access-Control-Allow-Origin: https://klickinvite.com'); //Testing ajax call CORS issue when calling from klickinvite.com
	require_once('../panel/_/config/connect_db.php');
	require_once('../panel/_/helper/helper_functions.php');
	require_once('../panel/_/backend/auth_router.php');

	function show_error($num,$msg)
	{
		echo json_encode(array("status"=>0,"msg"=>$msg,"error_code"=>"0x000DU".$num));
		exit();
	}

	function encode_baseurl_extension($invite_type,$invite_medium)
	{
		$invite_type=intval($invite_type);
		$invite_medium=intval($invite_medium);

		$invite_type_array=array(0,1,2,3); //0-PreInvite, 1-Invite, 2-Reminder, 3-ThankYouMsg
		$invite_medium_array=array(0,1,2); //0-SMS, 1-EMail, 2-WhatsApp
		if(!(in_array($invite_type, $invite_type_array)&&in_array($invite_medium, $invite_medium_array)))
		{
			return null;//error.
		}

		$invite_medium_bin=decbin($invite_medium);
		$invite_type_bin=decbin($invite_type);
		$invite_medium_bin= substr("00",0,2 - strlen($invite_medium_bin)).$invite_medium_bin;
		$invite_type_bin= substr("00",0,2 - strlen($invite_type_bin)).$invite_type_bin;

		//convert bin to hex
		return dechex(bindec($invite_medium_bin.$invite_type_bin));
	}
	function create_curl_request($array="")
	{
		if(!sizeof($array))
			show_error(1,"No valid arguments!");

		$curl = curl_init();
		if ( $_SERVER["SERVER_NAME"] == '127.0.0.1' || $_SERVER["SERVER_NAME"] == 'localhost' || (substr($_SERVER['SERVER_NAME'],0,7)=="192.168"))
	    {
	    	$sublink="/klickinvite/panel/_/backend/guestbook";
	    }
	    else
	    	$sublink="/panel/_/backend/guestbook";

	   	$pre=isSSL()?"https://":"http://";

		$curl_params=$array;/*array of all API 'is_curl'=> '1'*/
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		$strCookie = 'RequestHash='.hash_password(http_build_query($curl_params)).'; path=/';
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => $pre.$_SERVER['HTTP_HOST'].$sublink,
			CURLOPT_USERAGENT => $useragent,
			CURLOPT_COOKIE => $strCookie,
		    CURLOPT_POST => 1,
		    CURLOPT_POSTFIELDS =>http_build_query($curl_params)
		));
		if(!($resp = curl_exec($curl))){
	 	   show_error(2,"Server Issue");// return -1; //unsuccessful
		}
		curl_close($curl);
		$resp=json_decode($resp);
		return $resp;
	}

	function validate_user_credentials()
	{
		$err=array();
		if(!isset($_POST['nick_name']) || !special_chars2(test_input($_POST['nick_name'])))
			array_push($err, "Not a valid name");
		if(!isset($_POST['mobile']) || !is_valid_mobile(test_input($_POST['mobile'])))
			array_push($err, "Not a valid number");
		if(!isset($_POST['email']) || !is_valid_email(test_input($_POST['email'])))
			array_push($err, "Not a valid Email");
		if(sizeof($err))
			return array("status"=>0,"err"=>$err);
		else
			return array("status"=>1);
	}
	function is_demo_order($order_id="",$group_id="")
	{
		$reponse=array();
		if($order_id=="")
			show_error(6,"Invalid Request");
		else
		{
			if($group_id!="")
			{
				$sql="select group_id from group_details where group_id='$group_id'";
				if($result=mysqli_query($GLOBALS['conn'],$sql))
				{
                   if(!mysqli_num_rows($result))
                   	$group_id="";
				}
				else
				  $group_id="";
			}
           if($group_id!="")
			 $check_qry="SELECT OD.`order_id`, OD.`order_status`, OD.`old_status`, OD.`event_category`, OD.`theme`, OD.`domain_subdomain_name`, OD.`host_id`, OD.`created_by`, OD.`admin_id`, OD.`is_demo`,GD.`group_id`,OF.`only_ecard` FROM `order_data` OD, `group_details` GD, `order_features` OF WHERE OD.`order_id`='$order_id' and GD.`order_id`='$order_id' and GD.`enabled`='1' and OF.`order_id`='$order_id' and GD.`group_id`='$group_id' LIMIT 1;";
			else
			  $check_qry="SELECT OD.`order_id`, OD.`order_status`, OD.`old_status`, OD.`event_category`, OD.`theme`, OD.`domain_subdomain_name`, OD.`host_id`, OD.`created_by`, OD.`admin_id`, OD.`is_demo`,GD.`group_id`,OF.`only_ecard` FROM `order_data` OD, `group_details` GD, `order_features` OF WHERE OD.`order_id`='$order_id' and GD.`order_id`='$order_id' and GD.`enabled`='1' and OF.`order_id`='$order_id' LIMIT 1;";
			if($r=mysqli_query($GLOBALS['conn'], $check_qry))
			{
				$response['status']=1;
				if(mysqli_num_rows($r))
				{
					$response['order_flag']=1;
					$response['data']=mysqli_fetch_assoc($r);
				}
				else
					$response['order_flag']=0;
			}
			else
				show_error(7,"Could not connect to Server!");
		}
		return $response;
	}
	function get_guest_invite_url($guest_id,$group_id)
	{
		$response=array();
		$get_url="SELECT `unique_base_url` from `guest_group_relation` where `group_id`='$group_id' and `guest_id`='$guest_id';";
		if($r=mysqli_query($GLOBALS['conn'],$get_url))
		{
			if(mysqli_num_rows($r))
			{
				$response['status']=1;
				$row=mysqli_fetch_assoc($r);
				if (strstr($_SERVER["SERVER_NAME"],"app.klickinvite.com"))
			    {
			    	$sublink="ki1.in/".$row['unique_base_url'];
			    }
			    elseif(strstr($_SERVER["SERVER_NAME"],"i.klickinvite.com"))
				{
			    	$sublink="dev.ki1.in/".$row['unique_base_url'];
				}
				elseif($_SERVER["SERVER_NAME"] == '127.0.0.1' || $_SERVER["SERVER_NAME"] == 'localhost' || (substr($_SERVER['SERVER_NAME'],0,7)=="192.168"))
				{
				$sublink="localhost/klickinvite/t.php?h=".$row['unique_base_url'];
				}
				else
				{
			    	$sublink="ki1.in/".$row['unique_base_url'];
				}

				$pre=isSSL()?"https://":"http://";
				$response['url']=$pre.$sublink.encode_baseurl_extension(1,2);//for now it is 1 and 2
			}
			else
			{
				$response['status']=0;
				$response['msg']="No Url Found";
			}
		}
		else
			show_error(13,"Server Error");
		return $response;
	}
	function get_a_demo_order()
	{
		$order_id="";
		$sql="SELECT `order_id` FROM `order_data` where `is_demo`='1' and `order_status` NOT IN(-1,-2) LIMIT 1";
		if(!$r=mysqli_query($GLOBALS['conn'],$sql))
		{
			show_error(16,"Could not Connect to Server.");
		}
		else
		{
			if(mysqli_num_rows($r)==0)
				show_error(17,"No demo Order Found");
			else
			{
				$row=mysqli_fetch_assoc($r);
				$order_id=$row['order_id'];//need a hard coded order id
			}
		}
		return $order_id;
	}
	/*
			SCRIPT STARTS
	*/
	/*========================CRUCIAL AREA=============================*/
	if(isset($_POST['id']))
	{
		/*IT will be in base64 encoded reverse format*/
		$id=strrev(test_input($_POST['id']));
		if(!$id=base64_decode($id,TRUE))
		{
				$order_id=get_a_demo_order();
				$group_id="";
		}
		else
		{
			$id=explode("||",$id);
			$order_id=$id[0];
			if(sizeof($id)=='2')
				$group_id=$id[1];
			else
				$group_id="";
		}
	}
	else
	{
		//taking the first Demo order
		$order_id=get_a_demo_order();
		$group_id="";
	}

	/*================ ABOVE ELSE BLOCK IS IMPORTATNT===================*/


	if(!isset($_POST['action_id']))
		show_error("3","Invalid Request");

	$action_id_temp=intval(test_input($_POST['action_id']));

	switch ($action_id_temp) {
		case 1:
			$valid=validate_user_credentials();
			if($valid['status']==0)
				show_error(5,implode(" ",$valid['err']));
			else
			{
				/* Checking Order features*/
				$order_feature=is_demo_order($order_id,$group_id);
				if($order_feature['status']==0)
					show_error(8,"could not connect to Server");
				else
				{
					if($order_feature['order_flag']==0)
						show_error(9,"Invalid Order!");
				}
				if($order_feature['data']['is_demo']==0)
					show_error(10,"Not a demo order!");
				/* PREPARING API FOR CURL FOR ADDING A GUEST NO GROUP REQUIRED*/
				ini_set('max_execution_time', 120);//2 min
				$details=array();
				$details['nick_name']=test_input($_POST['nick_name']);
				$details['mobile']=test_input($_POST['mobile']);
				$details['email']=test_input($_POST['email']);
				$details['is_international']=0;
				$details['action_id']=401;
				$details['order_id']=$order_id;
				$details['country_code']="91";
				$details['display_name']="";
				$details['is_curl']=1;
				$resp=create_curl_request($details);
				if(!isset($resp->status))
					show_error(14,"Could not connect to Server!");
				if(($resp->status)=="0")
					show_error(11,$resp->msg);
				$selected_group=intval($order_feature['data']['group_id']);
				$selected_guest=array();
				array_push($selected_guest, $resp->data->guest_id);
				/*Update group group if already added*/
				$change_group=array();
				$change_group['action_id']=14;
				$change_group['order_id']=$order_id;
				$change_group['new_group']=$selected_group;
				$change_group['selected_guests']=$selected_guest;
				$change_group['is_curl']=1;
				$group_resp=create_curl_request($change_group);
				/*Now Create API to hit invite request*/
				$invite_api=array();
				$invite_api['action_id']=7;
				$invite_api['order_id']=$order_id;
				$invite_api['selected_group']=$selected_group;
				$invite_api['selected_guests']=$selected_guest;
				$invite_api['im']=2;
				$invite_api['itype']=1;
				$invite_api['is_curl']=1;
				$invite_resp=create_curl_request($invite_api);
				/*if(!isset($invite_resp->status))
					show_error(15,"Could not connect to Server!");*/
				if(!$invite_resp->status)
					show_error(12,$invite_resp->msg);

				$invite_url=get_guest_invite_url($selected_guest[0],$selected_group);

				$invite_url['only_ecard']=intval($order_feature['data']['only_ecard']);
				$invite_url['resp_both']=intval($invite_resp->sending->stats->both);
				$invite_url['resp_sms']=intval($invite_resp->sending->stats->sms);
				$invite_url['resp_email']=intval($invite_resp->sending->stats->email);
				echo json_encode($invite_url);
			}
			exit();
			break;

		default:
			show_error(4,"Invalid Request");
			break;
	}
?>
