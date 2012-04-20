<?php
namespace org\opencomb\saeadapter\service ;

use org\jecat\framework\cache\SaeStorageCache;
use org\jecat\framework\cache\EmptyCache;
use org\opencomb\platform\service\ServiceSerializer;
use org\opencomb\platform\service\Service;
use org\opencomb\platform\service\ServiceFactory ;

class SaeServiceFactory extends ServiceFactory
{
	protected function createCache(Service $aService,$sSerivceCacheFolder)
	{
		// (debug模式下不使用缓存)
		if( !$aService->isDebugging() )
		{
			$arrPath = parse_url($sSerivceCacheFolder) ;
			
			$aCache = new SaeStorageCache($arrPath['host'],$arrPath['path']) ;
			Cache::setSingleton($aCache) ;
			
			return $aCache ;
		}
		else 
		{
			return null ;
		}
	}
	
	protected function createServiceSerializer(Service $aService)
	{
		$aServiceSerializer = ServiceSerializer::singleton(true,$aService) ;
		$aServiceSerializer->setCache(new EmptyCache()) ;
		
		// 返回 null
		return ;
	}
}

