kanon-orm
=========
MySQL (mysql/pdo) ORM, utf-8

Some features:
* Multiple database connection
* Multiple classes per table
* Multi-field PRIMARY KEY
* Automatic field/table creation (CREATE/ALTER TABLE)
* Updating only changed fields
* Extendable via database drivers


DB connection

```php
<?php
modelStorage::getInstance()->connect($dsn, $username = 'root', $password = '', $charset = 'UTF8');
// for $dsn check PDO documentation about DSN
```

Model definition

```php
<?php
class myModel extends model{
  protected $_properties = array(
		'id' => array(
			'class' => 'integerProperty',
			'field' => 'id',
      'primaryKey' => true
		),
		'title' => array(
			'class' => 'stringProperty',
			'field' => 'title',
			'titleKey' => true
		),
		'isFavorite' => array(
			'class' => 'booleanProperty',
			'field' => 'is_favorite'
		)
	);
	protected $_actAs = array(
		'timestampable'
	);
}
```

```php
<?php
modelStorage::getInstance()->register('myModel', 'myTable');
```

Some SQL examples

```php
<?php
$myModels = myModel::getCollection();
foreach ($myModels
    ->select(
      $myModels // collection
        ->isFavorite // field
        ->is(1), // expression
      $myModels->createdAt->gte(time()-24*60*60)
    ) as $myModel){
}
$myModel = myModel::findOne(1); // by primaryKey
```

Using model
```php
<?php
echo $model->id; // $model->id->__toString()/$model->id->getValue()
$model->title = 'Hello';
$model->save();
```
