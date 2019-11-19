<?php

	$dbhost = '127.0.0.1';
	$dbroot = 'root';
	$dbpass = 'root';

	$conn = mysqli_connect($dbhost, $dbroot, $dbpass);
	if(! $conn )
	{
	    die('连接失败: ' . mysqli_error($conn));
	}
	mysqli_query($conn , "set names utf8");
	mysqli_select_db( $conn, 'crontab_task' );
	$sql = "SELECT `cmd`,`time` FROM yx_crontab"; // 后续where条件 ip = $_SERVER['REMOTE_ADDR']
	$query = mysqli_query($conn, $sql);
	if (!$query) {
		die('读取失败'. mysqli_error($conn));
	}

	$data = mysqli_fetch_all($query, MYSQLI_ASSOC);
	$todo = [];
	foreach ($data as $value) {
		$todo[] = $value['cmd'];
	}

	######################### data format ############################
	$now = $_SERVER['REQUEST_TIME'];
	foreach ( $todo as $cron ) {
            $slices = preg_split("/[\s]+/", $cron, 6);
            if( count($slices) !== 6 ) {
                continue;
            }
            $cmd       = array_pop($slices);
            $cron_time = implode(' ', $slices);
            $next_time = Crontab::parse($cron_time, $now);
            //var_dump(date("Y-m-d H:i", $next_time));exit;

            if ( $next_time !== $now ) {
                continue;
            } else {
                # todo执行命令的脚本文件 ()
                exec($cmd, $result, $status);
                # 根据返回的状态值输出
                if ($status) {
                    echo date('Y-m-d H:i:s', time()).' 命令 --> '.$cmd.' 执行失败'.'['.$i.']'."\n";
                } else {
                    echo date('Y-m-d H:i:s', time()).' 命令 --> '.$cmd.' 执行成功'.'['.$i.']'."\n";
                }
                /*
                    $pid = pcntl_fork();
                    if ($pid == -1) {
                        die('could not fork');
                    } else if ($pid) {
                        // we are the parent
                        pcntl_wait($status, WNOHANG); //Protect against Zombie children
                    } else {
                          // we are the child
                        `$cmd`;
                        exit;
                    }
                */
            }
            $i++;
        }

###
/* https://github.com/jkonieczny/PHP-Crontab */
class Crontab {
   /**
     * Finds next execution time(stamp) parsin crontab syntax,
     * after given starting timestamp (or current time if ommited)
     *
     * @param string $_cron_string:
     *
     * 0 1 2 3 4
     * * * * * *
     * - - - - -
     * | | | | |
     * | | | | +----- day of week (0 - 6) (Sunday=0)
     * | | | +------- month (1 - 12)
     * | | +--------- day of month (1 - 31)
     * | +----------- hour (0 - 23)
     * +------------- min (0 - 59)
     * @param int $_after_timestamp timestamp [default=current timestamp]
     * @return int unix timestamp - next execution time will be greater
     * than given timestamp (defaults to the current timestamp)
     * @throws InvalidArgumentException
     */
    public static function parse ( $_cron_string, $_after_timestamp = null )
    {
        if(!preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i', trim($_cron_string))){
            throw new InvalidArgumentException("Invalid cron string: ".$_cron_string);
        }

        if($_after_timestamp && !is_numeric($_after_timestamp)){
            throw new InvalidArgumentException("\$_after_timestamp must be a valid unix timestamp ($_after_timestamp given)");
        }

        $cron   = preg_split("/[\s]+/i",trim($_cron_string));
        
        $start  = empty($_after_timestamp)?time():$_after_timestamp;

        $date   = array(  
                        'minutes'   =>  self::_parseCronNumbers($cron[0],0,59),
                        'hours'     =>  self::_parseCronNumbers($cron[1],0,23),
                        'dom'       =>  self::_parseCronNumbers($cron[2],1,31),
                        'month'     =>  self::_parseCronNumbers($cron[3],1,12),
                        'dow'       =>  self::_parseCronNumbers($cron[4],0,6)
                );
        
        // limited to time()+366 - no need to check more than 1year ahead
        for( $i=0; $i<=60*60*24*366; $i+=60 )
        {
            if ( 
                in_array(intval(date('j',$start+$i)),$date['dom']) &&
                in_array(intval(date('n',$start+$i)),$date['month']) &&
                in_array(intval(date('w',$start+$i)),$date['dow']) &&
                in_array(intval(date('G',$start+$i)),$date['hours']) &&
                in_array(intval(date('i',$start+$i)),$date['minutes'])
            ) {
                return $start+$i;
            }
        }
        return null;
    }

    
    /**
     * get a single cron style notation and parse it into numeric value
     *
     * @param string $s cron string element
     * @param int $min minimum possible value
     * @param int $max maximum possible value
     * @return int parsed number
     */
    protected static function _parseCronNumbers($s,$min,$max)
    {
        $result = array();

        $v = explode(',',$s);
        foreach($v as $vv){
            $vvv = explode('/',$vv);
            $step = empty($vvv[1])?1:$vvv[1];
            $vvvv = explode('-',$vvv[0]);
            $_min = count($vvvv)==2?$vvvv[0]:($vvv[0]=='*'?$min:$vvv[0]);
            $_max = count($vvvv)==2?$vvvv[1]:($vvv[0]=='*'?$max:$vvv[0]);

            for($i=$_min;$i<=$_max;$i+=$step){
                $result[$i]=intval($i);
            }
        }
        ksort($result);
        return $result;
    }
}
###


	mysqli_close($conn);