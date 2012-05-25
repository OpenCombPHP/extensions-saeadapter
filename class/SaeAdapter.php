<?php 
namespace org\opencomb\saeadapter ;

use org\opencomb\development\toolkit\platform\CreateDistribution;
use org\opencomb\platform\service\Service;
use org\opencomb\platform\ext\Extension;

class SaeAdapter extends Extension 
{
	/**
	 * 载入扩展
	 */
	public function active(Service $aService)
	{
		// 检查是否安装 development-toolkit 扩展
		if( class_exists('org\\opencomb\\development\\toolkit\\platform\\CreateDistribution') )
		{
			$sVersion = Extension::flyweight('saeadapter')->metainfo()->version() ;
			
			CreateDistribution::$arrPlatforms['sae'] = array(
			
					'title' => '新浪云计算平台(SAE)应用包' ,
					'essential-extensions' => array('coresystem','saeadapter') ,
			
					// 检查根目录的可写权限
					'bCheckRootWritable' => false ,
					
					// oc.config.php 文件的位置
					'sFileOcConfig' => "'saestor://ocstor/oc.config.php'" ,
						
					// 安装程序上的默认输入
					'installer-default-input' => array(
							'sServicesFolder' => "'saestor://ocstor/services'" ,
							'sPublicFilesFolder' => "'saestor://ocstor/public/files'" ,
							'sPublicFileUrl' => "'http://{\$_SERVER['HTTP_APPNAME']}-ocstor.stor.sinaapp.com/public/files'" ,
							'sDBServer' => "<?php echo SAE_MYSQL_HOST_M ?>:<?php echo SAE_MYSQL_PORT ?>" ,
							'sDBUsername' => "<?php echo SAE_MYSQL_USER ?>" ,
							'sDBPassword' => "<?php echo SAE_MYSQL_PASS ?>" ,
							'sDBName' => "<?php echo SAE_MYSQL_DB ?>" ,
					) ,
				
					// service 安装位置
					'sInstallServiceFolder' => "install_root.'/services'" ,
					
					'process-before-package' => array('org\\opencomb\\saeadapter\\platform\\CreateSaeDistribution','processBeforePackage') ,
					'process-after-package' => array( 'org\\opencomb\\development\\toolkit\\platform\\CreateDistribution', 'debugProcessAfterPackage' ) ,
					
					// 插入到安装程序中的代码
					'sSetupCodes' => "
// 注册 SAE wrapper
require_once __DIR__.'/../sae-adapter/org/opencomb/saeadapter/wrapper/SaeStorageWrapper.php' ;
stream_wrapper_unregister('saestor') ;
stream_wrapper_register('saestor','org\\opencomb\\saeadapter\\wrapper\\SaeStorageWrapper') ;
" ,
					// 插入到oc.init.php文件中的代码
					'sOcInitCodes' => "
// 加载 SAE平台所需的类
require_once \\org\\jecat\\framework\\CLASSPATH.'/cache/SaeStorageCache.php' ;
require_once __DIR__.'/extensions/saeadapter/{$sVersion}/class/wrapper/SaeStorageWrapper.php' ;
require_once __DIR__.'/extensions/saeadapter/{$sVersion}/class/service/SaeServiceFactory.php' ;
// 注册 SAE wrapper
stream_wrapper_unregister('saestor') ;
stream_wrapper_register('saestor','org\\opencomb\\saeadapter\\wrapper\\SaeStorageWrapper') ;
// 注册 SaeServiceFactory
service\ServiceFactory::setSingleton(new \\org\\opencomb\\saeadapter\\service\\SaeServiceFactory) ;
" ,
			) ;
		}
	}
}
