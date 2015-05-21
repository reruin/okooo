<?php
	class logger {
		public static function json($v , $one = false){

			if($one){
				if(count($v)>=1) logger::trace(json_encode($v[0]));
			}else
				logger::trace(json_encode($v));
		}

        public static function trace($v)
        {
			logger::process($v);
        }

        private static function process($v){
			$type = $_SERVER["HTTP_ACCEPT"];
			if(strpos($type , "/json")){
                header("Content-Type:application/json; charset=utf-8;");
                header("Access-Control-Allow-Origin:*");
			}else{
				header("Content-Type:text/javascript; charset=utf-8;");
                header("Access-Control-Allow-Origin:*");
                header("Access-Control-Allow-Headers :'Origin, X-Requested-With, Content-Type, Accept'");
				if(!empty($_GET['callback'])){
					$callback = $_GET['callback'];
					$v = $callback."(".$v.")";
				}
			}

			echo($v);
		}

        public static function errorPower()
        {
            $v = '{"status":-1,"info":"power request!"}';
            logger::process($v);
        }

        public static function info($code , $info ,$obj = false)
        {
        	if($obj) $v = '{"status":"'.$code.'","detail":'.$info.'}';
            else $v = '{"status":"'.$code.'","detail":"'.$info.'"}';
			logger::process($v);
        }

        public static function success($info , $obj = false)
        {
        	if($obj) $v = '{"status":"0","detail":'.$info.'}';
            else $v = '{"status":"0","detail":"'.$info.'"}';
			logger::process($v);
        }


		public static function error($info,$code='-1')
        {
        	$v = '{"status":"'.$code.'","detail":"'.$info.'"}';
			logger::process($v);
        }
    }
?>