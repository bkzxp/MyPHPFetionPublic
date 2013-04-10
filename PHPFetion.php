<?php
/**
 * PHP飞信发送类
 *
 * @author quanhengzhuang <blog.quanhz.com>
 * @version 1.5.0
 */
class PHPFetion
{

    /**
     * 发送者手机号
     * @var string
     */
    protected $_mobile;

    /**
     * 飞信密码
     * @var string
     */
    protected $_password;

    /**
     * Cookie字符串
     * @var string
     */
    protected $_cookie = '';

    /**
     * Uid缓存
     * @var array
     */
    protected $_uids = array();

    /**
     * csrfToken
     * @var string
     */
    protected $_csrfToten = null;
    
    /**
     * result
     * @var json string
     */
    protected $_result = null;

    /**
     * 构造函数
     * @param string $mobile 手机号(登录者)
     * @param string $password 飞信密码
     */
    public function __construct($mobile, $password)
    {
        if ($mobile === '' || $password === '')
        {
            $this->_result = '{"Staus":"1","Msg":"账号和密码缺失"}';
            return false;
        }
        
        $this->_mobile = $mobile;
        $this->_password = $password;
        $this->_login(); 
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->_logout();
    }
    
    //判断操作的结果
    protected function _jsonJudgeResult($result,$mark)
    {
        $staus = '';
        $msg = '';
	    if(strpos($result, '请输入飞信密码') !== false)
	    {
            $staus = 2;
            $msg = '登陆失败，账号密码错误';
		}
		elseif(strpos($result, '登录超时') !== false)
		{
            $staus = 3;
            $msg = '网络原因造成您登录超时,请尝试重新登录';
		}
		elseif(strpos($result, '发送消息成功') !== false)
		{
		    $staus = 0;
		    $msg = '给他人发送消息成功';
		}
		elseif(strpos($result, '短信发送成功') !== false)
		{
		    $staus = 0;
		    $msg = '给自己发送消息成功';
		}
		else
		{
		    if($mark)//如果已经登陆成功了
		    {
                $staus = 9;
    		    $msg = '发送短信失败';
		    }
		    else
		    {
                $staus = 0;
                $msg = '登陆成功';
		    }
		}
		$this->_result = '{"Staus":"'.$staus.'","Msg":"'.$msg.'"}';
    }

    /**
     * 登录
     * @return string
     */
    protected function _login()
    {
        $uri = '/huc/user/space/login.do?m=submit&fr=space';
        $data = 'mobilenum='.$this->_mobile.'&password='.urlencode($this->_password);
        
        $result = $this->_postWithCookie($uri, $data);

        //解析Cookie
        preg_match_all('/.*?\r\nSet-Cookie: (.*?);.*?/si', $result, $matches);
        if (isset($matches[1]))
        {
            $this->_cookie = implode('; ', $matches[1]);
        }
        
        $result = $this->_postWithCookie('/im/login/cklogin.action', '');
        $this->_jsonJudgeResult($result,0);
    }

    /**
     * 获取飞信ID
     * @param string $mobile 手机号
     * @return string
     */
    protected function _getUid($mobile)
    {
        if(empty($this->_uids[$mobile]))
        {
            $uri = '/im/index/searchOtherInfoList.action';
            $data = 'searchText='.$mobile;
            
            $result = $this->_postWithCookie($uri, $data);
            
            //匹配
            preg_match('/toinputMsg\.action\?touserid=(\d+)/si', $result, $matches);

            $this->_uids[$mobile] = isset($matches[1]) ? $matches[1] : '';
        }
        
        return $this->_uids[$mobile];
    }

    /**
     * 获取csrfToken，给好友发飞信时需要这个字段
     * @param string $uid 飞信ID
     * @return string
     */
    protected function _getCsrfToken($uid)
    {
        if ($this->_csrfToten === null)
        {
            $uri = '/im/chat/toinputMsg.action?touserid='.$uid;
            
            $result = $this->_postWithCookie($uri, '');
            
            preg_match('/name="csrfToken".*?value="(.*?)"/', $result, $matches);

            $this->_csrfToten = isset($matches[1]) ? $matches[1] : '';
        }

        return $this->_csrfToten;
    }

    /**
     * 向好友发送飞信
     * @param string $uid 飞信ID
     * @param string $message 短信内容
     * @return string
     */
    protected function _toUid($uid, $message)
    {
        $uri = '/im/chat/sendMsg.action?touserid='.$uid;
        $csrfToken = $this->_getCsrfToken($uid);
        $data = 'msg='.urlencode($message).'&csrfToken='.$csrfToken;
        
        $result = $this->_postWithCookie($uri, $data);
        $this->_jsonJudgeResult($result,1);
    }

    /**
     * 给自己发飞信
     * @param string $message
     * @return string
     */
    protected function _toMyself($message)
    {
        $uri = '/im/user/sendMsgToMyselfs.action';
        $result = $this->_postWithCookie($uri, 'msg='.urlencode($message));
        $this->_jsonJudgeResult($result,1);
    }

    /**
     * 退出飞信
     * @return string
     */
    protected function _logout()
    {
        $uri = '/im/index/logoutsubmit.action';
        $result = $this->_postWithCookie($uri, '');
        
        return $result;
    }

    /**
     * 携带Cookie向f.10086.cn发送POST请求
     * @param string $uri
     * @param string $data
     */
    protected function _postWithCookie($uri, $data)
    {
        $fp = fsockopen('f.10086.cn', 80);
        fputs($fp, "POST $uri HTTP/1.1\r\n");
        fputs($fp, "Host: f.10086.cn\r\n");
        fputs($fp, "Cookie: {$this->_cookie}\r\n");
        fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:14.0) Gecko/20100101 Firefox/14.0.1\r\n");
        fputs($fp, "Content-Length: ".strlen($data)."\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);

        $result = '';
        while (!feof($fp))
        {
            $result .= fgets($fp);
        }

        fclose($fp);
        return $result;
    }
    
    /**
     * 向指定的手机号发送飞信
     * @param string $mobile 手机号(接收者)
     * @param string $message 短信内容
     * @return string
     */
    public function sendMSG($mobile, $message)
    {
        $result = json_decode($this->_result,true);
        if($result['Staus'])
        {
            return false;
        }
        if(empty($mobile))
        {
            $this->_result = '{"Staus":"4","Msg":"发送的手机号码缺失"}';
            return false; 
        }
        if(empty($message))
        {
            $this->_result = '{"Staus":"5","Msg":"短信内容缺失"}';
            return false;   
        }

        //判断是给自己发还是给好友发
        if ($mobile == $this->_mobile)//【发送失败、成功】
        {
            return $this->_toMyself($message);
        }
        else
        {
            $uid = $this->_getUid($mobile);
            if($uid !== '')
            {
                return $this->_toUid($uid, $message);//【发送成功、发送失败】
            }
            else
            {
                $this->_result = '{"Staus":"8","Msg":"没有获取到对方的飞信账号/对方不是您的好友"}';
            }
        }
        return ;
    }
    
    //获取result结果
    public function GetResultJson()
    {
        return $this->_result;
    }
}