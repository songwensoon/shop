<?php
/**
 * $Id: upload.php 2014-07-18 297461244@qq.com $
 * 
 *      
 * @author : soon
 * @support : 
 * @copyright : 297461244@qq.com
 */
class Image{
    function __construct(){
        $this->worker = new Image_Gd;
    }
    
    function load($filename){
        return $this->worker->load($filename);
    }
    
    function supportType(){
        return $this->worker->supportType();
    }
    
    function getWidth(){
        return $this->worker->getWidth();
    }
    
    function getHeight(){
        return $this->worker->getHeight();
    }
    
    function getExtension(){
        return $this->worker->getExtension();
    }
    
    function save($path){
        $this->worker->save($path);
    }
    
    function output(){
        $this->worker->output();
    }
    
    function resizeTo($w=0,$h=0){
        $this->worker->resizeTo($w,$h);
    }
    function resizeScale($w=0,$h=0){
        $this->worker->resizeScale($w,$h);
    }
    
    function square($v){
        $this->worker->square($v);
    }
    
    function resizeCut($w,$h){
        $this->worker->resizeCut($w,$h);
    }
    
    function rotate($a){
        $this->worker->rotate($a);
    }
    
    function waterMarkSetting($param){
        $this->worker->waterMarkSetting($param);
    }
    
    function waterMark(){
        $this->worker->waterMark();
    }

    function close(){
        $this->worker->close();
    }
}
?>
