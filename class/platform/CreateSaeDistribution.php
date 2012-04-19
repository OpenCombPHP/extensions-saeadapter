<?php
namespace org\opencomb\saeadapter\platform ;

use org\opencomb\development\toolkit\platform\CreateDistribution;
use net\phpconcept\pclzip\PclZip;

class CreateSaeDistribution
{
	static public function process(CreateDistribution $aDistributionMaker, PclZip $aPackage)
	{
		// 生成 sae_app_wizard.xml
		$aDistributionMaker->packFileByTemplate(
				null, 'sae_app_wizard.xml', 'saeadapter:platform/sae_app_wizard.xml', $aPackage
		) ;

		$arrLibClasses = $aDistributionMaker->params()->get('arrLibClasses')?:array() ;
		$arrLibClasses[] = "org\\jecat\\framework\\fs\\wrapper\\SaeStorageWrapperEx" ;
		$aDistributionMaker->params()->set('arrLibClasses',$arrLibClasses) ;
		
	} 
}

?>