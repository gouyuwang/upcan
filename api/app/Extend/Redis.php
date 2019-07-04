<?php

namespace App\Extend;


use Mockery\Exception;

class Redis
{
    protected $redis;

    protected $prefix = 'ekesi_';

    function __construct()
    {
        try {
            $this->redis = new   \Redis();
            $this->redis->connect(ENV('REDIS_HOST'), ENV('REDIS_PORT'));
            $this->redis->auth(ENV('REDIS_AUTH'));
        } catch (\RedisException $e) {
            exit(json_encode(['code' => 4040, 'msg' => $e->getMessage()]));
        } catch (Exception $e) {
            exit(json_encode(['code' => 4040, 'msg' => $e->getMessage()]));
        }
    }


    /**
     * TODO 根据key值获取缓存数据
     * @param $key
     * @return mixed
     */
    protected function get($key)
    {
        $rst = false;
        $key = $this->prefix . $key;
        if ($this->redis) {
            $rst = json_decode($this->redis->get($key), true);
        }
        return $rst;
    }


    /**
     * TODO 缓存的当前数据  缓存数组默认保存一天
     * @param $key
     * @param $data
     * @param int $expire
     * @return bool
     */
    public function set($key, $data, $expire = 86400)
    {
        $rst = false;
        $key = $this->prefix.$key;
        if ($this->redis) {
            $rst = $this->redis->set($key, json_encode($data));
            $rst && $rst = $this->redis->expire($key, $expire);
        }
        return $rst;
    }
}
