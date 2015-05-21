<?php
	class auth
	{
		public $checkMethod = array();
		public $useLogin = false;
		var $allowuser = array("157055644","147945634");
		function auth()
		{

		}

		public function haslogin()
		{
			return ($_SESSION['login']==true)?true:false;
		}

		function dologin()
		{
			$rurl = ($_GET['rurl']);if(empty($rurl)) $rurl = "";

			if($_SESSION['login']==true)
			{
				header("location:".urldecode($rurl));
				exit();
			}
			else
			{
				$u = ($_POST['username']);
				if(!empty($u))
				{
					if(in_array($u,$this->allowuser))
					{
						$_SESSION['login'] = true;
						header("location:".urldecode($rurl));
						exit();
					}else
						header("location:".'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				}
				else
					echo("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\"><head><title>LOGIN</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><style type=\"text/css\">body{margin:0;background:#000;font-family:'Microsoft YaHei',arial;color:#fff;text-align:center;}.botton{padding:15px;}.logo{margin:0 auto;margin-top:300px;}</style></head><body><form  method='post' action='fm.php?user-login&rurl=".$rurl."'><div class='logo'>ID:<input type='text' name='username' id='username' /><input type='submit' value='LOGIN' /></div></form></body></html>");
			}

		}

		function dologout()
		{
			unset($_SESSION['login']);
		}
	}
?>