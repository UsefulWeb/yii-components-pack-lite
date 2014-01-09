<?php /**
* 
*/
class ExActiveRecord extends GxActiveRecord
{
	public function __call($name,$parameters)
	{
	    if( $name{0} == '_')
	    {
	    	$attribute = substr($name, 1);
	    	if (array_search( $attribute,$this->attributeNames() )  !== false)
	    	{
	    		return $this->attributeScope($attribute,$parameters);
	    	}
	    	if (in_array($attribute, array('order','having','group','condition','limit')))
	    	{
	    		return $this->specialScope($attribute,$parameters);
	    	}
	    	
	    }
	    return parent::__call($name,$parameters);
	}

	public function primaryKey()
	{
		return $this->tableSchema->primaryKey;
	}
	private function attributeScope($attribute,$parameters)
	{
		$alias = $this->getTableAlias(false,false);
		$condition = "{$alias}.{$attribute} = :{$attribute}";
		$value  = $parameters[0];

		if (is_array($value))
		{
			$condition = "{$alias}.{$attribute} IN (:{$attribute})";
			$value = implode(',',$value);
		}
		
		$params = array(":{$attribute}"=>$value);

		if (is_null($value))
		{
			$condition = "{$alias}.{$attribute} IS NULL";
			$params = null;
		}
		$this->getDbCriteria()->mergeWith(array(
	        'condition'=>$condition,
	        'params'=> $params
	    ));
	    return $this;
	}
	private function specialScope($attribute,$parameters)
	{
		$value  = $parameters[0];
		$this->getDbCriteria()->mergeWith(array(
	        $attribute => $value,
	    ));
	    return $this;
	}

	/**
	 * Saves the current record and its MANY_MANY relations.
	 * This method will save the active record and update
	 * the necessary pivot tables for the MANY_MANY relations.
	 * The pivot table is the table that maps the relationship between two
	 * other tables in a MANY_MANY relation.
	 * This method won't save data on other active record models.
	 * @param array $relatedData The relation data in the format returned by {@link GxController::getRelatedData}.
	 * @param boolean $runValidation Whether to perform validation before saving the record.
	 * If the validation fails, the record will not be saved to database. This applies to all (including related) models.
	 * This does not apply for related models when in batch mode. This does not apply for deletes.
	 * @param array $attributes List of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved. This applies only to the main model.
	 * @param array $options Additional options. Valid options are:
	 * <ul>
	 * <li>'withTransaction', boolean: Whether to use a transaction.</li>
	 * <li>'batch', boolean: Whether to try to do the deletes and inserts in batch.
	 * While batches may be faster, using active record instances provides better control, validation, event support etc.
	 * Batch is only supported for deletes.</li>
	 * </ul>
	 * @return boolean Whether the saving succeeds.
	 * @see pivotModels
	 */
	public function saveWithRelated($relatedData, $runValidation = true, $attributes = null, $options = array()) {
		// Merge the specified options with the default options.
		$options = array_merge(
						// The default options.
						array(
							'withTransaction' => true,
							'batch' => true,
						)
						,
						// The specified options.
						$options
		);

		try {
			// Start the transaction if required.
			if ($options['withTransaction'] && ($this->getDbConnection()->getCurrentTransaction() === null)) {
				$transacted = true;
				$transaction = $this->getDbConnection()->beginTransaction();
			} else
				$transacted = false;

			// Save the main model.
			if (!$this->save($runValidation, $attributes)) {
				if ($transacted)
					$transaction->rollback();
				return false;
			}

			// If there is related data, call saveRelated.
			if (!empty($relatedData)) {
				if (!$this->saveRelated($relatedData, $runValidation, $options['batch'])) {
					if ($transacted)
						$transaction->rollback();
					return false;
				}
			}

			// If transacted, commit the transaction.
			if ($transacted)
				$transaction->commit();
		} catch (Exception $ex) {
			// If there is an exception, roll back the transaction...
			if ($transacted)
				$transaction->rollback();
			// ... and rethrow the exception.
			throw $ex;
		}
		
		$this->afterSaveWithRelated(new CEvent($this));

		return true;
	}

	public function onAfterSaveWithRelated($event)
	{
		$this->raiseEvent('onAfterSaveWithRelated', $event);
	}

	protected function afterSaveWithRelated($event)
	{
		if($this->hasEventHandler('onAfterSaveWithRelated'))
			$this->onAfterSaveWithRelated($event);
	}

