<?php

class D
{

    private $link; //数据库连接
    private $table; //表名
    private $prefix; //表前缀
    private $db_config;

    function __construct($table , $cfg = 'config.php')
    {
        if (is_array($cfg)) {
            $this->db_config = $cfg;
        } else {
            $this->db_config = require($cfg);
        }

        $this->conn();
        $this->table = $this->prefix . $table;
    }

    /**
     * 连接数据库
     */
    private function conn()
    {
        $db_config = $this->db_config["DB_CONFIG"];
        $host = $db_config["DB_HOST"];
        $port = $db_config['DB_PORT'];
        $user = $db_config["DB_USER"];
        $pwd = $db_config["DB_PWD"];
        $db_name = $db_config["DB_NAME"];
        $db_encode = $db_config["DB_ENCODE"];
        $this->prefix = $db_config["DB_PREFIX"];

        try{
            $con = "mysql:host=$host;port=$port;dbname=$db_name";
            $this->link = new PDO($con,$user,$pwd,array(PDO::MYSQL_ATTR_INIT_COMMAND=>"set names '$db_encode'"));
        }catch (PDOException $e){
            echo "数据库连接出错:".$e;
        }

    }

    public function exec($sqlStr = "")
    {
        return $this->link->exec($sqlStr);
    }
    /**
     * 数据查询
     * 参数:sql条件 查询字段 使用的sql函数名
     * @param string $where
     * @param string $field
     * @param string $fun
     * @return array
     * 返回值:结果集 或 结果(出错返回空字符串)
     */
    public function select($where = '1', $field = "*", $fun = '' , $and = "")
    {
        $rarr = array();
        if (empty($fun)) {
            $sqlStr = "select $field from $this->table where $where ".$and;
             $rt =  $this->link->query($sqlStr);
            while ($rt && $arr = $rt->fetch(PDO::FETCH_ASSOC)) {
                array_push($rarr, $arr);
            }
        } else {
            $sqlStr = "select $fun($field) as rt from $this->table where $where";
            $rt = $this->link->query($sqlStr);
            $v = $rt->fetch();
            $rarr = $v["rt"];
        }
        return $rarr;
    }

    /**
     * 数据更新
     * 参数:sql条件 要更新的数据(字符串 或 关联数组)
     * @param $where
     * @param $data
     * @return bool
     * 返回值:语句执行成功或失败,执行成功并不意味着对数据库做出了影响
     */
    public function update($where, $data, $tostring = false)
    {
        $ddata = '';
        if (is_array($data)) {
            while (list($k, $v) = each($data)) {
                if (empty($ddata)) {
                    $ddata = "$k='".addslashes($v)."'";

                } else {
                    $ddata .= ",$k='".addslashes($v)."'";
                }
            }
        } else {
            $ddata = $data;
        }
		//echo($ddata);
        $sqlStr = "update $this->table set $ddata where $where";
        if( $tostring ) return $sqlStr;
        else return $this->link->exec($sqlStr);
    }

    /**
     * 数据添加
     * 参数:数据(数组 或 关联数组 或 字符串)
     * @param $data
     * @return int
     * 返回值:插入的数据的ID 或者 0
     */
    public function insert($data , $tostring = false , $ignore = "")
    {
        $field = '';
        $idata = '';
		//echo( array_keys($data) !== range(0, count($data) - 1) ? 1 : 0);
        if (is_array($data) && array_keys($data) !== range(0, count($data) - 1)) {
            //关联数组
            while (list($k, $v) = each($data)) {
                if (empty($field)) {
                    $field = "$k";
                    $idata = "'$v'";
                } else {
                    $field .= ",$k";
                    $idata .= ",'$v'";
                }
            }
            $sqlStr = "insert into $this->table($field) values ($idata)";


        } else {
            //非关联数组 或字符串
            if (is_array($data)) {
                while (list($k, $v) = each($data)) {
                    if (empty($idata)) {
                        $idata = "'$v'";
                    } else {
                        $idata .= ",'$v'";
                    }
                }

            } else {
                //为字符串
                $idata = $data;
            }
            $sqlStr = "insert into $this->table values ($idata)";

        }

        if($ignore!=""){
            $sqlStr = $sqlStr . $ignore;
        }

        if($tostring) {  return $sqlStr; }


		return $this->link->exec($sqlStr);

		if($this->link->exec($sqlStr))
		{
		 return $this->link->lastinsertid();
		}
        return 0;
    }

    /**
     * 数据删除
     * 参数:sql条件
     * @param $where
     * @return int
     */
    public function delete($where)
    {
        $sqlStr = "delete from $this->table where $where";
        return $this->link->exec($sqlStr);
    }

    /**
     * 关闭MySQL连接
     * @return bool
     */
//    public function close()
//    {
//        return mysql_close($this->link);
//    }

}
?>