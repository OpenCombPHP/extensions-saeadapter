<?php
namespace org\opencomb\driver\sae\fs ;

use org\jecat\framework\fs\vfs\IPhysicalFileSystem ;

class SaeStorageFileSystem implements IPhysicalFileSystem
{
	public function __construct($aSaeStorage,$sDomain,$sFolderPrefix='')
	{
		$this->sDomain = $sDomain ;
		$this->sFolderPrefix = trim($sFolderPrefix,'/\\') ;
		if($this->sFolderPrefix)
		{
			$this->sFolderPrefix.= '/' ;
		}
		$this->aSaeStorage = $aSaeStorage ;
	}

	public function & openFile( $path , $mode , $options , &$opened_path)
	{
		$arrResource['file'] = $this->sFolderPrefix.ltrim($path,'/\\') ;
		$arrResource['position'] = 0 ;
		$arrResource['mode'] = $mode ;
		$arrResource['options'] = $options ;
		$arrResource['writen'] = false ;

		if ( in_array( $arrResource['mode'], array( 'r', 'r+', 'rb' ) ) ) {
			if ( !$arrResource['fcontent'] = $this->aSaeStorage->read($this->sDomain, $arrResource['file']) )
			{
				trigger_error("fopen({$path}): failed to read from Storage: No such domain or file.", E_USER_WARNING);
				$arrResource = null ;
			}
		} elseif ( in_array( $arrResource['mode'], array( 'a', 'a+', 'ab' ) ) ) {
			trigger_error("fopen({$path}): Sorry, saestor does not support appending", E_USER_WARNING);
			if ( !$arrResource['fcontent'] = $this->aSaeStorage->read($this->sDomain, $arrResource['file']) )
			{
				trigger_error("fopen({$path}): failed to read from Storage: No such domain or file.", E_USER_WARNING);
				$arrResource = null ;
			}
		} elseif ( in_array( $arrResource['mode'], array( 'x', 'x+', 'xb' ) ) ) {
			if ( !$this->aSaeStorage->getAttr($this->sDomain, $arrResource['file']) ) {
				$arrResource['fcontent'] = '';
			} else {
				trigger_error("fopen({$path}): failed to create at Storage: File exists.", E_USER_WARNING);
				$arrResource = null ;
			}
		} elseif ( in_array( $arrResource['mode'], array( 'w', 'w+', 'wb' ) ) ) {
			$arrResource['fcontent'] = '';
		} else {
			$arrResource['fcontent'] = $this->aSaeStorage->read($this->sDomain, $arrResource['file']);
		}

		return $arrResource;
	}

	public function readFile(&$arrResource,$nCount)
	{
		if (in_array($arrResource['mode'], array('w', 'x', 'a', 'wb', 'xb', 'ab') ) ) {
			return false ;
		}

		$ret = substr( $arrResource['fcontent'] , $arrResource['position'], $count);
		$arrResource['position'] += strlen($ret);

		return $ret;
	}

	public function writeFile(&$arrResource,$data)
	{
		if ( in_array( $arrResource['mode'], array( 'r', 'rb' ) ) ) {
			return false;
		}
		
		$left = substr($arrResource['fcontent'], 0, $arrResource['position']);
		$right = substr($arrResource['fcontent'], $arrResource['position'] + strlen($data));
		$arrResource['fcontent'] = $left . $data . $right;

		$arrResource['position'] += strlen($data);
		if ( strlen( $data ) > 0 )
		{
			$arrResource['writen'] = false;
		}

		return strlen( $data );
	}

	public function closeFile(&$arrResource)
	{
		if (!$arrResource['writen']) {
			$this->aSaeStorage->write( $this->sDomain, $arrResource['file'], $arrResource['fcontent'] );
			$arrResource['writen'] = true;
		}
	}

	public function endOfFile(&$arrResource)
	{
		return $arrResource['position'] >= strlen( $arrResource['fcontent']  );
	}

	public function tellFile(&$arrResource)
	{
		return $arrResource['position'];
	}

	public function seekFile(&$arrResource, $offset , $whence = SEEK_SET)
	{
		switch ($whence) {
			case SEEK_SET:

				if ($offset < strlen( $arrResource['fcontent'] ) && $offset >= 0) {
					$arrResource['position'] = $offset;
					return true;
				}
				else
					return false;

				break;

			case SEEK_CUR:

				if ($offset >= 0) {
					$arrResource['position'] += $offset;
					return true;
				}
				else
					return false;

				break;

			case SEEK_END:

				if (strlen( $arrResource['fcontent'] ) + $offset >= 0) {
					$arrResource['position'] = strlen( $arrResource['fcontent'] ) + $offset;
					return true;
				}
				else
					return false;

				break;

			default:

				return false;
		}
	}

