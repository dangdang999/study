<?php
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$operation = trim($_GPC['op']);
if($operation == 'uploadImage'){
    load()->func("file");
    $field = "files";
    if( !empty($_FILES[$field]["name"]) )
    {
        if( is_array($_FILES[$field]["name"]) )
        {
            $files = array( );
            foreach( $_FILES[$field]["name"] as $key => $name )
            {
                if( strrchr($name, ".") === false )
                {
                    $name = $name . ".jpg";
                }
                $file = array( "name" => $name, "type" => $_FILES[$field]["type"][$key], "tmp_name" => $_FILES[$field]["tmp_name"][$key], "error" => $_FILES[$field]["error"][$key], "size" => $_FILES[$field]["size"][$key] );
                if( function_exists("exif_read_data") )
                {
                    $image = imagecreatefromstring(file_get_contents($file["tmp_name"]));
                    $exif = exif_read_data($file["tmp_name"]);
                    if( !empty($exif["Orientation"]) )
                    {
                        switch( $exif["Orientation"] )
                        {
                            case 8: $image = imagerotate($image, 90, 0);
                                break;
                            case 3: $image = imagerotate($image, 180, 0);
                                break;
                            case 6: $image = imagerotate($image, -90, 0);
                                break;
                        }
                    }
                    imagejpeg($image, $file["tmp_name"]);
                }
                $files[] = uploadImage($file);
            }
            $ret = array( "status" => "success", "files" => $files );
            exit( json_encode($ret) );
        }
        else
        {
            if( strrchr($_FILES[$field]["name"], ".") === false )
            {
                $_FILES[$field]["name"] = $_FILES[$field]["name"] . ".jpg";
            }
            $result = uploadImage($_FILES[$field]);
            exit( json_encode($result) );
        }
    }
    else
    {
        $result["message"] = "请选择要上传的图片！";
        exit( json_encode($result) );
    }
}