<?php
namespace org\opencomb\driver\sae\setting ;

use org\jecat\framework\setting\ISetting;

class SaeKVDBSetting implements ISetting
{
	public function __construct()
	{
		$this->load() ;
	}
	
	/**
	 * 新建一个键
	 * @param string $sPath 键路径
	 * @return IKey 
	 */
	public function createKey()
	{
		return true ;
	}
	
	/**
	 * 检查是否存在键 
	 * @param string $sPath 键路径
	 * @return boolen 如果存在返回true ,不存在返回false
	 */
	public function hasKey($sPath)
	{
		$sPath = $this->sKeyPrefix.trim($sPath,'/') ;
		return array_key_exists($sPath,$this->arrKeys) ;
	}
	
	/**
	 * 删除一个键
	 * @param string $sPath 键路径
	 * @return boolen 删除成功返回true，失败返回false
	 */
	public function deleteKey($sPath)
	{
		$sPath = $this->sKeyPrefix.trim($sPath,'/') ;
		
		// 删除下级键
		foreach($this->keyIterator($sPath) as $sSubKey)
		{
			$this->deleteKey($sPath . '/' . $sSubKey) ;
		} 
		
		// 删除自己
		unset($this->arrKeys[$sPath]) ;
		$this->saeKvdb()->delete($sPath) ;
	}
	
	/**
	 * 获得子键的键名迭代器
	 * @param string $sPath 键路径
	 * @return \Iterator 
	 */
	public function keyIterator($sPath=null)
	{
		$sPath = $sPath? (trim($sPath,'/').'/'): '' ;
		$nPathLen = strlen($sPath) ;
		
		$arrChildKeys = array() ;
		foreach($this->arrKeys as $sKey=>&$item)
		{
			if(substr($sKey,0,$nPathLen)===$sPath)
			{
				$sSubKey = substr($sKey,$nPathLen) ;
				$pos = strpos($sSubKey,'/') ;
				if($pos!==0)
				{
					$sSubKey = substr($sSubKey,0,$sSubKey) ;
				}
				
				$arrChildKeys[] = $sSubKey ;
			}
		}
		
		return new \ArrayIterator($arrChildKeys) ;
	}
	
	/**
	 * 获得项的值
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @param mixed $defaultValue 默认值 ,如果项不存在就取默认值,并且以默认值新建项
	 */
	public function item($sPath,$sName='*',$defaultValue=null)
	{
		$sPath = trim($sPath,'/') ;
		
		if( !array_key_exists($sName,$this->arrKeys[$sPath]) )
		{
			$this->setItem($sPath,$sName,$defaultValue) ;
		}
		
		return $this->arrKeys[$sPath][$sName] ;
	}
	
	/**
	 * 设置项的值
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @param mixed $value
	 */
	public function setItem($sPath,$sName,$value)
	{
		$sPath = trim($sPath,'/') ;
		$this->arrKeys[$sPath][$sName] = $value ;
		
		$this->saeKvdb()->set($this->sKeyPrefix.$sPath,serialize($this->arrKeys[$sPath])) ;
	}
	
	/**
	 * 检查项是否存在
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @return boolen 如果项存在就返回true,如果不存在返回false
	 */
	public function hasItem($sPath,$sName)
	{
		$sPath = trim($sPath,'/') ;
		return array_key_exists($sPath,$this->arrKeys) and array_key_exists($sName,$this->arrKeys[$sPath]) ;
	}
	
	/**
	 * 删除项 
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 */
	public function deleteItem($sPath,$sName)
	{
		$sPath = trim($sPath,'/') ;
		unset($this->arrKeys[$sPath][$sName]) ;
		
		if( empty($this->arrKeys[$sPath]) )
		{
			unset($this->arrKeys[$sPath]) ;
			$this->saeKvdb()->delete($this->sKeyPrefix.$sPath) ;
		}
		else
		{
			$this->saeKvdb()->set($this->sKeyPrefix.$sPath,serialize($this->arrKeys[$sPath])) ;
		}
	}
	
	/**
	 * 获得项的名字迭代器
	 * @param string $sPath 键路径
	 * @return \Iterator 
	 */
	public function itemIterator($sPath)
	{
		return empty($this->arrKeys[$sPath])?
					new \EmptyIterator() :
					new \ArrayIterator(array_keys($this->arrKeys[$sPath])) ;
	}
	

	/**
	 * 保存
	 */
	public function save()
	{
		return true ;
	}

	private function load()
	{
		$aKvdb = $this->saeKvdb() ;
		$nSeek = 0 ;
		while($keys=$aKvdb->pkrget($this->sKeyPrefix,100,$nSeek))
		{
			$nSeek+= 100 ;
			foreach($keys as $sKey=>&$item)
			{
				list(,$sKey) = explode(':',$sKey,2) ;
				$this->arrKeys[$sKey] =& unserialize($item) ;
			}
		}
		
		ksort($this->arrKeys) ;
	}

	/**
	 * @param string $sKeyPath 键路径
	 * @return ISetting
	 */
	public function separate($sKeyPath)
	{
		$sPath = $sKeyPath? (trim($sPath,'/').'/'): '' ;
		$nPathLen = strlen($sPath) ;
		
		$arrSubKeys = array() ;
		foreach($this->arrKeys as $sKey=>&$item)
		{
			if( substr($sKey,0,$nPathLen)!==$sPath )
			{
				continue ;
			}
			
			$sKey = substr($sKey,$nPathLen) ;
			$arrSubKeys[$sKey] =& $item ;
		}
		
		$aSubSetting = new self() ;
		$aSubSetting->arrKeys =& $arrSubKeys ;
		$aSubSetting->sKeyPrefix = $this->sKeyPrefix . $sPath ;
		$aSubSetting->aKvdb = $this->aKvdb ;
		
		return $aSubSetting ;
	}

	protected function saeKvdb()
	{
		if(!$this->aKvdb)
		{
			$this->aKvdb = new \SaeKV();
			$this->aKvdb->init() ;
		}
		return $this->aKvdb ;
	}

	private $aKvdb ;
	private $arrKeys = array() ;
	private $sKeyPrefix = 'ocsetting:' ;
}

?>