	/**
	 * Saves the MANY_MANY relations of this record.
	 * Internally used by {@link saveWithRelated} and {@link saveMultiple}.
	 * See {@link saveWithRelated} and {@link saveMultiple} for details.
	 * @param array $relatedData The relation data in the format returned by {@link GxController::getRelatedData}.
	 * @param boolean $runValidation Whether to perform validation before saving the record.
	 * @param boolean $batch Whether to try to do the deletes and inserts in batch.
	 * While batches may be faster, using active record instances provides better control, validation, event support etc.
	 * Batch is only supported for deletes.
	 * @return boolean Whether the saving succeeds.
	 * @see saveWithRelated
	 * @see saveMultiple
	 * @throws CDbException If this record is new.
	 * @throws Exception If this active record has composite PK.
	 */
	protected function saveRelated($relatedData, $runValidation = true, $batch = true) {
		if (empty($relatedData))
			return true;

		// This active record can't be new for the method to work correctly.
		if ($this->getIsNewRecord())
			throw new CDbException(Yii::t('giix', 'Cannot save the related records to the database because the main record is new.'));
		
		$modelRelations = $this->relations();
		$pivotClassNames = $this->pivotModels();
		// Save each related data.
		foreach ($relatedData as $relationName => $relationData) {
			
			if ($modelRelations[$relationName][0] === self::MANY_MANY || isset($pivotClassNames[$relationName]))
			{
				$this->saveManyManyRelation($relationName, $relationData, $runValidation, $batch);
			}	
			else if ($modelRelations[$relationName][0] === self::HAS_ONE || $modelRelations[$relationName][0] === self::HAS_MANY)
			{
				$this->saveHasOneOrManyRelation($relationData, $modelRelations[$relationName][1],$modelRelations[$relationName][2]);
			}
			
			
		} // This is the end of the loop "save each related data".

		return true;
	}
	public function saveHasOneOrManyRelation($relationData, $relatedModelName, $attribute)
	{
		if (is_array($relationData))
		{
			foreach ($relationData as $data) 
			{
				if (is_object($data))
				{
					$data->{$attribute} = $this->primaryKey;
					$data->save();
				}
				else
				{
					
				}
			}
		}	else 	{
			$relationData->{$attribute} = $this->primaryKey;
			$relationData->save();
		}
		
	}
	public function saveManyManyRelation($relationName, $relationData, $runValidation = true, $batch = true)
	{
		// The pivot model class name.
			$pivotClassNames = $this->pivotModels();
			$pivotClassName = $pivotClassNames[$relationName];
			$pivotModelStatic = GxActiveRecord::model($pivotClassName);
			// Get the foreign key names for the models.
			$activeRelation = $this->getActiveRelation($relationName);
			$relatedClassName = $activeRelation->className;
			if (preg_match('/(.+)\((.+),\s*(.+)\)/', $activeRelation->foreignKey, $matches)) {
				// By convention, the first fk is for this model, the second is for the related model.
				$thisFkName = $matches[2];
				$relatedFkName = $matches[3];
			}
			// Get the primary key value of the main model.
			$thisPkValue = $this->getPrimaryKey();
			if (is_array($thisPkValue))
				throw new Exception(Yii::t('giix', 'Composite primary keys are not supported.'));
			// Get the current related models of this relation and map the current related primary keys.
			$currentRelation = $pivotModelStatic->findAll(new CDbCriteria(array(
								'select' => $relatedFkName,
								'condition' => "$thisFkName = :thisfkvalue",
								'params' => array(':thisfkvalue' => $thisPkValue),
							)));
			$currentMap = array();
			foreach ($currentRelation as $currentRelModel) {
				$currentMap[] = $currentRelModel->$relatedFkName;
			}
			// Compare the current map to the new data and identify what is to be kept, deleted or inserted.
			$newMap = $relationData;
			$deleteMap = array();
			$insertMap = array();
			if ($newMap !== null) {
				// Identify the relations to be deleted.
				foreach ($currentMap as $currentItem) {
					if (!in_array($currentItem, $newMap))
						$deleteMap[] = $currentItem;
				}
				// Identify the relations to be inserted.
				foreach ($newMap as $newItem) {
					if (!in_array($newItem, $currentMap))
						$insertMap[] = $newItem;
				}
			} else // If the new data is empty, everything must be deleted.
				$deleteMap = $currentMap;
			// If nothing changed, we simply continue the loop.
			if (empty($deleteMap) && empty($insertMap))
				return;
			// Now act inserting and deleting the related data: first prepare the data.
			// Inject the foreign key names of both models and the primary key value of the main model in the maps.
			foreach ($deleteMap as &$deleteMapPkValue)
				$deleteMapPkValue = array_merge(array($relatedFkName => $deleteMapPkValue), array($thisFkName => $thisPkValue));
			unset($deleteMapPkValue); // Clear reference;
			foreach ($insertMap as &$insertMapPkValue)
				$insertMapPkValue = array_merge(array($relatedFkName => $insertMapPkValue), array($thisFkName => $thisPkValue));
			unset($insertMapPkValue); // Clear reference;
			// Now act inserting and deleting the related data: then execute the changes.
			// Delete the data.
			if (!empty($deleteMap)) {
				if ($batch) {
					// Delete in batch mode.
					if ($pivotModelStatic->deleteByPk($deleteMap) !== count($deleteMap)) {
						return false;
					}
				} else {
					// Delete one active record at a time.
					foreach ($deleteMap as $value) {
						$pivotModel = GxActiveRecord::model($pivotClassName)->findByPk($value);
						if (!$pivotModel->delete()) {
							return false;
						}
					}
				}
			}
			// Insert the new data.
			foreach ($insertMap as $value) {
				$pivotModel = new $pivotClassName();
				$pivotModel->setAttributes($value);
				if (!$pivotModel->save($runValidation)) {
					return false;
				}
			}
	}
    /**
     * This method behaves like a destructor.
     * It frees memory allocated to
     * an object which calls it.
     * 
     * 
     * This function can be called like
     * $item = new Model;
     * $item->destruct();
     * 
     * "Destructor"
     */
    public function destruct() 
    {
        $fields1 = $this->relations();
        $fields2 = $this->attributeLabels();

        foreach($fields1 as $key=>$row)
        {
            unset($this->$key);
        }

        foreach($fields2 as $key=>$row)
        {
            unset($this->$key);
        }
    }
	
