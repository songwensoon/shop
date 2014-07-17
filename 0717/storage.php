<?php
/**
 * $Id: upload.php 2014-07-18 297461244@qq.com $
 * 
 *      
 * @author : soon
 * @support : 
 * @copyright : 297461244@qq.com
 */
class Storage{
    
    function __construct(){
        $this->worker = new Stor_File;
    }

    function mkdirs($pathname, $mode = 0755){
        return $this->worker->mkdirs($pathname,$mode);
    }
    
    function upload($id,$src){
        return $this->worker->upload($id,$src);
    }
    
    function write($id,$src){
        return $this->worker->write($id,$src);
    }

    function getListByPath($path){
        return $this->worker->getListByPath($path);
    }

    function read($id){
        return $this->worker->read($id);
    }

    function fileExists($id){
        return $this->worker->fileExists($id);
    }

    function deleteFolder($dir){
        return $this->worker->deleteFolder($dir);
    }
    
    function delete($id){
        return $this->worker->delete($id);
    }
    
    function getUrl($id){
        return $this->worker->getUrl($id);
    }

    function getPath($id){
        return $this->worker->getPath($id);
    }
}
?>
