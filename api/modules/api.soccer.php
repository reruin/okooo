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

        public function docalc(){

            $model = json_decode($_REQUEST['model'] , true);

            if($model){
                $m = $model["odds"];
                $calc = $model["calc"];

                $where = "1=1";
                $select = $model["bet"] ? $model["bet"] : "ave";
                for($i=0;$i<count($m);$i++){

                    $home = ($m[$i]["home"] && count($m[$i]["home"]) == 2) ? $m[$i]["home"] : array(0,99);
                    $away = ($m[$i]["away"] && count($m[$i]["away"]) == 2) ? $m[$i]["away"] : array(0,99);
                    $draw = ($m[$i]["draw"] && count($m[$i]["draw"]) == 2) ? $m[$i]["draw"] : array(0,99);
                    $type = $m[$i]["type"];

                    if(!$type){
                        logger::error("error model , miss type");
                        exit();
                    }
                    $where .= " and {$type}_start_home>={$home[0]} and {$type}_start_home<={$home[1]} and {$type}_start_away>={$away[0]} and {$type}_start_away<={$away[1]} and {$type}_start_draw>={$draw[0]} and {$type}_start_draw<={$draw[1]}";
                    //if($r) $res = array_merge($res , $r);
                }
                $res = $this->db->select($where,"{$select}_start_home as h,{$select}_start_draw as d,{$select}_start_away as a,result as r");

                $len = count($res);
                $cost = 0; // 成本
                $return = 0; //收益
                for($i=0;$i<$len;$i++){
                    //买负
                    if(in_array(0 , $calc)){
                       $cost++;
                       if( $res[$i]["r"] == 0 ) $return += $res[$i]["a"];
                    };

                    //买平
                    if(in_array(1 , $calc)){
                       $cost++;
                       if( $res[$i]["r"] == 1 ) $return += $res[$i]["d"];
                    };

                    //买胜
                    if(in_array(2 , $calc)){
                       $cost++;
                       if( $res[$i]["r"] == 2 ) $return += $res[$i]["h"];
                    };
                }

                $v = round(10000 * ($return-$cost) / $cost ) / 10000;
                logger::success( json_encode(array(
                    count=>$len,
                    r=>$v
                )) , true);
            }else{
                logger::error("error model");
            }
        }

        public function doconv(){
            $page = intval($_REQUEST['page']);
            $type = $_REQUEST['type'];
            $from = 1000 * $page;
            $to = 1000;
            //$sql = "select  m from soccer_match limit ".$from.",".$to;
            $r = $this->db->select("1","match_id,".$type."_main as m","","limit ".$from.",".$to);
            //echo($sql);
            //print_r($r);

            if($r){
                $len = count($r);
                $sql = "";
                for($i = 0;$i<$len;$i++){
                    $v = json_decode($r[$i]["m"] , true);
                    $id = $r[$i]["match_id"];
                    if(count($v) == 6){
                        $sql = $sql . ($sql == "" ? "" :";") . $this->db->update( "match_id=".$id , array(
                            $type."_start_home"=>$v[0],
                            $type."_start_draw"=>$v[1],
                            $type."_start_away"=>$v[2],
                            $type."_end_home"=>$v[3],
                            $type."_end_draw"=>$v[4],
                            $type."_end_away"=>$v[5]
                        ) , true);
                    }

                }
                if($sql) $this->db->exec($sql);
                logger::success("success");
            }

        }

        public function odds($type  , $match)
        {
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

            if(strpos($res , "赛前</td>")){
                $all = "";
            }else{
                //去除多余的 span
                $res = preg_replace(array("/(<\/?span[^>]*>|&darr;|&uarr;|<td colspan=\"12\">变化列表<\/td>|\(终\))/","/(\d)\/(\d\d)\/(\d\d)/m") ,array("","$1-$2-$3") , $res);

                preg_match_all("/<td[^>]*>([\w\W]+?)<\/td>/" , $res , $m);
                $all = array();
                for($i=12;$i<count($m[1]);$i=$i+12){
                //probability;
                    $t = $m[1];
                    array_push($all , array(
                        "time"=>$t[$i+0] ,
                        "odds"=>array($t[$i+2] , $t[$i+3] , $t[$i+4]),
                        "probability"=>array($t[$i+5] , $t[$i+6] , $t[$i+7]),
                        "kelly"=>array($t[$i+8] , $t[$i+9] , $t[$i+10]),
                        "payoff"=>$t[$i+11]
                    ));
                }
                $all = json_encode($all);
            }
            return $all;
        }

        public function doodds()
        {
            $match = $_REQUEST["match"];
            $type  = $_REQUEST["id"];
            $field = $_REQUEST["field"];
            $save  = $_REQUEST["save"];
            if($match && $type){
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

                $all = array();

                if(strpos($res , "赛前</td>") == false){
                    //去除多余的 span
                    $res = preg_replace(array("/(<\/?span[^>]*>|&darr;|&uarr;|<td colspan=\"12\">变化列表<\/td>|\(终\)|\(实时\)|\(初\))/","/(\d)\/(\d\d)\/(\d\d)/m") ,array("","$1-$2-$3") , $res);

                    preg_match_all("/<td[^>]*>([\w\W]+?)<\/td>/" , $res , $m);
                    $all = array();
                    $main = array();

                    $len = count($m[1]);
                    $step = 12;
                    // 超过 100条时，平均采样
                    if($len>100*12){
                        $step = 12 * ceil( ( $len/12 - 1) / 100 );
                        if($step < 12) $step = 12;
                    }

                    for($i=12;$i<$len;$i=$i+$step){
                    //probability;
                        $t = $m[1];
                        if($i == 12 || $i == $len-12){
                           array_push($main , $t[$i+2] , $t[$i+3] , $t[$i+4]);
                        }
                        array_push($all , array(
                            "time"=>$t[$i+0] ,
                            "odds"=>array($t[$i+2] , $t[$i+3] , $t[$i+4]),
                            "probability"=>array($t[$i+5] , $t[$i+6] , $t[$i+7]),
                            "kelly"=>array($t[$i+8] , $t[$i+9] , $t[$i+10]),
                            "payoff"=>$t[$i+11]
                        ));

                        //大量数据 取插值，保证 初采集到
                        if($i + $step > $len  && $i < $len - 12 && $step > 12){
                            $i = $len - $step - 12;
                        }
                    }
                }
                $all = json_encode($all);
                $save_field = array(
                    $field=>$all ,
                    $field."_main"=>"[".implode(",",$main)."]"
                );
                if(count($main) == 6){
                    $save_field[$field."_start_home"] = $main[0];
                    $save_field[$field."_start_draw"] = $main[1];
                    $save_field[$field."_start_away"] =$main[2];
                    $save_field[$field."_end_home"] = $main[3];
                    $save_field[$field."_end_draw"] = $main[4];
                    $save_field[$field."_end_away"] = $main[5];
                }

                if($save){
                    $this->db->update('match_id='.$match , $save_field);
                    logger::success("success");
                }else{
                    logger::success($all , true);
                }
            }else
            {
                logger::error('reqire match and id');
            }


        }
        public function dolist2(){
            $date = $_REQUEST['date'];
            if(empty($date)) $date = date("Y-m-d");
            $url = "http://www.okooo.com/jingcai/shuju/peilv/".$date;
            $year = explode("-",$date)[0];

            $res = file_get_contents($url);
            $res = iconv("GB2312","UTF-8//IGNORE",$res);

            echo($res);
        }

		public function dolist()
		{
		    $date = $_REQUEST['date'];
		    if(empty($date)) $date = date("Y-m-d");
		    $url = "http://www.okooo.com/jingcai/shuju/zhishu/".$date;
		    $year = explode("-",$date)[0];

            $res = file_get_contents($url);
            $res = iconv("GB2312","UTF-8//IGNORE",$res);

            $res = explode("</table",explode("magazine_tableh",$res)[1])[0];

            // 有些没有赛事名称
            $res = preg_replace(array("/<a href=\"\/soccer\/match\/(\d+)\/odds[^>]+>欧<\/a>/m",'/<span class="lsname"><\/span>/') ,array("$1","未知") , $res);

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

                    //if($res[$i][13]!="-1"){

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
                        ) , true , " on duplicate key update result=".$res[$i][13].",home_goal=".$res[$i][4].",away_goal=".$res[$i][5].",match_time='".$res[$i][2]."'");
                    //}

                }
                $this->db->exec($sql);
                logger::success(json_encode($res) , true);
            }else
                logger::success($res , true);
		}
	}
?>