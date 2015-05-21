<?php

	class soccer
	{
		private $db;

		function __construct()
		{

		}

		public function dodefault()
        {
            $id = $_REQUEST['id'];
            $type = $_REQUEST['type'];
			if(empty($type))
            {
                $type = "BaijiaBooks";
            }

            if(empty($id)) logger::error('require id');

            $referer = "http://www.okooo.com/soccer/match/".$id."/odds/";

            $context = array(
                'http' => array (
                    'header'=> 'Referer: ' . $referer

                )
            );

            $url = $referer."ajax/?page=0&companytype=".$type."&type=0";

            $xcontext = stream_context_create($context);

            $result = file_get_contents($url, true, $xcontext);

            $resp = explode("var data_str = '",$result);

            $resp = explode("';",$resp[1]);

			logger::trace($resp[0]);
		}
	}
?>