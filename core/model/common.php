<?php  if( !defined("IN_IA") ) 
{
	exit( "Access Denied" );
}
class Common_DeamFoodModel{
	public $public_build = NULL;
	public function getSetData($uniacid = 0){
		global $_W;
		if(empty($uniacid)){
			$uniacid = $_W["uniacid"];
		}
		$sets = pdo_get("deamx_food_settings", array('uniacid' => $uniacid));
		if(empty($sets)) {
			$sets = array();
		}
		return $sets;
	}
	public function getSysset($key = "", $uniacid = 0){
		global $_W;
		global $_GPC;
		$set = $this->getSetData($uniacid);
		$allset = iunserializer($set["sets"]);
		$retsets = array();
		if(!empty($key)){
			if(is_array($key)){
				foreach( $key as $k ){
					$retsets[$k] = (isset($allset[$k]) ? $allset[$k] : array( ));
				}
			}else {
				$retsets = (isset($allset[$key]) ? $allset[$key] : array( ));
			}
			return $retsets;
		}else{
			return $allset;
		}
	}
	public function getPluginset($key = "", $uniacid = 0){
		global $_W;
		global $_GPC;
		$set = $this->getSetData($uniacid);
		$allset = iunserializer($set["plugins"]);
		$retsets = array();
		if(!empty($key)){
			if(is_array($key)){
				foreach($key as $k){
					$retsets[$k] = (isset($allset[$k]) ? $allset[$k] : array());
				}
			}else{
				$retsets = (isset($allset[$key]) ? $allset[$key] : array());
			}
			return $retsets;
		}else{
			return $allset;
		}
	}
	public function updateSysset($values, $uniacid = 0){
		global $_W;
		global $_GPC;
		if(empty($uniacid)){
			$uniacid = $_W["uniacid"];
		}
		$setdata = $this->getSetData($uniacid);
		if(empty($setdata)){
			$res = pdo_insert("deamx_food_settings", array( "sets" => iserializer($values), "uniacid" => $uniacid ));
			$setdata = array( "sets" => $values );
		}else{
			$sets = iunserializer($setdata["sets"]);
			$sets = (is_array($sets) ? $sets : array( ));
			foreach( $values as $key => $value ) {
				foreach( $value as $k => $v) {
					$sets[$key][$k] = $v;
				}
			}
			$res = pdo_update("deamx_food_settings", array( "sets" => iserializer($sets) ), array( "id" => $setdata["id"] ));
			if( $res ) 
			{
				$setdata["sets"] = $sets;
			}
		}
		if(empty($res)){
			$setdata = $this->getSetData($uniacid);
		}
		$this->setGlobalSet($uniacid);
	}
	public function updatePluginset($values, $uniacid = 0){
		global $_W;
		global $_GPC;
		if(empty($uniacid)){
			$uniacid = $_W["uniacid"];
		}
		$setdata = $this->getSetData($uniacid);
		if(empty($setdata)){
			$res = pdo_insert("deamx_food_settings", array( "plugins" => iserializer($values), "uniacid" => $uniacid ));
			$setdata = array( "plugins" => $values );
		}else{
			$plugins = iunserializer($setdata["plugins"]);
			if(!is_array($plugins)){
				$plugins = array();
			}
			foreach( $values as $key => $value ) {
				foreach( $value as $k => $v ) {
					if( !isset($plugins[$key]) || !is_array($plugins[$key]) ) {
						$plugins[$key] = array( );
					}
					$plugins[$key][$k] = $v;
				}
			}
			$res = pdo_update("deamx_food_settings", array( "plugins" => iserializer($plugins) ), array( "id" => $setdata["id"] ));
			if($res){
				$setdata["plugins"] = $plugins;
			}
		}
		if(empty($res)){
			$setdata = $this->getSetData($uniacid);
		}
		$this->setGlobalSet($uniacid);
	}
	public function setGlobalSet($uniacid = 0){
		$sysset = $this->getSysset("", $uniacid);
		$sysset = (is_array($sysset) ? $sysset : array( ));
		$pluginset = $this->getPluginset("", $uniacid);
		if(is_array($pluginset)){
			foreach($pluginset as $k => $v){
				$sysset[$k] = $v;
			}
		}
		return $sysset;
	}
}

?>