<?php
namespace cn\com\sina\sae;


/**
 * SaeInterface , public interface of all sae client apis
 *
 * all sae client classes must implement these method for setting accesskey and secretkey , getting error infomation.
 * @package sae
 * @ignore
 **/
interface SaeInterface
{
    public function errmsg();
    public function errno();
}
