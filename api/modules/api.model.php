<?php
    class model
    {
        private $db;

        function __construct()
        {
            $this->db = new D("soccer_match");
        }

        public function dodefault()
        {

        }

        public function docalc3()
        {
            $where = $_REQUEST["where"];
            $select = $_REQUEST["select"];//下注公司
            $bet = $_REQUEST["bet"]; //下注类型
            $res = $this->db->select($where," sum({$select}_home) as h,sum({$select}_away) as a,sum({$select}_draw) as d, count(match_id) as c");


            $cost = 0; // 成本
            $return = 0; //收益
            if($res){
               //买负
               if( strpos($bet , '0')>=0 ){
                  $cost += $res[0]["c"];
                  $return += $res[0]["a"];
               };

               //买平
               if( strpos($bet , '1')>=0 ){
                  $cost += $res[0]["c"];
                  $return += $res[0]["d"];
               };

               //买胜
               if( strpos($bet , '2')>=0 ){
                  $cost += $res[0]["c"];
                  $return += $res[0]["h"];
               };

               $v = round(10000 * ($return-$cost) / $cost ) / 10000;
               logger::success( json_encode(array(
                   count=>$res[0]["c"],
                   r=>$v
               )) , true);
            }else{
                logger::success( json_encode(array(
                   count=>0,
                   r=>0
               )) , true);
            }
        }

        public function doget()
        {
            $where = $_REQUEST["where"];
            $order = json_decode( $_REQUEST["order"] );

            $arr = array();
            for($i = 0 ; $i < count($order); $i++ ){
                $res = $this->db->select("{$where} group by v order by v asc","count(*) as c, {$order[$i]} as v");
                array_push($arr , $res);
            }

            logger::success( json_encode($arr) , true);
        }

        public function dostatistics()
        {
            $where = $_REQUEST["where"];
            $order = $_REQUEST["order"];
            $res = $this->db->select("{$where} group by {$order} order by v asc","count(*) as c,{$order} as v");
            logger::success( json_encode($res) , true);
        }

        public function docalc()
        {
            $where = json_decode( $_REQUEST["where"] , true);
            $bet = json_decode( $_REQUEST["bet"] ,true);
            $res = json_decode( $_REQUEST["result"] , true);

            $count = $this->db->select("1=1","count(*) as c")[0]["c"];

            $arr = array();
            if(count($where) == count($bet) && count($bet) == count($res)){
            //SELECT result,count(result) as c,sum(bet365_start_home) as home,

                for($i=0;$i<count($bet);$i++)
                {
                    $r = $this->db->select("{$where[$i]} and result>=0 group by result","result as r, count(result) as c,sum({$bet[$i]}_home) as home,sum({$bet[$i]}_draw) as draw,sum({$bet[$i]}_away) as away");

                    //累加场数作为 成本
                    $len = $r[0]["c"] + $r[1]["c"] + $r[2]["c"];
                    $cost = strlen($res[$i]) * $len;
                    $return = 0;

                    if( strpos($res[$i] , '0') !== false ){
                       $return += $r[0]["away"];//累加负场数作为 收益
                    };
                    //买平
                    if( strpos($res[$i] , '1') !== false  ){
                       $return += $r[1]["draw"];//累加平场数作为 收益
                    };

                    //买胜
                    if( strpos($res[$i] , '2') !== false  ){
                       $return += $r[2]["home"];//累加胜场数作为 收益
                    };

                    $r = round(10000 * ($return-$cost) / $cost ) / 10000;
                    array_push($arr , array(
                        "count"=>$len,
                        "p"=>round(10000 * $len / $count)/10000,
                        "r"=>$r
                    ));
                }

                logger::success(json_encode($arr) , true);

            }

        }

        public function docalc2()
        {
            $where = $_REQUEST["where"];
            $select = $_REQUEST["select"];//下注公司
            $bet = $_REQUEST["bet"]; //下注类型
            $res = $this->db->select($where,"{$select}_home as h,{$select}_draw as d,{$select}_away as a,result as r");
            $count = $this->db->select("1=1","count(*) as c")[0]["c"];
            $len = count($res);
            $cost = 0; // 成本
            $return = 0; //收益
            for($i=0;$i<$len;$i++){
                //买负
                if( strpos($bet , '0') !== false ){
                   $cost++;
                   if($res[$i]["r"] == 0 ) $return += $res[$i]["a"];
                };

                //买平
                if( strpos($bet , '1') !== false  ){
                   $cost++;
                   if( $res[$i]["r"] == 1 ) $return += $res[$i]["d"];
                };

                //买胜
                if( strpos($bet , '2') !== false  ){
                   $cost++;
                   if( $res[$i]["r"] == 2 ) $return += $res[$i]["h"];
                };
            }

            $v = round(10000 * ($return-$cost) / $cost ) / 10000;
            logger::success( json_encode(array(
                "count"=>$len,
                "p"=>round(10000 * $len / $count)/10000,
                "r"=>$v
                //"sd": //标准差
            )) , true);
        }
    }
?>