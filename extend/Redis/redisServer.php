<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/11
 * Time: 17:36
 */

namespace Redis;

class redisServer
{
    protected $options;

    protected static $handler = null;
    static private $_instance = null;

    static public function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new redisServer();
        }
        return self::$_instance;
    }

    private function __construct($options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');
        }
        $this->options = config('auth_' . check_env() . '.REDIS');
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $func          = $this->options['persistent'] ? 'pconnect' : 'connect';     //判断是否长连接
        self::$handler = new \Redis;
        self::$handler->$func($this->options['host'], $this->options['port'], $this->options['timeout']);

        if ('' != $this->options['password']) {
            self::$handler->auth($this->options['password']);
        }
        if (0 != $this->options['select']) {
            self::$handler->select($this->options['select']);
        }
    }

    //私有的克隆方法
    private function __clone()
    {

    }

    /**
     * 写入缓存
     * @param $key
     * @param $value
     * @param int $expire
     * @return bool
     */
    public static function set($key, $value, $expire = 0)
    {
        if ($expire == 0) {
            $set = self::$handler->set($key, $value);
        } else {
            $set = self::$handler->setex($key, $expire, $value);
        }
        return $set;
    }

    /**
     * 读取缓存
     * @param string $key 键值
     * @return mixed
     */
    public static function get($key)
    {
        $fun = is_array($key) ? 'Mget' : 'get';
        return self::$handler->{$fun}($key);
    }

    /**
     * 删除键
     * @param $key
     * @return int
     */
    public static function delete($key)
    {
        return self::$handler->delete($key);
    }

    /**
     * 判断是否存在
     * @param $key
     * @return bool
     */
    public static function exists($key)
    {
        return self::$handler->exists($key);
    }

    //------------- 队列

    /**
     * 获取值长度
     * @param string $key
     * @return int
     */
    public static function LLen($key)
    {
        return self::$handler->lLen($key);
    }

    /**
     * 插入到列表头部
     * @param $key
     * @param $value
     * @param null $value2
     * @param null $valueN
     * @return bool|int
     */
    public static function LPush($key, $value, $value2 = null, $valueN = null)
    {
        return self::$handler->lPush($key, $value, $value2, $valueN);
    }

    /**
     * 移出并获取列表的第一个元素
     * @param string $key
     * @return string
     */
    public static function LPop($key)
    {
        return self::$handler->lPop($key);
    }

    /**
     * 插入到列表尾部
     * @param $key
     * @param $value
     * @param null $value2
     * @param null $valueN
     * @return bool|int
     */
    public static function RPush($key, $value, $value2 = null, $valueN = null)
    {

        return self::$handler->rPush($key, $value, $value2, $valueN);
    }

    /**
     * 移出并获取列表的最后一个元素
     * @param $key
     * @return string
     */
    public static function RPop($key)
    {

        return self::$handler->rpop($key);
    }

}