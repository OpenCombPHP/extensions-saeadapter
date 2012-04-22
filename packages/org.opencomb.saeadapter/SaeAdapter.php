<?php 
namespace org\opencomb\saeadapter ;

use org\opencomb\development\toolkit\platform\CreateDistribution;
use org\opencomb\platform\service\Service;
use org\opencomb\platform\ext\Extension ;
use org\jecat\framework\lang\oop\Package;
use org\jecat\framework\lang\oop\ClassLoader;
use net\phpconcept\pclzip\PclZip;

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

					// oc.init.php 上的常量定义
					'oc.init.php:define-const' => array(
							'FRAMEWORK_FOLDER' => "'saestor://framework'" ,
							'PLATFORM_FOLDER' => "'saestor://platform'" ,
							'EXTENSIONS_FOLDER' => "'saestor://extensions'" ,
							'EXTENSIONS_URL' => "'extensions'" ,
							'SERVICES_FOLDER' => "'saestor://services'" ,
							'PUBLIC_FILES_FOLDER' => "'saestor://public/files'" ,
							'PUBLIC_FILES_URL' => "'http://{\$_SERVER['HTTP_APPNAME']}-ocstor.stor.sinaapp.com/public/files'" ,
					) ,
						
					// 安装程序上的默认输入
					'installer-default-input' => array(
							'sDBServer' => "<?php echo SAE_MYSQL_HOST_M ?>:<?php echo SAE_MYSQL_PORT ?>" ,
							'sDBUsername' => "<?php echo SAE_MYSQL_USER ?>" ,
							'sDBPassword' => "<?php echo SAE_MYSQL_PASS ?>" ,
							'sDBName' => "<?php echo SAE_MYSQL_DB ?>" ,
					) ,
					
					'process-before-package' => function (CreateDistribution $aDistributionMaker, PclZip $aPackage){
								// 生成 sae_app_wizard.xml
								$aDistributionMaker->packFileByTemplate(
										null, 'sae_app_wizard.xml', 'saeadapter:platform/sae_app_wizard.xml', $aPackage
								) ;
								
								// 打包一些 driver 类
								foreach(array(
									'org\\opencomb\\driver\\sae\\fs' => 'SaeStorageFileSystem' ,
								) as $sNamespace=>$sClass)
								{
									$sClassPath = ClassLoader::singleton()->searchClass($sNamespace.'\\'.$sClass,Package::nocompiled) ;
									$aPackage->add( $sClassPath, 'drivers/'.str_replace('\\','/',$sNamespace).'/', dirname($sClassPath) ) ;
								}
					} ,
					// 'process-after-package' => array( 'org\\opencomb\\development\\toolkit\\platform\\CreateDistribution', 'debugProcessAfterPackage' ) ,
					
					// 插入到安装程序中的代码
					'sSetupCodes' => "
// 注册 SAE wrapper
require_once __DIR__.'/../framework/class/fs/vfs/VFSWrapper.php' ;
require_once __DIR__.'/../framework/class/fs/vfs/IPhysicalFileSystem.php' ;
require_once __DIR__.'/../framework/class/fs/vfs/VirtualFileSystem.php' ;
require_once __DIR__.'/../driver/org/opencomb/sae/fs/SaeStorageFileSystem.php' ;
stream_wrapper_register('oc','org\\jecat\\framework\\fs\\vfs\\VFSWrapper') ;
" ,
					// 插入到oc.init.php文件中的代码
					'sOcInitCodes' => "
// 启动 vfs
require_once __DIR__.'/framework/class/fs/vfs/VFSWrapper.php' ;
require_once __DIR__.'/framework/class/fs/vfs/IPhysicalFileSystem.php' ;
require_once __DIR__.'/framework/class/fs/vfs/LocalFileSystem.php' ;
require_once __DIR__.'/framework/class/fs/vfs/VirtualFileSystem.php' ;
stream_wrapper_register('oc','org\\\\jecat\\\\framework\\\\fs\\\\vfs\\\\VFSWrapper') ;
\\org\\jecat\\framework\\fs\\vfs\\VFSWrapper::vfs('oc')->mount('/',new LocalFileSystem(__DIR__)) ;

// 加载 SAE平台所需的类
require_once __DIR__.'/driver/org/opencomb/sae/fs/SaeStorageFileSystem.php' ;
" ,
			) ;
		}
	}
}