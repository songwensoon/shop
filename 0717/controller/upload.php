<?php
/**
 * @brief 文件上传
 * @class Upload
 * @note  前台
 */
class Upload extends IController
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
    
    public function index(){
		
		$album_id = IFilter::act( IReq::get('aid'),'int' );
        $act = IReq::get('t');
        if($act=='normal'){
            $act = 'normal';
        }elseif($act=='import'){
            $act = 'import';
        }else{
            $act = 'multi';
        }
		$albums_list = Album_Category::get_flat_category();
		$this->albums_list = $albums_list;
		$this->act = $act;
		$this->album_id = $album_id;
		$this->redirect('index');
    }

    function multi(){
        $album_id = IFilter::act( IReq::get('aid'),'int' );
        if(!$album_id){
			$act = IReq::get('t');
			if($act=='normal'){
				$act = 'normal';
			}elseif($act=='import'){
				$act = 'import';
			}else{
				$act = 'multi';
			}
			$albums_list = Album_Category::get_flat_category();
			$this->albums_list = $albums_list;
			$this->act = $act;
			$this->album_id = $album_id;
			$this->redirect('index',false);
            Util::showMessage("请先选择相册！");
			exit;
        }
        $upload_setting = array (
			'enable_pre_resize' =>'', 
			'allow_size' => '2M',
			'resize_width' => 1600,
			'resize_height' => 1200,
			'resize_quality' => 100,
			'enable_cut_big_pic' => '',
			'max_width' => 1600,
			'max_height' => 1200,
			'enable_thumb_cut' => '',
			'thumb_width' => 180,
			'thumb_height' => 180,
			'use_old_imgname' => ''
		);
		$support_type = array('jpg','jpeg','gif','png');
		$albumsDB = new IModel("albums");
		$user_id = ISafe::get('user_id');
		$where = 'id='.$album_id.' and user_id='.$user_id;
		$album_info = $albumsDB->getObj($where);
		$this->album_info = $album_info;
		$this->album_id = $album_id;
		$this->upload_setting = $upload_setting;
		$this->support_type = implode(',',$support_type);
        $this->redirect('multi');
    }
    
    function normal(){
        need_login('page');
        
        $album_id = intval($this->getRequest('aid'));

        if(!$album_id){
            showError(lang('pls_sel_album'));
        }

        $this->output->set('album_id',$album_id);
        $album_info = $this->mdl_album->get_info($album_id);
        $this->output->set('album_info',$album_info);
        
        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('upload_photo'),'link'=>site_link('upload'));
        $crumb_nav[] = array('name'=>lang('normal_mode'));
        $this->page_crumb($crumb_nav);

        $page_title = lang('upload_photo').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }

    function import(){
        need_login('page');
        
        $album_id = intval($this->getRequest('aid'));
        if(!$album_id){
            showError(lang('pls_sel_album'));
        }

        $this->output->set('album_id',$album_id);
        $album_info = $this->mdl_album->get_info($album_id);
        $this->output->set('album_info',$album_info);

        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('upload_photo'),'link'=>site_link('upload'));
        $crumb_nav[] = array('name'=>lang('normal_mode'));
        $this->page_crumb($crumb_nav);

        $page_title = lang('upload_photo').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }

    function save_import(){
        need_login('page');
        @set_time_limit(0);

        $timestamp = time();
        $dir = $this->getPost('dir');
        $autodel = $this->getPost('autodel')?true:false;
        $savemode = intval($this->getPost('save_mode'));
        $album_id = intval($this->getRequest('aid'));

        if(substr($dir,0,1) == '/' || substr($dir,1,1) == ':'){//如果是绝对地址
            $dirpath = $dir;
        }else{
            $dirpath = ROOTDIR.$dir;
        }
        //判断扫描的文件夹是否存在
        if(!is_dir($dirpath)){
            showError(lang('scan_dir_not_exists'));
        }
        if(!is_readable($dirpath)){
            showError(lang('dir_cannot_read'));
        }

        //开始扫描文件夹
        $alldir = getdirlist($dirpath);
        if(!$alldir){
            showError(lang('dir_has_no_files'));
        }

        $tmpfslib =& loader::lib('tmpfs');
        $imglib =& loader::lib('image');
        $supportType = $imglib->supportType();

        $album_num = 0;
        $photos_num = 0;
        foreach($alldir as $dir=>$files){
            if(count($files)<=0){
                continue;
            }
            if($savemode == 2){
                $dirname = file_base($dir);
                $dirname = file_en_name($dirname)?$dirname:date('Y-m-d',$timestamp).'_'.rand(10,99);
                $data = array(
                    'name' => $dirname,
                    'create_time' =>$timestamp,
                    'enable_comment' => 1,
                    'cate_id' => 0
                );
                //创建相册
                $album_id = $this->mdl_album->save($data);
                $album_num++;
            }

            foreach($files as $file){
                $file_ext = file_ext($file);
                if(!in_array($file_ext,$supportType)){
                    continue;
                }
                
                //将存储的图片读取到临时文件
                $tmpfile = time().rand(1000,9999).'.'.$file_ext;
                $tmpfslib->write($tmpfile,file_get_contents($file));
                $tmpfilepath = $tmpfslib->get_path($tmpfile);

                if($this->mdl_photo->save_upload($album_id,$tmpfilepath,file_base($file))){
                    if($autodel){
                        @unlink($file);
                    }
                    $photos_num++;
                }
            }
            if($savemode == 2){
                $this->mdl_album->update_photos_num($album_id);
                $this->mdl_album->check_repare_cover($album_id);
            }
        }
        if($savemode == 1){
            $this->mdl_album->update_photos_num($album_id);
            $this->mdl_album->check_repare_cover($album_id);
        }
        $msg = lang('import_success',$album_num,$photos_num);
        $this->output->set('result_msg',$msg);

        $this->output->set('album_id',$album_id);
        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('upload_photo'));        
        $this->page_crumb($crumb_nav);

        $page_title = lang('upload_photo').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }
    
    public function process(){
		$this->layout = '';
		@set_time_limit(0);
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
		
		$type = IFilter::string(IReq::get('t'));
		$album_id = IFilter::act( IReq::get('aid'),'int' );
        $chunk =  IFilter::act( IReq::get('chunk'),'int' );
        $chunks = IFilter::act( IReq::get('chunks'),'int' );
		$filename = IFilter::string(IReq::get('name'));
        $filehashname = md5($filename);

        $tmpfs_lib = new Tmp_Fs;
        $status = $tmpfs_lib->upload($filehashname,$chunk!=0);
        switch($status){
            case 100:
            $return = array(
                'jsonrpc'=>'2.0',
                'error'=> array( 
                    'code'=>$status,
                    'message'=>"打开目录失败！"
                 ),
                 'id'=>'id');
            break;
            case 101:
            $return = array(
                'jsonrpc'=>'2.0',
                'error'=>array(
                     'code'=>$status,
                     'message'=>"无法打开输入流。"
                 ),
                 'id'=>'id');
            break;
            case 102:
            $return = array(
                'jsonrpc'=>'2.0',
                'error'=>array(
                    'code'=>$status,
                    'message'=>"无法打开输出流。"
                 ),
                 'id'=>'id');
            break;
            case 0:
            $return = array('jsonrpc'=>'2.0','result'=>null,'id'=>'id');
        }
        if($status ==0 && ($chunks == 0||$chunk+1==$chunks)){
			$albumsDB = new IModel("albums");
			$user_id = ISafe::get('user_id');
			$where = 'id='.$album_id.' and user_id='.$user_id;
			$album_info = $albumsDB->getObj($where);
			$photo = new Photo();
            if(! $photo->save_upload($album_id,$tmpfs_lib->get_path($filehashname),$filename,true,array('cate_id'=>$album_info['cate_id']))){
                $return = array(
                'jsonrpc'=>'2.0',
                'error'=>array(
                    'code'=>$status,
                    'message'=> lang('Failed to save file.')
                 ),
                 'id'=>'id');
            }
        } 
        echo JSON::encode($return);
        exit;


















        @set_time_limit(0);
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $type = IFilter::string(IReq::get('t'));
		$album_id = IFilter::act( IReq::get('aid'),'int' );
       

        $chunk =  IFilter::act( IReq::get('chunk'),'int' );
        $chunks = IFilter::act( IReq::get('chunks'),'int' );
		$filename = IFilter::string(IReq::get('name'));
        $filehashname = md5($filename);

		//获得配置文件中的数据
		$config = new Config("site_config");

	 	//调用文件上传类
		$photoObj = new PhotoUpload();
		//设置缩略图大小
		$photoObj->setThumb(180,180);
		$result	= current($photoObj->albumphotorun());
		
		if($result['flag'] == 1)
		{
			$return = array('jsonrpc'=>'2.0','result'=>null,'id'=>'id');
		}
		else
		{
			$return = array(
                'jsonrpc'=>'2.0',
                'error'=> array( 
                    'code'=>100,
                    'message'=>$result['flag']
                 ),
                 'id'=>'id');
		}
		echo JSON::encode($return);
		exit;
    }

    function reupload(){
        need_login('page');

        $id = intval($this->getGet('id'));
        $photo_info = $this->mdl_photo->get_info($id);
        
        $imglib =& loader::lib('image');
        $supportType = $imglib->supportType();
        $filesize = $_FILES['upfile']['size'];
        $filename = $_FILES['upfile']['name'];
        $fileext = file_ext($filename);
        $allowsize = allowsize($this->setting->get_conf('upload.allow_size'));
        $error = '';

        if($_FILES['upfile']['error'] == 1){
            $error = lang('failed_larger_than_server',$filename);
            echo '<script>
                alert("'.$error.'");
                parent.hide_loading_bar();
            </script>';
            exit;
        }
        
        if($allowsize && $filesize>$allowsize){
            $error = lang('failed_larger_than_usetting',$filename);
            echo '<script>
                alert("'.$error.'");
                parent.hide_loading_bar();
            </script>';
            exit;
        }
        
        if($filesize == 0){
            $error = lang('failed_if_file',$filename);
            echo '<script>
                alert("'.$error.'");
                parent.hide_loading_bar();
            </script>';
            exit;
        }
        if(!in_array($fileext,$supportType)){
            $error = lang('failed_not_support',$filename);
            echo '<script>
                alert("'.$error.'");
                parent.hide_loading_bar();
            </script>';
            exit;
        }

        if($this->mdl_photo->save_upload($photo_info['album_id'],$_FILES['upfile']['tmp_name'],$filename,false,$photo_info)){
            if($photo_info['is_cover']){
                $this->mdl_album->set_cover($id);
            }

            echo '<script>
                parent.location.reload();
            </script>';

            exit;
        }else{
            echo '<script>
                alert("'.lang('Failed to save file.').$error.'");
                parent.$("#meiu_float_box").find("loading").hide();
                parent.$("#meiu_float_box").find("box_container").show();
            </script>';
            exit;
        }
    }
    
    function save(){
        @set_time_limit(0);
        @ignore_user_abort(true);
        $type = $this->getGet('t');
        $album_id = intval($this->getRequest('aid'));

        if(!$album_id){
            showError(lang('pls_sel_album'));
        }

        if($type == 'multi'){
            need_login('ajax');

            /*$files_count = intval($this->getPost('muilti_uploader_count'));
            for($i=0;$i<$files_count;$i++){
                $filename = $this->getPost("muilti_uploader_{$i}_tmpname");
                $realname = $this->getPost("muilti_uploader_{$i}_name");
                $purename = file_pure_name($filename);
                $purerealname = file_pure_name($realname);
                $photorow = $this->mdl_photo->get_photo_by_name_aid($album_id,$purename);
                if($photorow){
                    $this->mdl_photo->update($photorow['id'],array('name'=>$purerealname));
                }
            }
            */
            $this->mdl_album->update_photos_num($album_id);
            $this->mdl_album->check_repare_cover($album_id);
            
            $gourl = site_link('photos','index',array('aid'=>$album_id));
            form_ajax_success('box',lang('upload_photo_success'),null,1,$gourl);
        }else{
            need_login('page');
            
            $this->output->set('album_id',$album_id);
            $album_info = $this->mdl_album->get_info($album_id);
            $this->output->set('album_info',$album_info);

            $page_title = lang('upload_photo').' - '.$this->setting->get_conf('site.title');
            $page_keywords = $this->setting->get_conf('site.keywords');
            $page_description = $this->setting->get_conf('site.description');

            $this->page_init($page_title,$page_keywords,$page_description);
            
            $imglib =& loader::lib('image');
            $supportType = $imglib->supportType();

            $empty_num = 0;
            $error = '';
            $allowsize = allowsize($this->setting->get_conf('upload.allow_size'));
            if(isset($_FILES['imgs'])){
                foreach($_FILES['imgs']['name'] as $k=>$upfile){
                    
                    if (!empty($upfile)) {
                        $filesize = $_FILES['imgs']['size'][$k];
                        $tmpfile = $_FILES['imgs']['tmp_name'][$k];
                        $filename = $upfile;
                        $fileext = file_ext($filename);
                        
                        if($_FILES['imgs']['error'][$k] == 1){
                            $error .= lang('failed_larger_than_server',$filename).'<br />';
                            continue;
                        }
                        
                        if($allowsize && $filesize>$allowsize){
                            $error .= lang('failed_larger_than_usetting',$filename).'<br />';
                            continue;
                        }
                        
                        if($filesize == 0){
                            $error .= lang('failed_if_file',$filename).'<br />';
                            continue;
                        }
                        if(!in_array($fileext,$supportType)){
                            $error .= lang('failed_not_support',$filename).'<br />';
                            continue;
                        }
                        

                        if(! $this->mdl_photo->save_upload($album_id,$tmpfile,$filename,true,array('cate_id'=>$album_info['cate_id']))){
                            $error .= lang('file_upload_failed',$filename).'<br />';
                        }
                    }else{
                        $empty_num++;
                    }
                }
            }else{
                $error = lang('need_sel_upload_file');
            }
            if(isset($_FILES['imgs']) && $empty_num == count($_FILES['imgs']['name'])){
                $this->output->set('msginfo','<div class="failed">'.lang('need_sel_upload_file').'</div>');
            }else{
                $this->mdl_album->update_photos_num($album_id);
                $this->mdl_album->check_repare_cover($album_id);
                
                if($error){
                    $this->output->set('msginfo','<div class="failed">'.$error.'</div>');
                }else{
                    $this->output->set('msginfo','<div class="success">'.lang('upload_photo_success').'<a href="'.site_link('photos','index',array('aid'=>$album_id)).'">'.lang('view_album').'</a></div>');
                }
            }
            
            $crumb_nav = array();
            $crumb_nav[] = array('name'=>lang('upload_photo'));        
            $this->page_crumb($crumb_nav);

            loader::view('upload/normal');
        }
        
    }

}
