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

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

jimport( 'joomla.application.component.view');
jimport( 'simpleschema.category' );
jimport( 'simpleschema.person' );
jimport( 'simpleschema.blog.post' );

class EasyBlogViewCategories extends EasyBlogView
{
	function display( $tmpl = null )
	{
		$config	= EasyBlogHelper::getConfig();
		$callback	= JRequest::getCmd('callback', '' );
		$id			= JRequest::getCmd('id','0');
		$category 	= EasyBlogHelper::getTable( 'Category', 'Table' );
		$category->load($id);

		// private category shouldn't allow to access.
		$privacy	= $category->checkPrivacy();
		
		if(! $privacy->allowed )
		{
			return;
		}
		
		$catIds     = array();
		$catIds[]   = $category->id;
		EasyBlogHelper::accessNestedCategoriesId($category, $catIds);

		$model		= $this->getModel( 'Blog' );
		$rows		= $model->getBlogsBy( 'category' , $catIds );
		
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
