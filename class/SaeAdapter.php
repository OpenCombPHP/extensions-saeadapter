<?php 
namespace org\opencomb\saeadapter ;

use org\opencomb\development\toolkit\platform\CreateDistribution;
use org\opencomb\platform\service\Service;
use org\opencomb\platform\ext\Extension ;

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
			CreateDistribution::$arrPlatforms['sae'] = array(
			
					'title' => '新浪云计算平台(SAE)应用包' ,
					'essential-extensions' => array('coresystem','saeadapter') ,
			
					// 检查根目录的可写权限
					'bCheckRootWritable' => false ,
					
					// oc.config.php 文件的位置
					'sFileOcConfig' => "'saestor://ocstor/oc.config.php'" ,
						
					// 安装程序上的默认输入
					'installer-default-input' => array(
							'sServicesFolder' => 'saestor://ocstor/services' ,
							'sPublicFilesFolder' => 'saestor://ocstor/public/files' ,
							'sPublicFileUrl' => "http://<?php echo \$_SERVER['HTTP_APPNAME']?>-ocstor.stor.sinaapp.com/public/files" ,
							'sDBServer' => "<?php echo SAE_MYSQL_HOST_M ?>:<?php echo SAE_MYSQL_PORT ?>" ,
							'sDBUsername' => "<?php echo SAE_MYSQL_USER ?>" ,
							'sDBPassword' => "http://<?php echo \$_SERVER['HTTP_APPNAME']?>-ocstor.stor.sinaapp.com/public/files" ,
							'sDBName' => "<?php echo SAE_MYSQL_PASS ?>" ,
					) ,
					
					'process-before-package' => array('org\\opencomb\\saeadapter\\platform\\CreateSaeDistribution','processBeforePackage') ,
					
					'arrLibClasses' => array("org\\jecat\\framework\\fs\\wrapper\\SaeStorageWrapperEx") ,
					'arrInitCodes' => "
// 注册 SAE wrapper
stream_wrapper_unregister('saestor') ;
stream_wrapper_register('saestor','SaeStorageWrapperEx') ;
" ,
			) ;
		}
	}
}