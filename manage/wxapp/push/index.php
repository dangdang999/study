<?php
/**
 * 门店管理后台
 *
 * @author cmszs
 * @url
 */
defined('IN_IA') or exit('Access Denied');
class Index_DeamFoodManage extends DeamFoodManage
{
    public function test(){
        header('content-type:application/json');
        $url = "https://aip.baidubce.com/rest/2.0/image-classify/v2/advanced_general?access_token=24.7ea258761b4ff191b494960a42f3f4fc.2592000.1563955502.282335-16620439";
        $image = file_get_contents("/www/wwwroot/wx.dzapi.com/attachment/images/WechatIMG306.jpeg");
        $image = base64_encode($image);
        $data = array(
            'image' => $image
        );
        $content = ihttp_post($url, $data);
        $data = json_decode($content['content'], true);
        print_r($data);
    }
}