<?php
/**
 * @brief 用户中心模块
 * @class Ucenter
 * @note  前台
 */
class AlbumCategory extends IController
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
        $this->redirect('index');
    }

	public function create()
	{
		$this->layout = '';
		$from = IReq::get('from');
		$cate_list = Album_Category::get_flat_category();
		$data = array(
		'cate_list'=>$cate_list,
		'from'=>$from
		);
		$this->render('views/default/albumcategory/create',$data);
	}
	public function save(){
		$from = IReq::get('from');
        $data['name'] = IFilter::string(IReq::get('cate_name'));
		$data['par_id']   = IFilter::act(IReq::get('par_id'),'int');
		$data['sort']   = IFilter::act(IReq::get('sort'),'int');
		$user_id = ISafe::get('user_id');
		$data['user_id'] = $user_id;
		$result = array(
		'ret'=>true,
		'html'=>''
		);
        if($data['name'] == ''){
			$result = array(
			'ret'=>false,
			'html'=>'分类名不能为空！'
			);
			echo JSON::encode($result);exit;
        }
		$albumcate = new IModel("albumcate");
		$where = 'id = '.$data['par_id'];
		$row = $albumcate->getObj($where);
		
		$albumcate->setData($data);
		$albumcateid = $albumcate->add();
		$uparr = array();
		if($data['par_id'] != 0){
            $uparr['cate_path'] = $row['cate_path'].$albumcateid.',';
        }else{
            $uparr['cate_path'] = ','.$albumcateid.',';
        }
		$albumcate->setData($uparr);
		$where = "`id`=".$albumcateid;
		$albumcate->update($where);
        if($albumcateid){
            if($from){
				$from = base64_decode($from);
				$html = 
                Ui::form_ajax_success('box','创建分类成功<script>setTimeout(function(){ Mui.box.show("'.$from.'",true); },1000)</script>');
            }else{
                Ui::form_ajax_success('box','创建分类成功',null,0.5,$_SERVER['HTTP_REFERER']);
            }
        }else{
            Ui::form_ajax_failed('text','创建分类失败');
        }
    }

}
