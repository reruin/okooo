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
            $order = $_REQUEST["order"];
            $res = $this->db->select("{$where}","{$order},result as r");
            logger::success( json_encode($res) , true);
        }

        public function dostatistics()
        {
            $where = $_REQUEST["where"];
            $order = $_REQUEST["order"];
            $res = $this->db->select("{$where} group by {$order} order by v asc","count(*) as c,{$order} as v,result as r");
            logger::success( json_encode($res) , true);
        }


        public function docalc()
        {
            $where = $_REQUEST["where"];
            $select = $_REQUEST["select"];//下注公司
            $bet = $_REQUEST["bet"]; //下注类型
            $res = $this->db->select($where,"{$select}_home as h,{$select}_draw as d,{$select}_away as a,result as r");

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
                count=>$len,
                r=>$v
            )) , true);
        }
    }
?>