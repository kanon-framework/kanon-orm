<?php
#require_once dirname(__FILE__).'/../modelBehavior.php';
class commentable extends modelBehavior{
	protected $_properties = array(
		'commentsCount' => array(
			'class'=>'integerProperty',
			'field'=>'comments_count',
	),
	);
	protected $___methods = array(
		'getCommentsCollection',
		'getComments',
	);
	protected function _getCommentClass(){
		return $this->_modelName.'Comment';
	}
	/**
	 *
	 * @param model $model
	 */
	public function setUp($model){
		parent::setUp($model);
		if (!class_exists($this->_getCommentClass())){
			create_class($this->_getCommentClass(), 'commentPrototype'); // PHP 5 >= 5.3.0
			//eval('class c extends a{}');
			$tableName = $model->getCollection()->getTableName().'_comment'; // (s)
			$model->getStorage()->registerCollection($this->_getCommentClass(), $tableName);
		}
	}
	public function getCommentsCollection(){
		return modelCollection::getInstance($this->_getCommentClass());
	}
	/**
	 *
	 * @param model $model
	 * @return modelResultSet
	 */
	public function getComments($model){
		$comments = $this->getCommentsCollection();
		return $comments->select($comments->parentId->is($model->getPrimaryKeyValue()));
	}
}