	public function __destruct()
	{
		$this->destruct();
	}
	
    /*
	 *`This method search model by Attributes
	 *` If result is null, required model will be created
    */
    public function findByAttributesOrCreate($attributes,$condition='',$params=array())
    {
    	$model = $this->findByAttributes($attributes,$condition,$params);
    	
    	if (!is_null($model))
    	{
    		return $model;
    	}
    	else
    	{
			return $this->createByAttributes($attributes);
    		/*$class = get_class($this);
			$model = new $class;
			$model->attributes = $attributes;
			if ($model->save())
			{
				return $model;
			}*/
    	}
    	
    }

    public static function getClassByTableName($tableName)
	{

		$tableName=self::removePrefix($tableName,false);
		if(($pos=strpos($tableName,'.'))!==false) // remove schema part (e.g. remove 'public2.' from 'public2.post')
			$tableName=substr($tableName,$pos+1);
		$className='';
		foreach(explode('_',$tableName) as $name)
		{
			if($name!=='')
				$className.=ucfirst($name);
		}
		return $className;
	}
	
	public static function removePrefix($tableName,$addBrackets=true)
	{	
		$db = Yii::app()->db;

		if($addBrackets && $db->tablePrefix=='')
			return $tableName;
		$prefix=$db->tablePrefix!='' ? $db->tablePrefix : $db->tablePrefix;
		if($prefix!='')
		{
			if($addBrackets && $db->tablePrefix!='')
			{
				$prefix=$db->tablePrefix;
				$lb='{{';
				$rb='}}';
			}
			else
				$lb=$rb='';
			if(($pos=strrpos($tableName,'.'))!==false)
			{
				$schema=substr($tableName,0,$pos);
				$name=substr($tableName,$pos+1);
				if(strpos($name,$prefix)===0)
					return $schema.'.'.$lb.substr($name,strlen($prefix)).$rb;
			}
			elseif(strpos($tableName,$prefix)===0)
				return $lb.substr($tableName,strlen($prefix)).$rb;
		}
		return $tableName;
	}
	/**
	 * This function tries to create a
	 * new row in some table filling it
	 * with given $attributes param
	 * 
	 * If success - it will return model object
	 * If fail - it will return false
	 * 
	 * USAGE
	 * E.g. you have "user" table
	 * id
	 * username
	 * password
	 * gender
	 * 
	 * You should pass $attributes param like
	 * $attributes = array(
	 *		'username' => $username_value,
	 *		'password' => $password_value,
	 *		'gender' => $gender_value
	 * );
	 * 
	 * User::model()->createByAttributes($attributes);
	 * 
	 * @param associative array $attributes
	 * @return object|false
	 */
	public function createByAttributes($attributes = array())
	{
		$class = get_class($this);
		$model = new $class;
		$model->attributes = $attributes;
		if ($model->save())
		{
			return $model;
		}
		else
		{
			return false;
		}
	}

	public function behaviors()
	{
		$behaviors = array();

		if (isset(Yii::app()->activeRecord))
		{
			$behaviors = Yii::app()->activeRecord->behaviors;
		}

		return array_merge(parent::behaviors(),$behaviors);
	}

	public function relations()
	{
		$relations = array();

		if (isset(Yii::app()->activeRecord))
		{
			$relations = Yii::app()->activeRecord->relations;
		}

		return array_merge(parent::relations(),$relations);
	}

	public function pivotModels()
	{
		$pivotModels = array();

		if (isset(Yii::app()->activeRecord))
		{
			$pivotModels = Yii::app()->activeRecord->pivotModels;
		}

		return array_merge(parent::pivotModels(),$pivotModels);
	}

	public function rules()
	{
		$rules = array();

		if (isset(Yii::app()->activeRecord))
		{
			$rules = Yii::app()->activeRecord->rules;
		}

		return array_merge(parent::rules(),$rules);
	}
} 
?>