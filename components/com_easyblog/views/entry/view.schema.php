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

		$user   = JFactory::getUser($blog->created_by);
		$profile = EasyBlogHelper::getTable( 'Profile', 'Table' );
		$profile->load( $user->id );

		$blog->author = $profile;
		$created			= EasyBlogDateHelper::dateWithOffSet($blog->created);
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
			$blog->text			= ( !empty( $blog->intro ) ) ? $blog->intro : $blog->content;
		}
		else
		{
			$blog->text			= $blog->intro . $blog->content;
		   
		}

		$blog->text         = EasyBlogHelper::getHelper( 'Videos' )->strip( $blog->text );
		$blog->text			= EasyBlogGoogleAdsense::stripAdsenseCode( $blog->text );
		
		$category	= EasyBlogHelper::getTable( 'Category', 'Table' );
		$category->load( $blog->category_id );
		$blog->category = $category;
		
		$item = $this->map($blog);
		
		echo $callback ? $callback . '(' . json_encode($item) . ')' : json_encode($item); 
		jexit(); 

	}
	
	// @TODO - This needs to be moved to an easysocial helper
	public function map($blog) {

		$item	= new PostSimpleSchema;
		$image_data 				= json_decode($blog->image);
		
		$item->postid 				= $blog->id;
		$item->title 				= $blog->title;
		$item->text 				= $blog->text;
		$item->textplain 			= strip_tags($blog->text, '<p><br><pre><a><blockquote><strong>');
		$item->image->url			= $image_data->url;
		$item->image->variations	= $blog->getImage();
		$item->created_date 		= EasyBlogDateHelper::getLapsedTime($blog->created);
		
		$item->author->name 		= $blog->author->nickname;
		$item->author->photo 		= $blog->author->avatar;
		
		$item->category->categoryid	= $blog->category->id;
		$item->category->title		= $blog->category->title;

		return $item;
		
	}
}
