<?php
/**
 *
 *
 * @author imfox
 * @url
 */
if( !defined("IN_IA") )
{
    exit( "Access Denied" );
}
class ImFoxModule
{
    public function run(){
        global $_W,$_GPC;
        $router = trim($_GPC['op']);
        $this->$router();
    }

}