<?php
/**
 *  管理逻辑
 */

namespace App\Http\Controllers\Api\V1\Logic;

use App\Models\AdminModel;

class AdminLogic extends BaseLogic
{
    private $adminModel;

    function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    /**
     * TODO 用户登录
     * @param array $data
     * @return array
     */
    public function checkLogin(array $data = [])
    {
        if (empty($data['username'])) {
            $this->_Error(4000, [], '账户名不能空');
        }
        if (empty($data['password'])) {
            $this->_Error(4000, [], '密码不能空');
        }
        $params = [
            'where' => [
                'admin_account' => $data['username'],
                'dele' => 1,
            ],
            'field' => ['admin_id', 'admin_pwd', 'status', 'role_id','admin_nickname']
        ];
        $adminInfo = $this->adminModel->findInfo($params);
        // 账户密码是否正确
        if (empty($adminInfo) || $adminInfo['admin_pwd'] !== sha1($data['password'])) {
            return ['code' => 4000, 'msg' => '用户不存在或密码错误'];
        }
        // 账号是否已经冻结
        if ($adminInfo['status'] == 2) {
            return ['code' => 4000, 'msg' => '该账户已被冻结, 请联系超级管理员！'];
        }
        unset($adminInfo['admin_pwd']);
        // 更新当前用户登录信息
        $params = [
            'where' => [
                'dele' => 1,
                'admin_id' => $adminInfo['admin_id']
            ],
            'data' => [
                'last_login_ip' => $this->get_real_ip(),
                'last_login_time' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->adminModel->editInfo($params);

        // 生成token
        $token = $this->getToken([
            '_admin_id' => $adminInfo['admin_id'],
            '_role_id' => $adminInfo['role_id'],
        ]);
        return $this->_Success(array_merge($adminInfo, ['token' => $token]));
    }

    /**
     * TODO 获取管理员列表
     * @param array $data
     * @return array
     */
    public function getAdminList(array $data = [])
    {
        // 当前页码(非必要)
        $current_page = isset($data['current_page']) ? (int)$data['current_page'] : 1;
        // 页面大小(非必要)
        $page_size = isset($data['page_size']) ? (int)$data['page_size'] : (int)ENV('PAGE_SIZE', 50);
        $params = [
            'where' => ['dele' => 1],// 未删除
            'orderBy' => ['admin_id', 'DESC'],
            'limit' => [($current_page - 1) * $page_size, $page_size],
            'field' => [
                'admin_id',
                'admin_account',
                'admin_nickname',
                'role_id',
                'last_login_ip',
                'last_login_time',
                'create_time',
                'update_time',
                'status',
            ],
        ];
        !empty($data['q']) && $params['like'] = ['admin_nickname', $data['q']];
        $rst = $this->adminModel->selectInfo($params, false);
        $total = 0;
        // 数据处理
        if (!empty($rst)) {
            $total = $this->adminModel->total($params);
        }
        // 返回数据
        return [
            'current_page' => $current_page,
            'page_size' => $page_size,
            'total_num' => $total,
            'list' => $rst
        ];
    }

    /**
     * TODO 获取管理员详情
     * @param array $data
     * @return array|string
     */
    public function getAdminDetails(array $data = [])
    {
        $params = [
            'where' => ['dele' => 1, 'admin_id' => $data['admin_id']],// 未删除
        ];
        $rst = $this->adminModel->findInfo($params, false);
        return $rst;
    }

    /**
     * TODO 编辑管理员
     * @param array $data
     * @return array|bool|int
     */
    public function editAdmin(array $data = [])
    {
        // 获取当前用户信息
        $adminInfo = $this->getAdminDetails($data);
        // 用户是否存在
        if (empty($adminInfo))
            return $this->_Error(4000, [], '该用户不存在');
        // 非超级管理员不能添加
        if (0 !== (int)$data['_role_id'] && $data['admin_id'] != $data['_admin_id'])
            return $this->_Error(4000, [], '无权操作');
        // 同为超级管理员不能修改其他超管信息
        if (0 === (int)$adminInfo['role_id'] && $adminInfo['admin_id'] != $data['_admin_id']) {
            return $this->_Error(4000, [], '跨权操作');
        }

        // 处理密码
        if (!empty($data['admin_pwd'])) {
            $data['admin_pwd'] = sha1($data['admin_pwd']);
        }

        $params = [
            'where' => ['admin_id' => $data['admin_id']],
            'data' => $data
        ];
        $rst = $this->adminModel->editInfo($params);
        if (is_array($rst)) {
            return $this->_Error(4000, [], '更新数据失败');
        }
        return $this->_Success();
    }

    /**
     * TODO 设置管理员状态
     * @param array $data
     * @return array
     */
    public function setAdminState(array $data = [])
    {
        // 获取当前用户信息
        $adminInfo = $this->getAdminDetails($data);
        // 用户是否存在
        if (empty($adminInfo))
            return $this->_Error(4000, [], '该用户不存在');
        // 非超级管理员不能添加
        if (0 !== (int)$data['_role_id'])
            return $this->_Error(4000, [], '无权操作');
        // 同为超级管理员不能修改其他超管信息
        if (0 === (int)$adminInfo['role_id']) {
            return $this->_Error(4000, [], '跨权操作');
        }
        $params = [
            'where' => ['admin_id' => $data['admin_id']],
            'data' => ['status' => $adminInfo['status'] % 2 + 1]
        ];
        $rst = $this->adminModel->editInfo($params);
        if (is_array($rst)) {
            return $this->_Error(4000, [], '更新数据失败');
        }
        return $this->_Success();
    }

    /**
     * TODO  添加管理
     * @param array $data
     * @return array
     */
    public function addAdmin(array $data = [])
    {
        if (empty($data['admin_account'])) return $this->_Error(4000, [], '输入账户名');
        if (empty($data['admin_pwd'])) return $this->_Error(4000, [], '输入密码');
        if (empty($data['admin_nickname'])) return $this->_Error(4000, [], '输入昵称');
        $data['role_id'] = isset($data['role_id']) ? $data['role_id'] : 1; // 默认非超管
        // 获取当前用户信息
        $params = [
            'where' => ['admin_account' => $data['admin_account'], 'dele' => 1],
        ];
        $adminInfo = $this->adminModel->findInfo($params);
        // 用户是否存在
        if (!empty($adminInfo))
            return $this->_Error(4000, [], '该用户已存在');
        // 非超级管理员不能添加
        if (0 !== (int)$data['_role_id'])
            return $this->_Error(4000, [], '无权操作');
        //  超级管理员不能添加超管
        if (0 === $data['role_id']) {
            return $this->_Error(4000, [], '跨权操作');
        }

        // 处理密码
        if (!empty($data['admin_pwd'])) {
            $data['admin_pwd'] = sha1($data['admin_pwd']);
        }

        $rst = $this->adminModel->addInfo($data);

        if (is_array($rst)) {
            return $this->_Error(4000, [], '添加数据失败');
        }
        return $this->_Success();
    }


    /**
     * TODO 删除管理员
     * @param array $data
     * @return array
     */
    public function deleteAdmin(array $data = [])
    {
        // 获取当前用户信息
        $adminInfo = $this->getAdminDetails($data);
        // 用户是否存在
        if (empty($adminInfo))
            return $this->_Error(4000, [], '该用户不存在');
        // 非超级管理员不能操作
        if (0 !== (int)$data['_role_id'])
            return $this->_Error(4000, [], '无权操作');
        // 不能删除超级管理
        if (0 === (int)$adminInfo['role_id']) {
            return $this->_Error(4000, [], '跨权操作');
        }
        // 参数
        $params = ['where' => ['admin_id' => $data['admin_id']]];
        $rst = $this->adminModel->deleInfo($params);
        if (is_array($rst)) {
            return $this->_Error(4000, [], '删除数据失败');
        }
        return $this->_Success();
    }


}