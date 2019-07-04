<?php

/*
  description:对PHPExcel一些操作方法的封装
 */

namespace App\Extend;

class Excel
{

    protected $E;   //excel类

    public function __construct()
    {    //构造方法
        require_once(__DIR__ . '/PHPExcel/PHPExcel.php');
        $this->E = new \PHPExcel();
    }

    /* 	toArr  excel转为数组
      $arr = [
      'path'=>excel文件路径,
      'sheet'=>取第几个sheet, 从0开始记
      'row'=>从哪一行开始  从0开始记
	  'ext'=>'xlsx'   //if elsx
      ]
     */

    public function toArr($arr = [])
    {
        if (empty($arr))
            return false;
        if (isset($arr['ext']) && $arr['ext'] == 'xlsx') {
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        } else {
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        }
        $objReader->setReadDataOnly(true);       /* 加载对象路径 */
        $objPHPExcel = $objReader->load($arr['path']);    /* 获取工作表 */
        if (!isset($arr['sheet'])) {
            $objWorksheet = $objPHPExcel->getActiveSheet();   /* 获得当前活动的工作表，即打开默认显示的那张表 */
        } else {
            $objWorksheet = $objPHPExcel->getSheet($arr['sheet']); /* 也可以这样获取，读取第一个表,参数0 */
        }
        $highestRow = $objWorksheet->getHighestRow();    /* 得到总行数 */
        $highestColumn = $objWorksheet->getHighestColumn();         /* 得到总列数 */
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);            /* 取单元数据进数组 */

        $excelData = [];
        $row = isset($arr['row']) ? (int)$arr['row'] : 1;   //默认从
        for ($row; $row <= $highestRow; ++$row) {
            for ($col = 0; $col <= $highestColumnIndex; ++$col) {
                $excelData[$row][] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;
    }

    /* 	toExcel  数组转为excel
     * 	$arr = [
      'cloumn'	=>	['列1','列2'],
      'data'		=>	[0=>['1','2','3'],1=>['4','5','6']],
      'title'		=>	'标题',
      'ty'		=>	'1',		//默认保存
      'path'		=>	'保存路径',
      'filename'	=>	'文件名'
      'width'       => 30    //设置宽度
      ]  传入的数组 ty表示保存或者下载
     * 	$arr
     */

    public function toExcel($arr = [])
    {
        //支持26列
        $letter = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        if (empty($arr))
            return ['code' => '0000', 'msg' => '无数据，请检查'];
        //设置excel列名
        if (isset($arr['cloumn'])) {  //
            foreach ($arr['cloumn'] as $k => $v) {
                $this->E->setActiveSheetIndex(0)->setCellValue($letter[$k] . '1', $v);
            }
        }
        $width = isset($arr['width']) ? $arr['width'] : 20;
        //把数据循环写入excel中
        if (isset($arr['data'])) {
            foreach ($arr['data'] as $key => $value) {
                if (isset($arr['cloumn'])) {
                    $key += 2;
                } else {
                    $key += 1;
                }
                foreach ($value as $ko => $vo) {
                    if (!is_numeric($ko))
                        continue;
                    $this->E->setActiveSheetIndex(0)->setCellValue($letter[$ko] . $key, $vo);
                    $this->E->setActiveSheetIndex(0)->getColumnDimension($letter[$ko])->setWidth($width);
                }
            }
        }
        $title = isset($arr['title']) ? $arr['title'] : 'Excel2007'; //标题 默认为Excel2007
        $this->E->getActiveSheet()->setTitle($title);
        $this->E->setActiveSheetIndex(0);
        $objWriter = \PHPExcel_IOFactory::createWriter($this->E, 'Excel2007');
        $filename = isset($arr['filename']) ? $arr['filename'] : date('Ymd') . mt_rand(11111, 99999) . '.xlsx';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
        return ['code' => '2000', 'msg' => $filename];
    }


    /**
     *
     * execl数据导出
     * 应用场景：订单导出
     * @param string $title 模型名（如Member），用于导出生成文件名的前缀
     * @param array $cellName 表头及字段名
     * @param array $data 导出的表数据
     *
     * 特殊处理：合并单元格需要先对数据进行处理
     */
    function exportOrderExcel($title, $cellName, $data)
    {
        // 引入核心文件
        $objPHPExcel = new \PHPExcel();
        // 定义配置
        $topNumber = 2;//表头有几行占用
        // 创建人
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        // 最后修改人
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        // 标题
        $objPHPExcel->getProperties()->settitle("Office 2007 XLSX Test Document");
        // 题目
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        // 描述
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        // 关键字
        $objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
        // 种类
        $objPHPExcel->getProperties()->setCategory("Test result file");
        // 文件名称
        $fileName = iconv('utf-8', 'gb2312', $title) . date('_YmdHis');
        $cellKey = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM',
            'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'
        );

        //写在处理的前面（了解表格基本知识，已测试）
//     $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);//所有单元格（行）默认高度
//     $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);//所有单元格（列）默认宽度
//     $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);//设置行高度
//     $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);//设置列宽度
//     $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);//设置文字大小
//     $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);//设置是否加粗
//     $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);// 设置文字颜色
//     $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
//     $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
//     $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);//设置填充颜色
//     $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FF7F24');//设置填充颜色

        //处理表头标题
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $cellKey[count($cellName) - 1] . '1');//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '订单信息');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //处理表头
        foreach ($cellName as $k => $v) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellKey[$k] . $topNumber, $v[1]);//设置表头数据
            $objPHPExcel->getActiveSheet()->freezePane($cellKey[$k] . ($topNumber + 1));//冻结窗口
            $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k] . $topNumber)->getFont()->setBold(true);//设置是否加粗
            $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k] . $topNumber)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
            if ($v[3] > 0)//大于0表示需要设置宽度
            {
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellKey[$k])->setWidth($v[3]);//设置列宽度
            }
        }
        //处理数据
        foreach ($data as $k => $v) {
            foreach ($cellName as $k1 => $v1) {
                $objPHPExcel->getActiveSheet()->setCellValue($cellKey[$k1] . ($k + 1 + $topNumber), $v[$v1[0]]);
                if ($v['end'] > 0) {
                    if ($v1[2] == 1)//这里表示合并单元格
                    {
                        $objPHPExcel->getActiveSheet()->mergeCells($cellKey[$k1] . $v['start'] . ':' . $cellKey[$k1] . $v['end']);
                        $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k1] . $v['start'])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    }
                }
                if ($v1[4] != "" && in_array($v1[4], array("LEFT", "CENTER", "RIGHT"))) {
                    $v1[4] = eval('return \PHPExcel_Style_Alignment::HORIZONTAL_' . $v1[4] . ';');
                    //这里也可以直接传常量定义的值，即left,center,right；小写的strtolower
                    $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k1] . ($k + 1 + $topNumber))->getAlignment()->setHorizontal($v1[4]);
                }
            }
        }
        //导出execl
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls"); // attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

}
