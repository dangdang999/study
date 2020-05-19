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

class Index extends ImFoxModule{
    public function get_index_settings(){
        global $_W;
        //adv
        $adv = pdo_getall("deamx_food_adv",array('uniacid'=>$_W['uniacid'],'adv_isshow'=>"1"),array('id','adv_title','adv_img','adv_url'),'',"sortid desc,id desc");
        foreach($adv as &$row){
            $row['adv_img'] = tomedia($row['adv_img']);
        }
        unset($row);
        //广告
        $sysset = m('common')->getSysset();
        $advertisement = $sysset['advertisement'];
        show_json(1,array('adv'=>$adv,'adv_width'=>$advStyle[0],'adv_height'=>$advStyle[1],'advertisement' => $advertisement));
    }
}