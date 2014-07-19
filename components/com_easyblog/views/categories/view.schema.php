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
			
			$item = $this->map($row);
			$posts[] = $item;

		}

		echo $callback ? $callback . '(' . json_encode($posts) . ')' : json_encode($posts); 
		jexit(); 

	}
	
	// @TODO - This needs to be moved to an easysocial helper
	public function map($row) {
		
		$config	= EasyBlogHelper::getConfig();		
		$blog 	= EasyBlogHelper::getTable( 'Blog' );
		$blog->load( $row->id );

		$user   = JFactory::getUser($row->created_by);
		$profile = EasyBlogHelper::getTable( 'Profile', 'Table' );
		$profile->load( $user->id );

		$blog->author = $profile;
		$created			= EasyBlogDateHelper::dateWithOffSet($row->created);
		$formatDate         = true;
		if(EasyBlogHelper::getJoomlaVersion() >= '1.6')
		{
			$langCode   = EasyBlogStringHelper::getLangCode();
			if($langCode != 'en-GB' || $langCode != 'en-US')
				$formatDate = false;
		}
		$blog->created       = $created->toMySQL();
		if( $config->get( 'main_rss_content' ) == 'introtext' )
		{
			$blog->text			= ( !empty( $row->intro ) ) ? $row->intro : $row->content;
		}
		else
		{
			$blog->text			= $row->intro . $row->content;
		   
		}

		$blog->text         = EasyBlogHelper::getHelper( 'Videos' )->strip( $blog->text );
		$blog->text			= EasyBlogGoogleAdsense::stripAdsenseCode( $blog->text );
		
		$category	= EasyBlogHelper::getTable( 'Category', 'Table' );
		$category->load( $row->category_id );
		$blog->category = $category;
		
		$item	= new PostSimpleSchema;
		$pos 						= JString::strpos(strip_tags($blog->text), ' ', 100);
		$image_data					= json_decode($blog->image);
		
		$item->postid 				= $blog->id;
		$item->title 				= $this->escape($blog->title);
		$item->text 				= $blog->text;
		$item->textplain 			= JString::substr(strip_tags($blog->text), 0, $pos);
		$item->image				= $blog->getImage();
		$item->image->url			= $image_data->url;
		$item->created_date 		= $blog->created;
		
		$item->author->name 		= $blog->author->nickname;
		$item->author->photo 		= $blog->author->avatar;
		
		$item->category->categoryid	= $blog->category->id;
		$item->category->title		= $blog->category->title;

		return $item;
		
	}	
}
