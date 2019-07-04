<?php
// +----------------------------------------------------------------------
// |   Created by Phpstorm
// +----------------------------------------------------------------------
// | File:  BaseModel.php
// +----------------------------------------------------------------------
// | Date: 2019/5/28 9:30
// +----------------------------------------------------------------------
// | Author: ywg
// +----------------------------------------------------------------------
// | DESC:  基础模型
// +----------------------------------------------------------------------


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Validator;

class BaseModel extends Model
{
    // 默认分页参数
    public $curPage = 1;
    public $pageSize = 10;

    // 审核版本切换
    public static $isAudit = false;
    // 设置主键类型
    protected $keyType = "string";

    // 默认数据库类型
    protected $connection = 'mysql';


    /**
     * BaseModel constructor.
     */
    public function __construct()
    {
        self::$isAudit && $this->setConnection("mysql_audit");
        parent::__construct();
    }


    /**
     * 列表查询
     * @param array $params 查询参数,详见 @method builder($params, $page)
     * @param bool $page 是否分页
     * @return array
     */
    public function selectInfo(array $params = [], $page = true)
    {
        try {
            $list = $this->builder($params, $page)->get()->toArray();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        return $list;
    }

    /**
     * 查询单条
     * @param array $params
     * @return array
     */
    public function findInfo(array $params = [])
    {
        $ret = null;
        try {
            $ret = $this->builder($params, true)->first();
            if (!empty($ret)) {
                $ret = $ret->toArray();
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        return $ret;
    }

    /**
     * 查询总数
     * @param array $params
     * @return int
     */
    public function total(array $params = [])
    {
        $num = 0;
        try {
            $num = $this->builder($params, false)->toBase()->getCountForPagination();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        return $num;
    }

    /**
     * 新增
     * @param array $arr
     * @return array|bool
     */
    public function addInfo($arr = [])
    {
        $arr['create_time'] = date('Y-m-d H:i:s');
        //过滤数据
        $arr = $this->filter(['type' => 'add', 'data' => $arr]);
        //验证数据
        $rules = isset($this->rules['add']) ? $this->rules['add'] : [];
        $messages = isset($this->messages) ? $this->messages : [];
        $vResult = $this->validator($arr, $rules, $messages);
        if (is_array($vResult)) return $vResult;
        //新增
        $id = $this->insertGetId($arr);//自动返回新增id
        return $id;
    }


    /**
     * 修改
     * @param array $arr
     * [
     *      condition...
     *     'data'=>[]
     * ]
     * @return array|bool|int
     */
    public function editInfo($arr = [])
    {
        // 过滤数据
        $arr['data'] = $this->filter(['type' => 'edit', 'data' => $arr['data']]);
        // 验证数据
        $rules = isset($this->rules['edit']) ? $this->rules['edit'] : [];
        $messages = isset($this->messages) ? $this->messages : [];
        $vResult = $this->validator($arr['data'], $rules, $messages);
        if (is_array($vResult)) return $vResult;
        // 如果字段被全部屏蔽，说明传入的字段存在问题
        if (empty($arr['data'])) return false;
        // 修改
        $res = $this->builder($arr, false)->update($arr['data']);
        return $res;
    }


    /**
     * 删除
     * @param array $arr
     * @param bool $real 是否真实删除
     * @return array|bool|int
     */
    public function deleInfo($arr = [], $real = false)
    {
        if (!$real) {
            $arr['data'] = ['dele' => 2];
            $res = $this->editInfo($arr);
        } else {
            $res = $this->builder($arr)->delete();
        }
        return $res;
    }


    /**
     * 原生查询
     * @param string $sql
     * @return mixed
     */
    public function nativQuery($sql = '')
    {
        $object = \DB::select($sql);
        return json_decode(json_encode($object), true);
    }

    /**
     * 某个字段加几
     * @param array $arr = ['where' =>['a' =>'b','c' =>'d'],'setInc' =>'e']
     * @param int $num 量级
     * @return mixed
     */
    public function setInc($arr = [], $num = 1)
    {
        $arr['num'] = isset($arr['num']) ? $arr['num'] : $num;
        $res = $this->where($arr['where'])->increment($arr['setInc'], $arr['num']);
        return $res;
    }

    /**
     * 某个字段减几
     * @param array $arr = = ['where' =>['a' =>'b','c' =>'d'],'setDec' =>'e']
     * @param int $num 量级
     * @return mixed
     */
    public function setDec($arr = [], $num = 1)
    {
        $arr['num'] = isset($arr['num']) ? $arr['num'] : $num;
        $res = $this->where($arr['where'])->decrement($arr['setDec'], $arr['num']);
        return $res;
    }

    /**
     * 过滤数据,用于删除掉不希望接收到的数据
     * @param array $filter [
     *      'type'  => '', // 规则类型
     *      'data'  => []  // 要过滤的字段
     * ]
     * @return array|mixed
     */
    public function filter($filter = [])
    {
        // 规则类型
        $filterType = $filter['type'];
        // 过滤的字段集合
        $fieldData = $filter['data'];
        // 允许的字段
        $allow_fields = isset($this->filter[$filterType]) ? $this->filter[$filterType] : [];
        // 如果没有定义该字段的过滤器，则默认全部允许
        if (empty($allow_fields)) {
            return $fieldData;
        }
        $data = [];
        // 过滤掉不允许出现的字段
        foreach ($fieldData as $k => $v) {
            if (in_array($k, $allow_fields))
                $data[$k] = $v;
        };
        return $data;
    }


    /**
     * 内部数据验证
     * @param array $data 验证的数据
     * @param array $rules 规则
     * @param array $messages 提示消息
     * @return array|bool     验证通过返回true，否则返回错误提示信息
     */
    public function validator($data = [], $rules = [], $messages = [])
    {
        return self::staticValidator($data, $rules, $messages);
    }

    /**
     * 静态数据验证
     * @param array $data 验证的数据
     * @param array $rules 规则
     * @param array $messages 提示消息
     * @return array|bool     验证通过返回true，否则返回错误提示信息
     */
    public static function staticValidator($data = [], $rules = [], $messages = [])
    {
        // 验证
        if (!empty($rules)) {
            if (!empty($messages)) {
                $validator = Validator::make($data, $rules, $messages);
            } else {
                $validator = Validator::make($data, $rules);
            }
        }
        // 如果没有过滤器则忽略验证行为
        if (isset($validator) && $validator->fails()) {
            $messageObj = $validator->errors();
            $tips = $messageObj->all();
            return ['code' => 4000, 'msg' => $tips[0]];
        }
        return True;
    }


    /**
     * 构造查询器Builder对象
     * @param array $arr = [
     *
     *      // 查询字段: 支持数组,不支持字符串
     *      'field' => ['field1', 'field2'],
     *
     *      // 联合查询
     *      'union'=>[
     *          'table1'=>[
     *               // 支持所有条件
     *           ],
     *          'table2'=>[
     *               // 支持所有条件
     *           ],
     *       ],
     *      'unionAll'=>[
     *          'table1'=>[
     *               // 支持所有条件
     *           ],
     *          'table2'=>[
     *               // 支持所有条件
     *           ],
     *       ],
     *
     *      // 原生查询字段，用于支持 MySQL 等内置函数, 建议少用
     *      'raw' => ['field1', 'field2'],
     *
     *      // where条件
     *      'where' => [
     *          'field1' => 'val1',
     *          'field2' => 'val2'
     *      ],
     *
     *      // 单表达式查询
     *      'exp' => ['field1', '>=', 'val'],
     *
     *      // 多表达式查询
     *      'expMore' => [
     *          ['field1', '>=', 'val'],
     *      ],
     *
     *      // in查询方式一
     *      // 'in' => ['field1', ['a', 'b', 'c'],'field2', ['a', 'b', 'c']],
     *      // in查询方式二
     *      'in' => [
     *          'field1' => ['a', 'b', 'c'],
     *          'field2' => ['a', 'b', 'c']
     *      ],
     *
     *
     *      // notin查询方式一
     *      // 'notin' => ['field1', ['a', 'b', 'c']],
     *      // notin查询方式二
     *      'notin' => [
     *          'field1' => ['a', 'b', 'c'],
     *          'field2' => ['a', 'b', 'c']
     *      ],
     *
     *      // 或 查询, 此处是 | 关系 : a or (bcd)
     *      'orWhere' => ['field1' => 'b', 'field2' => 'c'],
     *
     *      //
     *      'join' => [
     *          ['table1', ['tabl1.field1', '=', 'table2.field1']],
     *          ['table2', ['tabl1.field2', '=', 'table2.field2']]
     *      ],
     *
     *      // 左联查询
     *      'leftJoin' => [
     *          ['table1', ['tabl1.field1', '=', 'table2.field1']],
     *          ['table2', ['tabl1.field2', '=', 'table2.field2']]
     *      ],
     *
     *      'leftJoinMore' => [
     *          ['table1', ['tabl1.field1', '=', 'table2.field1'], ['tabl1.field2', '=', 'table2.field2']],
     *      ],
     *
     *      // 右联查询
     *      'rightJoin' => [
     *          ['table1', ['tabl1.field1', '=', 'table2.field1']],
     *          ['table2', ['tabl1.field2', '=', 'table2.field2']]
     *      ],
     *
     *      // whereBetween查询
     *      'whereBetween' => [
     *          ['field1', ['v1', 'v2']],
     *          ['field2', ['v1', 'v2']]
     *      ],
     *
     *      // whereNotBetween查询
     *      'whereNotBetween' => [
     *          ['field1', ['v1', 'v2']],
     *          ['field3', ['v1', 'v2']]
     *      ],
     *
     *      //  like查询方式
     *      // 'like' => ['like', ['field1', 'val']],
     *      // like查询方式二
     *      'like' => [
     *          ['field1', 'field2', 'field3'],
     *          'val',
     *      ],
     *
     *      // orWhere查询, 此处是 & 关系 a & (bcd)
     *      'whereOr' => [
     *          'where' => ['field1' => 'val', 'field2' => 'val'],
     *          'exp' => ['field1', '>', 'xx'],
     *          'expMore' => [
     *              ['field1', '>=', 'xx']
     *          ],
     *          'in' => ['field1', ['a', 'b', 'c']]
     *      ],
     *
     *      // 分页查询，查询第二个参数为false的时候这个条件将无效
     *      'limit' => [1, 2],
     *
     *      // 单排序规则
     *      'orderBy' => ['field1', 'desc'],
     *
     *      // 多排序规则
     *      'order' => [
     *          ['field1', 'aes'],
     *          ['field2', 'aes'],
     *          ['field3', 'desc']
     *      ],
     *      // 分组
     *      'groupBy' => 'field1',
     *
     *      // 调试模式
     *      'tosql' => True
     * ];
     * @param bool $page shifou
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function builder($arr = [], $page = true)
    {
        /**
         * 参数解析
         */
        $field = isset($arr['field']) ? $arr['field'] : '';
        $raw = isset($arr['raw']) ? $arr['raw'] : '';
        $where = isset($arr['where']) ? $arr['where'] : false;
        $join = isset($arr['join']) ? count($arr['join']) : false;
        $joinMore = isset($arr['joinMore']) ? count($arr['joinMore']) : false;
        $leftJoin = isset($arr['leftJoin']) ? count($arr['leftJoin']) : false;
        $leftJoinMore = isset($arr['leftJoinMore']) ? count($arr['leftJoinMore']) : false;
        $rightJoin = isset($arr['rightJoin']) ? count($arr['rightJoin']) : false;
        $rightJoinMore = isset($arr['rightJoinMore']) ? count($arr['rightJoinMore']) : false;
        $orWhere = isset($arr['orWhere']) ? $arr['orWhere'] : false;
        $in = isset($arr['in']) ? $arr['in'] : false;
        $notin = isset($arr['notin']) ? $arr['notin'] : false;
        $exp = isset($arr['exp']) ? $arr['exp'] : false;
        $expMore = isset($arr['expMore']) ? count($arr['expMore']) : false;
        $take = isset($arr['limit']) ? $arr['limit'][1] : $this->pageSize;
        $skip = isset($arr['limit']) ? $arr['limit'][0] : 0;
        $orderBy = isset($arr['orderBy']) ? $arr['orderBy'] : false;
        $order = isset($arr['order']) ? count($arr['order']) : false;
        $groupBy = isset($arr['groupBy']) ? $arr['groupBy'] : false;
        $whereBetween = isset($arr['whereBetween']) ? $arr['whereBetween'] : false;
        $whereNotBetween = isset($arr['whereNotBetween']) ? $arr['whereNotBetween'] : false;
        $like = isset($arr['like']) ? $arr['like'] : false;
        $whereOr = isset($arr['whereOr']) ? $arr['whereOr'] : false;
        $union = isset($arr['union']) ? $arr['union'] : false;
        $unionAll = isset($arr['unionAll']) ? $arr['unionAll'] : false;
        $debug = isset($arr['tosql']) ? (boolean)$arr['tosql'] : false;

        /**
         * sql解析
         */

        // Get Builder Object
        $builder = $this->newQuery();

        // union
        if ($union || $unionAll) {

            $newBuilder = null;

            // union
            if ($union) {
                $unionArr = $arr['union'];
                foreach ($unionArr as $t => $v) {
                    $_builder = $this->table2model($t)->builder($v, false);
                    if ($newBuilder) {
                        $newBuilder->union($_builder);
                    } else {
                        $newBuilder = $_builder;
                    }
                }
            }

            // union all
            if ($unionAll) {
                $unionArr = $arr['unionAll'];
                foreach ($unionArr as $t => $v) {
                    $_builder = $this->table2model($t)->builder($v, false);
                    if ($newBuilder) {
                        $newBuilder->unionAll($_builder);
                    } else {
                        $newBuilder = $_builder;
                    }
                }
            }
            $builder->from(\DB::raw("({$newBuilder->toSql()}) as _temp"))->mergeBindings($newBuilder->getQuery());
        }

        // where
        if ($where) $builder->where($where);

        // or
        if ($orWhere) $builder->orWhere($orWhere);

        // innerJoin on a.x=b.x
        if ($join) {
            for ($i = 1; $i <= $join; $i++) {
                $k = $i - 1;
                $builder->join(
                    $arr['join'][$k][0],    // table
                    $arr['join'][$k][1][0], // field
                    $arr['join'][$k][1][1], // op
                    $arr['join'][$k][1][2]  // field
                );
            }
        }

        // leftJoin  on a.x = b.x
        if ($leftJoin) {
            for ($i = 1; $i <= $leftJoin; $i++) {
                $k = $i - 1;
                $builder->leftJoin(
                    $arr['leftJoin'][$k][0],
                    $arr['leftJoin'][$k][1][0],
                    $arr['leftJoin'][$k][1][1],
                    $arr['leftJoin'][$k][1][2]
                );
            }
        }

        // rightJoin  on a.x=b.x
        if ($rightJoin) {
            for ($i = 1; $i <= $rightJoin; $i++) {
                $k = $i - 1;
                $builder->rightJoin(
                    $arr['rightJoin'][$k][0],
                    $arr['rightJoin'][$k][1][0],
                    $arr['rightJoin'][$k][1][1],
                    $arr['rightJoin'][$k][1][2]
                );
            }
        }

        // innerJoin  on a.x=b.x and a.y = b.y
        if ($joinMore) {
            for ($i = 1; $i <= $joinMore; $i++) {
                $k = $i - 1;
                $builder->join($arr['joinMore'][$k][0], function ($query) use ($arr, $k) {
                    foreach ($arr['joinMore'][$k] as $item) {
                        if (is_array($item)) {
                            $query = $query->on($item[0], $item[1], $item[2]);
                        }
                    }
                });
            }
        }

        // leftJoin on a.x=b.x and a.y = b.y
        if ($leftJoinMore) {
            for ($i = 1; $i <= $leftJoinMore; $i++) {
                $k = $i - 1;
                $builder->leftJoin($arr['leftJoinMore'][$k][0], function ($query) use ($arr, $k) {
                    foreach ($arr['leftJoinMore'][$k] as $item) {
                        if (is_array($item)) {
                            $query = $query->on($item[0], $item[1], $item[2]);
                        }
                    }
                });
            }
        }

        // rightJoin on a.x=b.x and a.y = b.y
        if ($rightJoinMore) {
            for ($i = 1; $i <= $rightJoinMore; $i++) {
                $k = $i - 1;
                $builder->rightJoin($arr['rightJoinMore'][$k][0], function ($query) use ($arr, $k) {
                    foreach ($arr['rightJoinMore'][$k] as $item) {
                        if (is_array($item)) {
                            $query = $query->on($item[0], $item[1], $item[2]);
                        }
                    }
                });
            }
        }


        // like
        if ($like) {
            $fields = $like[0];
            $keywords = $like[1];
            if (is_array($fields)) {
                $builder->where(function ($builder) use ($fields, $keywords) {
                    foreach ($fields as $val) {
                        $builder->orWhere($val, "like", "%{$keywords}%");
                    }
                });
            } else if (is_array($keywords)) {
                $builder->where(function ($builder) use ($fields, $keywords) {
                    foreach ($keywords as $val) {
                        $builder->orWhere($fields, "like", "%{$val}%");
                    }
                });
            } else {
                $builder->where($fields, 'like', "%{$keywords}%");
            }
        }

        // in
        if ($in) {
            if (is_string(array_keys($in)[0])) {
                foreach ($in as $f => $values) {
                    $values = is_array($values) ? $values : (array)$values;
                    $builder->whereIn($f, $values);
                }
            } else {
                for ($i = 0, $j = count($in) / 2; $i < $j; $i += 2) {
                    $builder->whereIn($in[$i], $in[$i + 1]);
                }
            }
        }

        // not in
        if ($notin) {
            if (is_string(array_keys($notin)[0])) {
                foreach ($notin as $f => $values) {
                    $values = is_array($values) ? $values : (array)$values;
                    $builder->whereNotIn($f, $values);
                }
            } else {
                for ($i = 0, $j = count($notin) / 2; $i < $j; $i += 2) {
                    $builder->whereNotIn($notin[$i], $notin[$i + 1]);
                }
            }
        }

        // expression
        if ($exp) {
            $builder->where($exp[0], $exp[1], $exp[2]);
        }

        // expression more
        if ($expMore) {
            for ($i = 1; $i <= $expMore; $i++) {
                $k = $i - 1;
                $builder->where($arr['expMore'][$k][0], $arr['expMore'][$k][1], $arr['expMore'][$k][2]);
            }
        }
        // between
        if ($whereBetween) {
            foreach ($whereBetween as $item) {
                $builder->whereBetween($item[0], $item[1]);
            }
        }

        // not between
        if ($whereNotBetween) {
            foreach ($whereNotBetween as $item) {
                $builder->whereNotBetween($item[0], $item[1]);
            }
        }

        // raw
        if ($raw) {
            $raw = implode($raw, ",");
            $builder->selectRaw($raw);
        }

        // where or
        if ($whereOr) {
            $builder->where(function ($builder) use ($whereOr) {
                if (isset($whereOr["where"])) {
                    $builder->orWhere($whereOr["where"]);
                }
                if (isset($whereOr["in"])) {
                    $whereOrIn = $whereOr["in"];
                    $builder->orWhere(function ($builder) use ($whereOrIn) {
                        $builder->whereIn($whereOrIn[0], $whereOrIn[1]);
                    });
                }
                if (isset($whereOr["exp"])) {
                    $builder->orWhere($whereOr["exp"][0], $whereOr["exp"][1], $whereOr["exp"][2]);
                }
                if (isset($whereOr["expMore"])) {
                    $whereOrExpMore = $whereOr["expMore"];
                    $builder->orWhere(function ($builder) use ($whereOrExpMore) {
                        for ($j = count($whereOrExpMore), $i = 0; $i < $j; $i++) {
                            $builder->where($whereOrExpMore[$i][0], $whereOrExpMore[$i][1], $whereOrExpMore[$i][2]);
                        }
                    });
                }
            });
        }


        // sort
        if ($orderBy) {
            $builder->orderBy($orderBy[0], $orderBy[1]);
        }

        // sort more
        if ($order) {
            for ($j = 1; $j <= $order; $j++) {
                $ke = $j - 1;
                $builder->orderBy($arr['order'][$ke][0], $arr['order'][$ke][1]);
            }
        }

        // page
        if ($page) {
            $builder->take($take)->skip($skip);
        }

        // group
        if ($groupBy) {
            $builder->groupBy($groupBy);
        }

        // field
        if ($field) {
            $builder->select($field);
        }

        // debug
        if ($debug) {
            die($builder->toSql());
        }

        return $builder;
    }


    /**
     * 将 table 转换生成 model
     * @param $table
     * @return mixed
     */
    protected function table2model($table)
    {
        $model = __NAMESPACE__ . '\\' . implode('', array_map('ucfirst', explode('_', str_replace(ENV('DB_PREFIX'), '', strtolower($table))))) . 'Model';
        return new $model;
    }

}
