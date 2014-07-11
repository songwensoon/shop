<?php
/**
 * @brief 用户中心模块
 * @class Ucenter
 * @note  前台
 */
class Albums extends IController
{
	public $layout = 'albums';

	public function init()
	{
		CheckRights::checkUserRights();

		if(ISafe::get('user_id') == '')
		{
			$this->redirect('/simple/login');
		}
	}
    public function index()
    {
		$pageSize = 10;
		$cate = IReq::get('cate');
		$user_id = ISafe::get('user_id');
		$albumsDB   = new IQuery("albums");
		if(empty($cate)){
			$cate = 0;
		}
		$page = IReq::get('page');
		if(!$page){
			$page= 1;
		}
		$albumsDB->where = ' user_id='.$user_id.' and cate_id='.$cate;
		$albumsDB->page = $page;
		$albumsDB->pagesize = 8;
		$albumsRows = $albumsDB->find();
		$PageBarHtml = $albumsDB->getPageBar();
		$cate_list = Album_Category::get_flat_category();
		$this->cate_list = $cate_list;
		$this->PageBarHtml = $PageBarHtml;
		$this->albums = $albumsRows;
		$this->cate = $cate;
        $this->redirect('index');
    }

	public function create()
	{
		$this->layout = '';
		$cate_list = Album_Category::get_flat_category();
		$fromurl = IUrl::creatUrl('albums/create');
		$fromurl = base64_encode($fromurl);
		$data = array(
		'cate_list'=>$cate_list,
		'fromurl'=>$fromurl
		);
		$this->render('views/default/albums/create',$data);
	}
	

	public function save(){
		
        $album['name'] = IFilter::string(IReq::get('album_name'));
        $album['desc'] = IFilter::string(IReq::get('desc'));
        $album['priv_type'] = IFilter::act( IReq::get('priv_type'),'int' )?IFilter::act( IReq::get('priv_type'),'int' ):0;
        $album['tags'] = IFilter::string(IReq::get('album_tags'));
        $album['priv_pass'] = IFilter::string(IReq::get('priv_pass'));
        $album['priv_question'] = IFilter::string(IReq::get('priv_question'));
        $album['priv_answer'] = IFilter::string(IReq::get('priv_answer'));
        $album['cate_id'] = IFilter::act( IReq::get('cate_id'),'int' );
        $album['create_time'] = $album['up_time'] = time();
        $album['enable_comment'] = IFilter::act( IReq::get('enable_comment'),'int' );
		$user_id = ISafe::get('user_id');
		$album['user_id'] = $user_id;
        if($album['name'] == ''){
            Ui::form_ajax_failed('text',"相册名称不能为空");
        }
        if($album['priv_type'] == '1'){
            if($album['priv_pass']==''){
                Ui::form_ajax_failed('text','相册密码不能为空');
            }
        }
        if($album['priv_type'] == '2'){
            if($album['priv_question'] == ''){
                Ui::form_ajax_failed('text','相册问题不能为空');
            }
            if($album['priv_answer'] == ''){
                Ui::form_ajax_failed('text','相册答案不能为空');
            }
        }
		$albumsDB = new IModel("albums");
		$albumsDB->setData($album);
		$album_id = $albumsDB->add();
		if($album_id){
			Ui::form_ajax_success('box','创建相册成功',null,0.5,$_SERVER['HTTP_REFERER']);
		}else{
			Ui::form_ajax_failed('text','创建相册失败');
		}	
    }

	public function photos()
	{
	
	}
}
