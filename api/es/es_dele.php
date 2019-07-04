<?php
require_once __DIR__.'/../vendor/autoload.php';
use Pheanstalk\Pheanstalk;				//队列
use Elasticsearch\ClientBuilder;		//es

class es 
{
	public $client;
	private $es = [
		'es.easysq.cn:80'				//es
		];
	private $bt = '127.0.0.1';			//队列
	public function __construct()
	{
		$hosts = $this->es;
		//连接es
		$this->client = ClientBuilder::create()   // Instantiate a new ClientBuilder
					  ->setSSLVerification(false)
					  ->setHosts($hosts)      	// Set the hosts
					  ->build();              	// Build the client object	
	}
	//测试方法
	public function index()
	{
		$pheanstalk = new Pheanstalk($this->bt,'11300','3',false);
		/*$put = $pheanstalk
					->useTube('rmrb_tube')
					->put("job payload goes here1\n",1024,0,10);*/
		$job = $pheanstalk
				->watch('rmrb_tube')			
				->ignore('default')	 
				->reserve();	
		$data = $job->getData();	//获取内容
		$id = $job->getId();		//获取id
		//$res = $pheanstalk->delete($job);
		print_r($data);
		print_r($id);
		exit;
		$params = [
			'index' => [ 
						//'my_index', 
						'rmrb' 
					]
		];
		$response = $this->client
								->indices()
								->getSettings($params);
		print_r($response);
		echo 123;exit;
	}
	//创建
	public function creatIndex($arr = [])
	{
		$arr['index'] = 'rmrb';
		$response = $this->client
								->index($arr);
		file_put_contents('./bean_log.txt','creat======'.json_encode($response)."/n",FILE_APPEND);//写日志
	}
	
	//修改
	public function editIndex($arr = [])
	{
		$arr['index'] = 'rmrb';
		$response = $this->client
								->update($arr);
		file_put_contents('./bean_log.txt','edit======'.json_encode($response)."/n",FILE_APPEND);
	}
	
	//删除
	public function deleIndex($arr = [])
	{
		$arr['index'] = 'rmrb';
		$response = $this->client
								->delete($arr);
		file_put_contents('./bean_log.txt','dele======'.json_encode($response)."/n",FILE_APPEND);
	}
	//beantalk循环 add
	public function hdAdd()
	{
		$pheanstalk = new Pheanstalk($this->bt,'11300','3',false);
		//bean_add
		while(true) {
			//获取任务，此为阻塞获取，直到获取有用的任务为止
			$job = $pheanstalk
							->watch('bean_add')			
							->ignore('default')	 
							->reserve();		
			$data = $job->getData();	//获取内容
			$data = json_decode($data,true);
			//处理任务
			$result= $this->creatIndex($data);
			if($result) {
				//删除任务
				$pheanstalk->delete($job);
			} else {
				//休眠任务
				$pheanstalk->bury($job);
			}
			//跳出无限循环
			if(file_exists('./shutdown.txt')) {
				file_put_contents('./shutdown.txt', 'bean_add'.date('Y-m-d H:i:s').'关闭');
				break;
			}
		}	
	}
	//beantalk循环 edit
	public function hdEdit()
	{
		$pheanstalk = new Pheanstalk($this->bt,'11300','3',false);
		//bean_edit
		while(true) {
			//获取任务，此为阻塞获取，直到获取有用的任务为止
			$job = $pheanstalk
							->watch('bean_edit')			
							->ignore('default')	 
							->reserve();		
			$data = $job->getData();	//获取内容
			$data = json_decode($data,true);
			//处理任务
			$result= $this->editIndex($data);
			if($result) {
				//删除任务
				$pheanstalk->delete($job);
			} else {
				//休眠任务
				$pheanstalk->bury($job);
			}
			//跳出无限循环
			if(file_exists('./shutdown.txt')) {
				file_put_contents('./shutdown.txt', 'bean_edit'.date('Y-m-d H:i:s').'关闭');
				break;
			}
		}	
	}
	//beantalk循环 dele
	public function hdDele()
	{
		$pheanstalk = new Pheanstalk($this->bt,'11300','3',false);
		//bean_dele
		while(true) {
			//获取任务，此为阻塞获取，直到获取有用的任务为止
			$job = $pheanstalk
							->watch('bean_dele')			
							->ignore('default')	 
							->reserve();		
			$data = $job->getData();	//获取内容
			$data = json_decode($data,true);
			//处理任务
			$result= $this->deleIndex($data);
			if($result) {
				//删除任务
				$pheanstalk->delete($job);
			} else {
				//休眠任务
				$pheanstalk->bury($job);
			}
			//跳出无限循环
			if(file_exists('./shutdown.txt')) {
				file_put_contents('./shutdown.txt', 'bean_dele'.date('Y-m-d H:i:s').'关闭');
				break;
			}
		}	
	}
	
	//创建索引
	public function creatIndex1()
	{
		$params = $this->indexParam();
		$response = $this->client
								->indices()
								->create($params);
		print_r($response);
		file_put_contents('./bean_log.txt','creatIndex1======'.json_encode($response)."/n",FILE_APPEND);
	}
	
