<?php
/**
 * 与UI BOX相关的
 *
 * @author soon
 * @packge 
 */
class Ui{
	//表单提交成功提示
	public static function form_ajax_success($type = 'box|text', $content , $title = null, $close_time = 0 , $forward = ''){
		self::form_ajax($type,true,$content,$title,$close_time,$forward);
	}
	//表单提交失败提示
	public static function form_ajax_failed($type = 'box|text', $content , $title = null, $close_time = 0 , $forward = ''){
		self::form_ajax($type,false,$content,$title,$close_time,$forward);
	}

	public static function form_ajax($type = 'box|text', $flag , $content , $title = null, $close_time = 0 , $forward = ''){		
		if($type == 'box'){
			$content = self::ajax_box($content,$title,$close_time,$forward,false);
		}
		
		echo JSON::encode(
			array('ret'=>$flag,'html'=>$content)
		);
		exit;
	}

	//浮动图层内容
	public static function ajax_box( $content , $title = '', $close_time = 0 , $forward = '' , $display = true )
	{   
		if(!$title){
			$title = "系统提示";
		}
		$html = "<div class='box_title titbg'><div class='closer sprite i_close' onclick='Mui.box.close()'></div>".$title."</div>";
		$html .= "<div class='box_container'>".$content."</div>";
		if ($close_time > 0){
			$html .= "<script defer='defer'>";
			$close_time = $close_time*1000;
			if ($forward==''){
				$html .= "setTimeout(function (){Mui.box.close()},{".$close_time."});";
			}else{
				$html .= "setTimeout(function (){window.location.href='".addslashes($forward)."';},".$close_time.");";
			}
			$html .= '</script>';
		}
		if($display){
			echo $html;
			exit;
		}else{
			return $html;
		}
	}
}
