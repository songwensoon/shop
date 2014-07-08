<?php
/**
 * @copyright (c) 2011 jooyea.cn
 * @file article.php
 * @brief 关于文章管理
 * @author chendeshan
 * @date 2011-02-14
 * @version 0.6
 */

 /**
 * @class article
 * @brief 文章管理模块
 */
class Article
{
	//显示标题
	public static function showTitle($title,$color=null,$fontStyle=null)
	{
		$str='<span style="';
		if($color!=null) $str.='color:'.$color.';';
		if($fontStyle!=null)
		{
			switch($fontStyle)
			{
				case "1":
				$str.='font-weight:bold;';
				break;

				case "2":
				$str.='font-style:oblique;';
				break;
			}
		}
		$str.='">'.$title.'</span>';
		return $str;
	}
	/**
	 * @brief 获取树形分类
	 * @param int $catId 分类ID
	 * @return array
	 */
	public static function catTree($catId)
	{
		$result    = array();
		$catDB     = new IModel('article_category');
		$childList = $catDB->query('parent_id = '.$catId);
		if(!$childList)
		{
			$catRow = $catDB->getObj('id = '.$catId);
			$childList = $catDB->query('parent_id = '.$catRow['parent_id']);
		}
		return $childList;
	}
	
	/**
	 * @brief 获取树形分类下所有文章分类ID
	 * @param int $catId 分类ID
	 * @return array
	 */
	public static function getcatTreeids($catId)
	{
		$result    = array();
		$ids = array();
		$catDB     = new IModel('article_category');
		$childList = $catDB->query('parent_id = '.$catId);
		if(!empty($childList))
		{
			$ids[] = $catId;
			foreach($childList as $k=>$v)
			{
				$tmpids = self::getcatTreeids($v['id']);
				$ids = array_merge($ids,$tmpids);
			}
		}else{
			$ids[] = $catId;
		}
		return $ids;
	}
	/**
	 * @brief 获取树形分类
	 * @param int $catId 分类ID
	 * @return array
	 */
	public static function articleTree($articleId)
	{
		$result    = array();
		$articleDB     = new IModel('article');
		$catRow = $articleDB->getObj('id = '.$articleId);
		$childList = self::catTree($catRow['category_id']);
		return $childList;
	}
	/**
	 * @brief  获取分类数据
	 * @param  int   $catId  分类ID
	 * @return array $result array(id => name)
	 */
	public static function  articlecatRecursion($articleId)
	{
		$result    = array();
		$articleDB     = new IModel('article');
		$catRow = $articleDB->getObj('id = '.$articleId);
		$result = self::catRecursion($catRow['category_id']);
		return $result;
	}
	/**
	 * @brief  获取分类数据
	 * @param  int   $catId  分类ID
	 * @return array $result array(id => name)
	 */
	public static function catRecursion($catId)
	{
		$result = array();
		$catDB  = new IModel('article_category');
		$catRow = $catDB->getObj('id = '.$catId);
		while(true)
		{
			if($catRow)
			{
				array_unshift($result,array('id' => $catRow['id'],'name' => $catRow['name']));
				$catRow = $catDB->getObj('id = '.$catRow['parent_id']);
			}
			else
			{
				break;
			}
		}
		return $result;
	}
	
	public static function get_word($string, $length, $dot = '..', $charset = 'utf-8')
    {
		$string = strip_tags($string);
        if (strlen($string) <= $length)
        {
            return $string;
        }

        $string = str_replace(array('　', '&nbsp;', '&', '"', '<', '>'), array('', '', '&', '"', '<', '>'), $string);

        $strcut = '';
        if (strtolower($charset) == 'utf-8')
        {

            $n = $tn = $noc = 0;
            while ($n < strlen($string))
            {

                $t = ord($string[$n]);
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126))
                {
                    $tn = 1;
                    $n++;
                    $noc++;
                }
                elseif (194 <= $t && $t <= 223)
                {
                    $tn = 2;
                    $n += 2;
                    $noc += 2;
                }
                elseif (224 <= $t && $t < 239)
                {
                    $tn = 3;
                    $n += 3;
                    $noc += 2;
                }
                elseif (240 <= $t && $t <= 247)
                {
                    $tn = 4;
                    $n += 4;
                    $noc += 2;
                }
                elseif (248 <= $t && $t <= 251)
                {
                    $tn = 5;
                    $n += 5;
                    $noc += 2;
                }
                elseif ($t == 252 || $t == 253)
                {
                    $tn = 6;
                    $n += 6;
                    $noc += 2;
                }
                else
                {
                    $n++;
                }

                if ($noc >= $length)
                {
                    break;
                }

            }
            if ($noc > $length)
            {
                $n -= $tn;
            }

            $strcut = substr($string, 0, $n);

        }
        else
        {
            for ($i = 0; $i < $length; $i++)
            {
                $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
            }
        }

        return $strcut . $dot;
    }
}
