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
        $this->redirect('index');
    }

	public function create()
	{
		$this->layout = '';
		$this->render('views/default/albums/create');
	}
}
