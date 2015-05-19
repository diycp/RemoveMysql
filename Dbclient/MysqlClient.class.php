<?php
/**
 * MySQL 数据库操作工具类, 方便数据库操作.
 */
class Mysql{

    private $url; //远程地址
    private $secret; //通信密匙
    private $optype; //操作类型
    private $ip; //客户端ip
    public $errno; //mysql错误代码
    public $error_mess; //mysql错误信息
 
    /*构造函数*/
    public function __construct($url,$secret) {
        $this->url = $url ? $url : 'http://blog.star7th.com';
        $this->secret = $secret ? $secret : '123456';
        $this->ip = $this->getip();
    }
    /**
     *   post数据
     */
    private function post($post_data){
        $post_data['optype'] = $this->optype;
        $post_data['ip'] = $this->ip;
        //var_export($post_data);
        $post_data2['data'] = $this->encrypt(base64_encode(json_encode($post_data)), $this->secret);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_POST, 1 );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data2);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        //var_dump($response);
        $result = json_decode($response,true);
        if (!$result) {
            $result['error_mess'] = $response ;
            $result['errno']  = -1 ;
        }
        $errno = curl_errno($curl);
        return $errno?$errno:$result;
        
        
    }

    /**
     *  远程执行 mysql_query ，返回结果为数组。
     */
    public function query($sql){
           return $this->post(array('sql' =>$sql));
    }

    /**
     *  只执行sql语句，不返还数据，只返回布尔值
     */
    public function query_sql($sql){
        $this->optype='query_sql';
        $result = $this->query($sql);
        $this->errno=$result['errno'];  //errno被设置
        if($result['errno']==0){
            return true;
        }else{
            $this->error_mess=$result['error_mess'];  //error_mess被设置
            return false;
        }
    }

    /**
     * 执行 SQL 语句, 以数组的形式返回结果的第一条记录
     */
    public function get($sql){
        $this->optype='get';
        $result = $this->query($sql);
        $this->errno=$result['errno'];  //errno被设置
        if($result['errno']==0){
            return $result['row'];
        }else{
            $this->error_mess=$result['error_mess'];  //error_mess被设置
            return false;
        }
    }

    /**
     * 执行 SQL 语句, 以数组的形式返回结果的多条记录
     */
    public function gets($sql){
        $this->optype='gets';
        $result = $this->query($sql);
        $this->errno=$result['errno'];  //errno被设置
        if($result['errno']==0){
            return $result['rows'];
        }else{
            $this->error_mess=$result['error_mess'];  //error_mess被设置
            return false;
        }
    }
    /**
     * 执行一条带有结果集计数的 count SQL 语句, 并返该计数.
     */
    public function count($sql){
         $this->optype='count';
        $result = $this->query($sql);
        $this->errno=$result['errno'];  //errno被设置
        if($result['errno']==0){
            return $result['count'];
        }else{
            $this->error_mess=$result['error_mess'];  //error_mess被设置
            return false;
        }
    }


    /**
     * 保存一条记录, 返回新插入的id(若id存在的话).
     * @param array $row
     */
    public function save($table, $row){
        $this->optype='save';
        $sqlA = '';
        foreach($row as $k=>$v){
            $sqlA .= "`$k` = '$v',";
        }

        $sqlA = substr($sqlA, 0, strlen($sqlA)-1);
        $sql  = "insert into `{$table}` set $sqlA";
        $result=$this->query($sql);
        $this->errno=$result['errno'];  //errno被设置
        if($result['errno']==0){
            return $result['last_insert_id'];
            return true;//防止这个表的结构中没有id的存在，还是返回个true
        }else{
            $this->error_mess=$result['error_mess'];  //error_mess被设置
            return false;
        }
    }

    /**
     * 更新$row[value]所指定的记录.，例如$row[value]是一个id值
     * @param array $row 要更新的记录, 键名为$field的数组项的值指示了所要更新的记录.
     * @return int 影响的行数.
     * @param string $field 字段名, 默认为'id'.
     */
    public function update($table, $row, $field='id'){
        $this->optype='update';
        $sqlA = '';
        foreach($row as $k=>$v){
            $sqlA .= "`$k` = '$v',";
        }

        $sqlA = substr($sqlA, 0, strlen($sqlA)-1);
        if(is_object($row)){
            $value = $row->{$field};
        }else if(is_array($row)){
            $value = $row[$field];
        }
        $sql  = "update `{$table}` set $sqlA where `{$field}`='$value'";
        $result= $this->query($sql);
        $this->errno=$result['errno'];  //errno被设置
        if($result['errno']==0){
            return true;
        }else{
            $this->error_mess=$result['error_mess'];  //error_mess被设置
            return false;
        }
    }

    /**
     * 删除一条记录.
     * @param  $value 要删除的记录的键值，例如id值
     * @param string $field 字段名, 默认为'id'.
     */
    public function remove($table, $value, $field='id'){
        $this->optype='remove';
        $sql  = "delete from `{$table}` where `{$field}`='{$value}'";
        $result= $this->query($sql);
        $this->errno=$result['errno'];  //errno被设置
        if($result['errno']==0){
            return true;
        }else{
            $this->error_mess=$result['error_mess'];  //error_mess被设置
            return false;
        }
    }

    /**
    * 获取客户端 ip 地址
    * @access public
    * @return string
    */
    public function getip() {
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
            if($realip == '127.0.0.1' && isset($_SERVER["HTTP_X_REAL_IP"])) {
                $realip = $_SERVER["HTTP_X_REAL_IP"];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
            if($realip == '127.0.0.1' && getenv('HTTP_X_REAL_IP')) {
                $realip = getenv('HTTP_X_REAL_IP');
            }
        }
        return $realip;
    }

     /*
     * 加密，可逆
     * 可接受任何字符
     * 安全度非常高
     */
    public function encrypt($txt, $key = 'fffiidi')
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
        $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
        $nh1 = rand(0,64);
        $nh2 = rand(0,64);
        $nh3 = rand(0,64);
        $ch1 = $chars{$nh1};
        $ch2 = $chars{$nh2};
        $ch3 = $chars{$nh3};
        $nhnum = $nh1 + $nh2 + $nh3;
        $knum = 0;$i = 0;
        while(isset($key{$i})) $knum +=ord($key{$i++});
        $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum%8,$knum%8 + 16);
        $txt = base64_encode($txt);
        $txt = str_replace(array('+','/','='),array('-','_','.'),$txt);
        $tmp = '';
        $j=0;$k = 0;
        $tlen = strlen($txt);
        $klen = strlen($mdKey);
        for ($i=0; $i<$tlen; $i++) {
            $k = $k == $klen ? 0 : $k;
            $j = ($nhnum+strpos($chars,$txt{$i})+ord($mdKey{$k++}))%64;
            $tmp .= $chars{$j};
        }
        $tmplen = strlen($tmp);
        $tmp = substr_replace($tmp,$ch3,$nh2 % ++$tmplen,0);
        $tmp = substr_replace($tmp,$ch2,$nh1 % ++$tmplen,0);
        $tmp = substr_replace($tmp,$ch1,$knum % ++$tmplen,0);
        return $tmp;
    }
}
?>