<?php
	// this is control class , and it will be inited after runing.
	require_once "com.auth.php";
	class control
	{
		private $auth;
		public function __construct()
		{
			$auth = new auth();
			$this->request_init();
		}


		function getmodule($mod){
			return 'modules/api.'.$mod.'.php';
		}

		function request_init()
		{
			if (isset($_GET['m'])) {
				$mod = $_GET['m'];
				if (isset($_GET['a'])) {
					$act = $_GET['a'];
				} else {
					$act = 'default';
				}
				if (isset($_GET['class'])) {
					$class = $_GET['class'];
				} else {
					$class = "";
				}
				$classFile = $this->getmodule($mod);

				if (file_exists($classFile)) {
					require_once($classFile);
					if (class_exists($mod)) {
						try {
							$instance = new $mod();
							if(method_exists($mod, "do".$act)) {
								if ($this->authenticate($instance,$act)) {
									$act = "do".$act;
									try {
										$result = $instance->$act();
									}
									catch(Exception $error) {
										die($error->getMessage());
									}
								} else {
									die("You do not have access to the requested page!");
								}
							}else{
								die("ERROR:'$act' <br/>An valid method for your request was not found");
							}
						}
						catch(Exception $error) {
							die($error->getMessage());
						}
					} else {
						die("An valid module for your request was not found");
					}
				} else {
					die("An valid module for your request was not found");
				}
			} else {
				die("A valid module was not specified");
			}
		}

		function authenticate($c,$m)
		{
			return (!empty($c->checkMethod))?in_array($m,$c->checkMethod):true;
		}
	}
?>