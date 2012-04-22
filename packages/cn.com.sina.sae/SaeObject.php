<?php
namespace cn\com\sina\sae;

/**
 * Sae基类
 * 
 * STDLib的所有class都应该继承本class,并实现SaeInterface接口  
 *
 * @author Easychen <easychen@gmail.com>
 * @version $Id$
 * @package sae
 * @ignore
 */

/**
 * SaeObject
 *
 * @package sae
 * @ignore
 */
abstract class SaeObject implements SaeInterface
{
    function __construct()
    {
        // 
    }
    
}
