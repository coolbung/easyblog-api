<?php
/**
* @package		EasyBlog
* @copyright	Copyright (C) 2010 Stack Ideas Private Limited. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasyBlog is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');
jimport( 'joomla.html.toolbar' );
jimport( 'simpleschema.category' );
jimport( 'simpleschema.person' );
jimport( 'simpleschema.blog.post' );

class EasyBlogViewEntry extends EasyBlogView
{
	function display( $tmpl = null )
	{
		$config		= EasyBlogHelper::getConfig();
		$jConfig	= EasyBlogHelper::getJConfig();

		require_once( EBLOG_HELPERS . DIRECTORY_SEPARATOR . 'date.php' );
		require_once( EBLOG_HELPERS . DIRECTORY_SEPARATOR . 'helper.php' );
		require_once( EBLOG_HELPERS . DIRECTORY_SEPARATOR . 'string.php' );
		require_once( EBLOG_CLASSES . DIRECTORY_SEPARATOR . 'adsense.php' );

		$id 		= JRequest::getInt( 'id' );
		$callback	= JRequest::getCmd('callback', '' );

        $model		= $this->getModel( 'Blog' );
		$blog 		= EasyBlogHelper::getTable( 'Blog' );
		$blog->load( $id );

		if(!$blog->id) { 
			echo $callback ? $callback . '(' . json_encode(array()) . ')' : json_encode(array()); 
			jexit(); 
		}

	
		$item = EasyBlogHelper::getHelper( 'SimpleSchema' )->mapPost($blog, '<p><br><pre><a><blockquote><strong><h2><h3><em>');
		
		echo $callback ? $callback . '(' . json_encode($item) . ')' : json_encode($item); 
		jexit(); 

	}

}
