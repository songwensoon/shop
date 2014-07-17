<?php
/**
 * $Id: upload.php 2014-07-18 297461244@qq.com $
 * 
 *      
 * @author : soon
 * @support : 
 * @copyright : 297461244@qq.com
 */
class Upload{

	private $dir = 'upload'; //图片存储的目录名称

	//构造函数
	function __construct($dir = '')
	{
		//设置默认路径地址
		if($dir == '')
		{
			$dir = self::hashDir();
		}

		$this->setDir($dir);
	}

	/**
	 * @brief 获取图片散列目录
	 * @return string
	 */
	public static function hashDir()
	{
		$dir  = isset(IWeb::$app->config['upload']) ? IWeb::$app->config['upload'] : $this->dir;
		$dir .= '/'.date('Y/m/d');
		return $dir;
	}

    function get_path($fileName){
        $fileName = preg_replace('/[^\w\._]+/', '', $fileName);
        return $this->dir . DIRECTORY_SEPARATOR . $fileName;
    }

    function write($fileName,$content,$append=false,$fullPath=false){
        if (!file_exists($this->dir))
            @mkdir($this->dir);
        
        if($fullPath){
             $filePath = $fileName;
        }else{
            $filePath = $this->dir . DIRECTORY_SEPARATOR . $fileName;
        }
        if($append){
            return file_put_contents($filePath,$content,FILE_APPEND);
        }
        return file_put_contents($filePath,$content);
    }

    function read($fileName,$fullPath=false){
        if($fullPath){
             $filePath = $fileName;
        }else{
            $filePath = $this->dir . DIRECTORY_SEPARATOR . $fileName;
        }
        return file_get_contents($filePath);
    }

    function delete($fileName,$fullPath=false){
        if($fullPath){
             $filePath = $fileName;
        }else{
            $filePath = $this->dir . DIRECTORY_SEPARATOR . $fileName;
        }
        return @unlink($filePath);
    }

    function upload($fileName,$append=false,$fullPath=false){ 
        $fileName = preg_replace('/[^\w\._]+/', '', $fileName);

        if (!file_exists($this->dir))
            @mkdir($this->dir);
            
        if($fullPath){
            $filePath = $fileName;
        }else{
            $filePath = $this->dir . DIRECTORY_SEPARATOR . $fileName;
        }

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"]))
            $contentType = $_SERVER["CONTENT_TYPE"];
        
        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($filePath, !$append ? "wb" : "ab");
                if ($out) {
                    $in = fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else
                        return 101;
                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                } else
                    return 102;
            } else
                return 0;
        }else{
            $out = @fopen($filePath, !$append ? "wb" : "ab");
            if ($out) {
                $in = @fopen("php://input", "rb");
                if ($in) {
                    while ($buff = fread($in, 4096))
                        fwrite($out, $buff);
                } else
                    return 101;
                fclose($in);
                fclose($out);
            } else{
                return 102;
            }
            return 0;
        }
    }
}
