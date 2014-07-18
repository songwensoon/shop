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
			echo "";exit;
			$this->redirect('/simple/login');
		}
	}
    public function index()
    {
		$pageSize = 10;
		$cate = IReq::get('cate_id');
		$user_id = ISafe::get('user_id');
		$albumsDB   = new IQuery("albums");
		if(empty($cate)){
			$cate = 0;
		}
		$where = ' user_id='.$user_id.' and id='.$cate;
		$albumcateDB = new IModel('albumcate');
		$albumcateInfo = $albumcateDB->getObj($where);
		$page = IReq::get('page');
		if(!$page){
			$page= 1;
		}
		$albumsDB->where = ' user_id='.$user_id.' and cate_id='.$cate;
		$albumsDB->page = $page;
		$albumsDB->pagesize = 8;
		$albumsRows = $albumsDB->find();
		$photosDB = new IModel("photos");
		$user_id = ISafe::get('user_id');
		if(!empty($albumsRows)){
			foreach($albumsRows as $k=>$v){
				if($v['cover_id']){
					$where = 'id='.$v['cover_id'].' and user_id='.$user_id;
					$cover_info = $photosDB->getObj($where);
					if($cover_info){
						$albumsRows[$k]['cover_path'] = $cover_info['thumb'];
					}else{
						$albumsRows[$k]['cover_id'] = 0;
					}
				}
			}
		}
		$PageBarHtml = $albumsDB->getPageBar();
		$cate_list = Album_Category::get_flat_category();
		$this->cate_list = $cate_list;
		$this->PageBarHtml = $PageBarHtml;
		$this->albums = $albumsRows;
		$this->cate = $cate;
		$this->albumcateInfo = $albumcateInfo;
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
	public function modify(){   
		$this->layout = '';
        $id = IFilter::act( IReq::get('id'),'int' );
		$albumsDB = new IModel("albums");
		$user_id = ISafe::get('user_id');
		$where = 'id='.$id.' and user_id='.$user_id;
		$info = $albumsDB->getObj($where);

        $cate_list = Album_Category::get_flat_category();
		$fromurl = IUrl::creatUrl("albums/modify/id/{$id}");
		$fromurl = base64_encode($fromurl);
		$data = array(
		'cate_list'=>$cate_list,
		'fromurl'=>$fromurl,
		'info'=>$info,
		'id'=>$id
		);
		$this->render('views/default/albums/modify',$data);
    }
	public function confirm_delete(){
		$this->layout = '';
        $id = IFilter::act( IReq::get('id'),'int' );
		$albumsDB = new IModel("albums");
		$user_id = ISafe::get('user_id');
		$where = 'id='.$id.' and user_id='.$user_id;
		$album_info = $albumsDB->getObj($where);

		$data = array(
		'album_name'=>$album_info['name'],
		'id'=>$id
		);
		$this->render('views/default/albums/confirm_delete',$data);
	}
	public function delete(){
        $this->layout = '';
        $id = IFilter::act( IReq::get('id'),'int' );
		$albumsDB = new IModel("albums");
		$user_id = ISafe::get('user_id');
		$where = 'id='.$id.' and user_id='.$user_id;
		$result = $albumsDB->del($where);
        if($result){
            Ui::ajax_box("删除相册成功",null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            Ui::ajax_box("删除相册失败");
        }
    }
	//确认批量删除
	public function confirm_delete_batch(){
        $this->layout = '';
        $ids = IReq::get('sel_id');
        if(!$ids || count($ids) == 0){
            Ui::ajax_box("请先选择要删除的相册！");
        }
        $this->render('views/default/albums/confirm_delete_batch');
    }
	//批量删除
	public function delete_batch(){
		$ids = IReq::get('sel_id');
		if(!$ids || count($ids) == 0){
            Ui::ajax_box("请先选择要删除的相册！");
        }else{
			$ids = array_keys($ids);
			if(!is_array($ids)){
				Ui::ajax_box("删除相册失败");
			}
			$in_sql = '';
			foreach($ids as $i){
				$in_sql .= intval($i).',';
			}
			$in_sql = trim($in_sql,',');
			$albumsDB = new IModel("albums");
			$user_id = ISafe::get('user_id');
			$where = 'id in ('.$in_sql.') and user_id='.$user_id;
			
            $result = $albumsDB->del($where);
			if($result){
				Ui::ajax_box("删除相册成功",null,0.5,$_SERVER['HTTP_REFERER']);
			}else{
				Ui::ajax_box("删除相册失败");
			}
        }
    }
	
	public function photos(){
		
        $search['name'] = IFilter::string(IReq::get('sname'));
        $search['album_id'] = $album_id = IFilter::act( IReq::get('album_id'),'int' );
        $albumsDB = new IModel("albums");
		$user_id = ISafe::get('user_id');
		$where = 'id='.$album_id.' and user_id='.$user_id;
		$album_info = $albumsDB->getObj($where);
		
		$pageSize = 10;
		$user_id = ISafe::get('user_id');
		$photosDB   = new IQuery("photos");
		
		$page = IReq::get('page');
		if(!$page){
			$page= 1;
		}
		$photosDB->where = ' user_id='.$user_id.' and album_id='.$album_id;
		$photosDB->page = $page;
		$photosDB->pagesize = 8;
		$photos = $photosDB->find();
		$PageBarHtml = $photosDB->getPageBar();
        if(!empty($photos)){
            foreach($photos as $k=>$v){
				$img_url = IUrl::creatUrl("albums/photosview/id/$v[id]");
                $img_thumb = '<a href="'.$img_url.'"> <img src="'.$v['thumb'].'" alt="'.$album_info['name'].'_'.$v['name'].'" /></a>';
                $photos[$k]['img_thumb'] = $img_thumb;
            }
        }
		$this->PageBarHtml = $PageBarHtml;
        $this->photos = $photos;
		$this->album_id = $album_id;
		$this->redirect('photos');

    }
}
