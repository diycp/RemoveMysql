<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);
$config=array(
    'secret'=>'wdkiyttheti7outry',     //填写通信密匙
    'host'=>'localhost',   //数据库服务器地址
    'port'=>3306,         //数据库服务器地址端口，一般是3306
    'username'=>'root',     //数据库用户名
    'password'=>'',     //数据库密码
    'dbname'=>'lifeq_test',         //选择使用哪个数据库
    'charset'=>'utf8',          //编码，默认utf8
);
if(isset($_POST['data'])){
    $removemysql=new removeMysql($_POST['data'],$config);
}

/**
*       远程数据库服务端类
*/
class removeMysql 
{
    private $conn;
    private $ip;

    function __construct($data,$config)
    {
        $array = json_decode(base64_decode($this->decrypt($data , $config['secret'] )) , 1  );
        if(!$array['sql']){
            $Info['errno']= -1 ;//密匙不正确
            $Info['error_mess']='secret is wrong';
            echo json_encode($Info);
            exit();
        }
        $this->ip = $array['ip'] ;
        $this->db_connect($config);
        $optype = $array['optype'] ;
        $this->$optype($array['sql']);
    }

    /*
     * 解密
     * 
     */
     function decrypt($txt, $key = 'fffiidi')
     {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
        $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
        $knum = 0;$i = 0;
        $tlen = strlen($txt);
        while(isset($key{$i})) $knum +=ord($key{$i++});
        $ch1 = $txt{$knum % $tlen};
        $nh1 = strpos($chars,$ch1); 
        $txt = substr_replace($txt,'',$knum % $tlen--,1);
        $ch2 = $txt{$nh1 % $tlen};
        $nh2 = strpos($chars,$ch2);
        $txt = substr_replace($txt,'',$nh1 % $tlen--,1);
        $ch3 = $txt{$nh2 % $tlen};
        $nh3 = strpos($chars,$ch3);
        $txt = substr_replace($txt,'',$nh2 % $tlen--,1);
        $nhnum = $nh1 + $nh2 + $nh3;
        $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum % 8,$knum % 8 + 16);
        $tmp = '';
        $j=0; $k = 0;
        $tlen = strlen($txt);
        $klen = strlen($mdKey);
        for ($i=0; $i<$tlen; $i++) {
            $k = $k == $klen ? 0 : $k;
            $j = strpos($chars,$txt{$i})-$nhnum - ord($mdKey{$k++});
            while ($j<0) $j+=64;
            $tmp .= $chars{$j};
        }
        $tmp = str_replace(array('-','_','.'),array('+','/','='),$tmp);
        return trim(base64_decode($tmp));
    }

    /**
     *  链接数据库
     */
    function db_connect($c){
        $server = $c['host'] . ':' . $c['port'];
        $this->conn = mysql_connect($server, $c['username'], $c['password'], true) or die('connect db error');
        mysql_select_db($c['dbname'], $this->conn) or die('select db error');
        if($c['charset']){
            mysql_query("set names " . $c['charset'], $this->conn);
        }
    }

    public function query($sql){

        $result = mysql_query($sql, $this->conn);
        return $result;
    }

    /**
     *  
     */
    function json_echo($Info){
        $Info['errno']=mysql_errno();
        $Info['error_mess']=mysql_error();
        header('Content-Type:text/html; charset=utf-8');
        echo json_encode($Info);
        exit();
    }

    /**
     *  只执行sql语句，不返还执行后数据，只返回执行状态
     */
    public function query_sql($sql){
        $result = $this->query($sql);
        $Info=array();
        $this->json_echo($Info);
    }

    /**
     * 执行 SQL 语句, 返回结果的第一条记录
     */
    public function get($sql){
        $result = $this->query($sql);
        if($row = mysql_fetch_array($result,MYSQL_ASSOC)){
            $Info['row'] = $row;
        }
        $this->json_echo($Info);
    }

    /**
     * 执行 SQL 语句, 返回结果的多条记录
     */
    public function gets($sql){
        $result = $this->query($sql);
        $rows=array();
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
            $rows[] = $row;
        }
        if($rows)$Info['rows']=$rows;
        $this->json_echo($Info);
    }

    /**
     * 执行一条带有结果集计数的 count SQL 语句, 并返该计数.
     */
    public function count($sql){
        $result = $this->query($sql);
        if($row = mysql_fetch_array($result)){
            $Info['count'] = (int)$row[0];;
        }
        $this->json_echo($Info);
    }

    /**
     * 保存一条记录, 返回新插入的id(若id存在的话).
     */
    public function save($sql){
        $result = $this->query($sql);
        $Info['last_insert_id'] =mysql_insert_id();
        $this->json_echo($Info);
    }

    /**
     *  更新
     */
    public function update($sql){
        $result = $this->query($sql);
        $Info=array();
        $this->json_echo($Info);
    }

    /**
     *  删除
     */
    public function remove($sql){
        $result = $this->query($sql);
        $Info=array();
        $this->json_echo($Info);
    }

};

?>