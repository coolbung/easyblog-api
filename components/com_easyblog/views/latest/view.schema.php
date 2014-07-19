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

class EasyBlogViewLatest extends EasyBlogView
{
	function display( $tmpl = null )
	{
		$config		= EasyBlogHelper::getConfig();
		$jConfig	= EasyBlogHelper::getJConfig();

		require_once( EBLOG_HELPERS . DIRECTORY_SEPARATOR . 'date.php' );
		require_once( EBLOG_HELPERS . DIRECTORY_SEPARATOR . 'helper.php' );
		require_once( EBLOG_HELPERS . DIRECTORY_SEPARATOR . 'string.php' );
		require_once( EBLOG_CLASSES . DIRECTORY_SEPARATOR . 'adsense.php' );

		$sort		= JRequest::getCmd('sort', $config->get( 'layout_postorder' ) );
		$callback	= JRequest::getCmd('callback', '' );

        $model		= $this->getModel( 'Blog' );
        $rows		= $model->getBlogsBy('', '', $sort , 0 , EBLOG_FILTER_PUBLISHED, null, true);

		if(!count($rows)) { 
			echo $callback ? $callback . '(' . json_encode(array()) . ')' : json_encode(array()); 
			jexit(); 
		}
		
		foreach( $rows as $row )
		{
			$item = EasyBlogHelper::getHelper( 'SimpleSchema' )->mapPost($row, '', 100, array('text'));
			$posts[] = $item;
		}
		
		echo $callback ? $callback . '(' . json_encode($posts) . ')' : json_encode($posts); 
		jexit(); 

	}

}