	public function unlinkFile($path)
	{
		$sFile = $this->sFolderPrefix.ltrim($path,'/\\');
		clearstatcache( true );
		return $this->aSaeStorage->delete( $this->sDomain , $sFile );
	}
	/**
	 * @return bool
	 */
	public function lockFile(&$resource)
	{
		return false ;
	}

	public function flushFile(&$arrResource)
	{
echo __METHOD__ ; 
print_r($arrResource) ;
return ;
		if (!$arrResource['writen'])
		{
			$this->aSaeStorage->write( $this->sDomain, $arrResource['file'], $arrResource['fcontent'] );
			$arrResource['writen'] = true;
		}

		return $arrResource['writen'];
	}
/*
	public function stream_stat(&$arrResource)
	{
		return array();
	}
*/
	
	
	public function stat($path, $flags)
	{
		$sPath = $this->sFolderPrefix.ltrim($path,'/\\');
		 
		// 文件
		if ( $attr = $this->aSaeStorage->getAttr( $this->sDomain , $sPath ) ) {
			$stat = array();
			$stat['dev'] = $stat[0] = 0x8001;
			$stat['ino'] = $stat[1] = 0;;
			$stat['mode'] = $stat[2] = 33279; //0100000 | 0777;
			$stat['nlink'] = $stat[3] = 0;
			$stat['uid'] = $stat[4] = 0;
			$stat['gid'] = $stat[5] = 0;
			$stat['rdev'] = $stat[6] = 0;
			$stat['size'] = $stat[7] = $attr['length'];
			$stat['atime'] = $stat[8] = 0;
			$stat['mtime'] = $stat[9] = $attr['datetime'];
			$stat['ctime'] = $stat[10] = $attr['datetime'];
			$stat['blksize'] = $stat[11] = 0;
			$stat['blocks'] = $stat[12] = 0;
			return $stat;
		// 目录
		} else {
			$sPath = rtrim($sPath, '/\\');
			
			// 检查是否为空
			$arrFileList =& $this->aSaeStorage->getList($this->sDomain,$sPath.'/*',1) ;
			if( empty($arrFileList) )
			{
				return false ;
			}
			
			else 
			{
				$stat = array();
				$stat['dev'] = $stat[0] = 0x8001;
				$stat['ino'] = $stat[1] = 0;;
				$stat['mode'] = $stat[2] = 16895; //040000 | 0777;
				$stat['nlink'] = $stat[3] = 0;
				$stat['uid'] = $stat[4] = 0;
				$stat['gid'] = $stat[5] = 0;
				$stat['rdev'] = $stat[6] = 0;
				$stat['size'] = $stat[7] = 4096;
				$stat['atime'] = $stat[8] = 0;
				$stat['mtime'] = $stat[9] = 0;
				$stat['ctime'] = $stat[10] = 0;
				$stat['blksize'] = $stat[11] = 0;
				$stat['blocks'] = $stat[12] = 0;
			}
			return $stat;
		}
	}

	
	


	/**
	 * @return resource
	 */
	public function openDir($sPath,$options)
	{
		return false ;
	}
	
	/**
	 * @return string
	 */
	public function readDir(&$resource)
	{
		return false ;
	}
	
	/**
	 * @return bool
	 */
	public function closeDir(&$resource)
	{
		return false ;
	}
	/**
	 * @return bool
	 */
	public function rewindDir(&$resource)
	{
		return false ;
	}
	/**
	 * @return bool
	 */
	public function mkdir($path, $mode, $options)
	{
		return $this->aSaeStorage->write(
				$this->sDomain
				, $this->sFolderPrefix.$path.'/__________sae-dir-tag'
				, '1'
		)? true: false ;
	}
	/**
	 * @return bool
	 */
	public function rename($sFrom,$sTo)
	{
		return false ;
	}
	/**
	 * @return bool
	 */
	public function rmdir($sPath,$options)
	{
		return false ;
	}

	public function url($sPath)
	{
		return $this->sFolderPrefix.$sPath ;
	}

	
	private $sDomain ;
	private $sFolderPrefix ;
	private $aSaeStorage ;

}

