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

class Basic extends ImFoxModule{
    public function get_form_id(){
        global $_W, $_GPC;
        $formId = trim($_GPC['form_id']);
        if(!empty($_W['openid']) && $formId != "the formId is a mock one"){
            com('message')->saveFormId($formId, $_W['openid'], '1');
        }else{
            exit("need not save");
        }
    }
}