	//索引参数
	private function indexParam()
	{
		$params = [
			'index' => 'rmrb',
			'body'=>[
				'settings' => [
					'number_of_shards' => 1,  	// 一个主节点
					'number_of_replicas' => 1	//一个副本
				],
				'mappings' => [
					'news' => [		//资讯
						'_source' => [
							'enabled' => true		//存储这个索引的内容
						],
						'properties' => [			//字段
							'newsID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'newsTitle' => [
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'newsContent' => [
								'type' => 'string',
								'analyzer' => 'ik_max_word'
							],
							'newsSource'=>[
								'type' => 'string',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'newsIntro'=>[
								'type' => 'string',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'newsTime'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'newsState'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'newsDele'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'newsReco'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],							
							'newsLink'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'newsAuth'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'attrID'=>[
								'type' => 'string',
								'analyzer' => 'ik_max_word'
							],
							'commentNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'favoriteNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'starNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'attach'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
						]
					],
					'activity'=>[		//活动
						'_source' => [
							'enabled' => true		//存储这个索引的内容
						],
						'properties' => [			//字段
							'activityID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'activityTitle'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'activityContent'=>[
								'type' => 'string',	//字段类型
								'analyzer' => 'ik_max_word'
							],
							'activityLoc'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'activitySource'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'activityBegin'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'activityEnd'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'activityTime'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'enlistBegin'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'enlistEnd'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'activityUrl'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'activityPic'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'activityCost'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'activityTel'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'attach'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'attrID'=>[
								'type' => 'string',
								'analyzer' => 'ik_max_word'
							],
							'activityRun'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'activityState'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'activityDele'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'activityReco'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'enlistNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'commentNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'favoriteNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'isGlobal'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'isEnlist'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'sysUserID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'starNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							]
						]
					],
					'learn'=>[	//学习
						'_source' => [
							'enabled' => true		//存储这个索引的内容
						],
						'properties' => [			//字段
							'learnID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'learnTitle'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'learnContent'=>[
								'type' => 'string',	//字段类型
								'analyzer' => 'ik_max_word'
							],
							'learnSource'=>[
								'type' => 'string',
								'analyzer' => 'ik_max_word'
							],
							'learnLink'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'learnAuth'=>[
								'type' => 'string',
								'analyzer' => 'ik_max_word'
							],
							'attrID'=>[
								'type' => 'string',
								'analyzer' => 'ik_max_word'
							],
							'attach'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'learnPic'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'learnTime'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'learnState'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'learnDele'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'learnReco'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'learnNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'commentNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'favoriteNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'isGlobal'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'isComment'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'starNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							]
						]
					],
					'job'=>[	//职位
						'_source' => [
							'enabled' => true		//存储这个索引的内容
						],
						'properties' => [			//字段
							'jobID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'jobTitle'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'jobIntro'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'jobLoc'=>[
								'type' => 'string',
								'analyzer' => 'ik_max_word'
							],
							'jobEntice'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'lastTime'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'releTime'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'jobPay'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'jobExp'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'jobType'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'jobNature'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'jobState'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'jobDele'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'degress'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'companyID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'favoriteNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'starNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'commentNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'jobReco'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'jobScore'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'attrID'=>[
								'type' => 'string',
								'analyzer' => 'ik_max_word'
							]
						]
					],
					'company'=>[//公司
						'_source' => [
							'enabled' => true		//存储这个索引的内容
						],
						'properties' => [			//字段
							'companyID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'companyName'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'companyLogo'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'companyPic'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'companyTime'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'lastTime'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'companyScore'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'jobNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'provinceID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'cityID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'areaID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'companyState'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'companyDele'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'companyReco'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'commentNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'favoriteNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'starNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'companyPop'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'attrGroupID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'companyFinance'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'attrID'=>[
								'type' => 'string',
								'analyzer' => 'ik_max_word'
							]
						]
					],
					'course'=>[ //课程
						'_source' => [
							'enabled' => true		//存储这个索引的内容
						],
						'properties' => [			//字段
							'courseID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'courseTitle'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'courseFit'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'courseIntro'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'attrID'=>[
								'type' => 'string',
								'analyzer' => 'ik_max_word'
							],
							'coursePrice'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'lastTime'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'courseType'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'courseState'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'courseDele'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'isFree'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'learnNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'offline'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'teacherID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'PID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'favoriteNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'starNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'commentNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'instID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'attrGroupID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'courseReco'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'courseClassify'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'courseNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'isAudit'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'examID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'exam1ID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'courseScore'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							]
						]
					],
					'inst'=>[	//机构
						'_source' => [
							'enabled' => true		//存储这个索引的内容
						],
						'properties' => [			//字段
							'instID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'instName'=>[
								'type' => 'string',	//字段类型
								'index'=>'analyzed',
								'fields'=>[
									'cn'=>[
										'type'=>'string',
										'analyzer' => 'ik_smart' //ik_max_word: 会将文本做最细粒度的拆分 ik_smart: 会做最粗粒度的拆分
									],
									'en'=>[
										'type'=>'string',
										'analyzer' => 'ik_max_word'	 //ik_max_word
									]
								]
							],
							'instLogo'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'instPic'=>[
								'type' => 'string',
								'index' => 'not_analyzed'
							],
							'attrID'=>[
								'type' => 'string',
								'analyzer' => 'ik_max_word'
							],
							'instScore'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'courseNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'studentNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'instState'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'instDele'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'instReco'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'commentNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'favoriteNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'starNum'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'provinceID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'cityID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							],
							'areaID'=>[
								'type' => 'integer',
								'index' => 'not_analyzed'
							]
						]
					]
				]
			]
		];
		return $params;
	}
	
	
	
	
	
}


//(new es())->index();			//测试方法
	
//(new es())->creatIndex1();		//建数据结构

//(new es())->hdAdd();			//建索引

//(new es())->hdEdit();			//修改索引

(new es())->hdDele();			//删除索引










?>