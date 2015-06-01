<?php

	class soccer
	{
		private $db;

		function __construct()
		{
            $this->db = new D("soccer_match");
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

        public function doodds()
        {
            $match = $_REQUEST["match"];
            $type  = $_REQUEST["id"];
            $url = "http://www.okooo.com/soccer/match/".$match."/odds/change/".$type."/";

            $referer = "http://www.okooo.com/soccer/match/".$match."/odds/";

            $context = array(
                'http' => array (
                    'header'=> 'Referer: ' . $referer

                )
            );
            $xcontext = stream_context_create($context);
            $res = file_get_contents($url, true, $xcontext);

            $res = iconv("GB2312","UTF-8//IGNORE",$res);
            //去除多余的 span
            $res = preg_replace("/(<\/?span[^>]*>|&darr;|&uarr;)/" ,"" , $res);

            preg_match_all("/<td[^>]*>([\w\W]+?)<\/td>/" , $res , $m);

            for($i=12;$i<count($m[1]);$i++){
                echo($m[1][$i]);echo(",");
            }

        }

		public function dolist()
		{
		    $date = $_REQUEST['date'];
		    if(empty($date)) $date = date("Y-m-d");
		    $url = "http://www.okooo.com/jingcai/shuju/zhishu/".$date;
		    $year = date("Y");

            $res = file_get_contents($url);
            $res = iconv("GB2312","UTF-8//IGNORE",$res);

            $res = explode("</table",explode("magazine_tableh",$res)[1])[0];
            $res = preg_replace("/<a href=\"\/soccer\/match\/(\d+)\/odds[^>]+>欧<\/a>/m" ,"$1" , $res);

            $clear_g = array(
                "/<\/?(span|p|b)[^>]*>/" //传统标签
                ,"/(\s|\"|'|,|;|<i>[\w\W]+?<\/i>|<a[^>]*>[\w\W]+?<\/a>|&nbsp)/" // 空格
                ,"/<(td)[^>]*>/" // 分割
            );

            $res = preg_replace($clear_g ,"" , $res);

            $res = preg_replace(array("/<tr[^>]*>/","/<\/tr>/","/(<\/td>)/","/\/,,/","/\]\[/","/胜/","/负/","/平/","/,,/","/VS/","/,,/") , array("[","]",",","","],[",2,0,1,",","0-0",",-1,"), $res);

            $res = preg_replace("/\[([^,]+?),([^,]+?),(\d\d-\d\d)(\d\d:\d\d),([^,]+?),([-\d]+)-([-\d]+),([^,]+),([\d\.]+,[\d\.]+,[\d\.]+,[\d\.]+,[\d\.]+,[\d\.]+,[\-\d]+,\d+)\]/m",'["$1","$2","'.$year.'-$3 $4","$5",$6,$7,"$8",$9]',$res);


            $res = "[".explode("</th>],",$res)[1]."]";


            if($_REQUEST["save"])
            {

                $res = json_decode($res , true);
                $sql = "";

                for($i = 0; $i < count($res); $i++ ){

                    if($res[$i][13]!="-1"){

                        $sql = $sql . ($sql == "" ? "" :";") .  $this->db->insert(array(
                            "match_oid"=>$res[$i][0],
                            "type"=>$res[$i][1],
                            "match_time"=>$res[$i][2],
                            "home"=>$res[$i][3],
                            "home_goal"=>$res[$i][4],
                            "away_goal"=>$res[$i][5],
                            "away"=>$res[$i][6],
                            "result"=>$res[$i][13],
                            "match_id"=>$res[$i][14]
                        ) , true);
                    }

                }
                //echo($sql);
                $this->db->exec($sql);
            }

            logger::success(json_encode($res) , true);
		}
	}
?>