<?php

/**
 * Port of DynamicModel from Yii2
 *
 * @author Redguy
 * @link http://3e.pl/
 * @copyright redguy
 * @license http://www.yiiframework.com/license/
 * @version 1.0
 *
 */

Yii::import('system.validators.CValidator');

/**
 * CSDynamicModel is a model class primarily used to support ad-hoc data validation.
 *
 * The typical usage of CSDynamicModel is as follows,
 *
 * ```php
 * public function actionSearch($name, $email) {
 *     $model = CSDynamicModel::validateData(compact('name', 'email'), array(
 *         array('name, email', 'length', 'max' => 128),
 *         array('email', 'email'),
 *    ));
 *     if ($model->hasErrors()) {
 *         // validation fails
 *     } else {
 *         // validation succeeds
 *     }
 * }
 * ```
 *
 * The above example shows how to validate `$name` and `$email` with the help of CSDynamicModel.
 * The [[validateData()]] method creates an instance of CSDynamicModel, defines the attributes
 * using the given data (`name` and `email` in this example), and then calls [[Model::validate()]].
 *
 * You can check the validation result by [[hasErrors()]], like you do with a normal model.
 * You may also access the dynamic attributes defined through the model instance, e.g.,
 * `$model->name` and `$model->email`.
 *
 * Alternatively, you may use the following more "classic" syntax to perform ad-hoc data validation:
 *
 * ```php
 * $model = new CSDynamicModel(compact('name', 'email'));
 * $model->addRule(array('name, email', 'string', 'max' => 128))
 *     ->addRule(array('email', 'email'))
 *     ->validate();
 * if ($model->hasErrors()) {
 *     // validation fails
 * } else {
 *     // validation succeeds
 * }
 * ```
 *
 * CSDynamicModel implements the above ad-hoc data validation feature by supporting the so-called
 * "dynamic attributes". It basically allows an attribute to be defined dynamically through its constructor
 * or [[defineAttribute()]].
 */
class CSDynamicModel extends CModel
{
    private $_attributes = array();
    private $_validators;

    /**
     * Constructors.
     *
     * @param array $attributes the dynamic attributes (name-value pairs, or names) being defined
     * @param array $config the configuration array to be applied to this object.
     */
    public function __construct($attributes = array(), $config = array())
    {
        $this->_validators = new CList();
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        foreach ($attributes as $name => $value) {
            if (is_integer($name)) {
                $this->_attributes[$value] = null;
            } else {
                $this->_attributes[$name] = $value;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        } else {
            return parent::__get($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->_attributes)) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return isset($this->_attributes[$name]);
        } else {
            return parent::__isset($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __unset($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            unset($this->_attributes[$name]);
        } else {
            parent::__unset($name);
        }
    }

    /**
     * Defines an attribute.
     *
     * @param string $name the attribute name
     * @param mixed $value the attribute value
     */
    public function defineAttribute($name, $value = null)
    {
        $this->_attributes[$name] = $value;
    }

    /**
     * Undefines an attribute.
     *
     * @param string $name the attribute name
     */
    public function undefineAttribute($name)
    {
        unset($this->_attributes[$name]);
    }

    /**
     * Adds a validation rule to this model.
     * You can also directly manipulate [[validators]] to add or remove validation rules.
     * This method provides a shortcut.
     *
     * @param mixed $rule rule definition or Validator descendant object
     * @return static the model itself
     */
    public function addRule($rule)
    {
        if ($rule instanceof CValidator) {
            $this->_validators->add($rule);
        } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
            $validator = CValidator::createValidator($rule[1], $this, $rule[0], array_slice($rule, 2));
            $this->_validators->add($validator);
        } else {
            throw new CException('Invalid validation rule: a rule must specify both attribute names and validator type.');
        }

        return $this;
    }

    /**
     * Validates the given data with the specified validation rules.
     * This method will create a DynamicModel instance, populate it with the data to be validated,
     * create the specified validation rules, and then validate the data using these rules.
     *
     * @param array $data the data (name-value pairs) to be validated
     * @param array $rules the validation rules. Please refer to [[Model::rules()]] on the format of this parameter.
     * @return static the model instance that contains the data being validated
     * @throws InvalidConfigException if a validation rule is not specified correctly.
     */
    public static function validateData($data, $rules = array())
    {
        /** @var DynamicModel $model */
        $model = new CSDynamicModel($data);
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                $model->addRule($rule);
            }

            $model->validate();
        }

        return $model;
    }

    public function attributeNames()
    {
        return array_keys($this->_attributes);
    }

    /** override */
    public function createValidators()
    {
        return $this->_validators->toArray();
    }

    public function getValidators($attribute = null)
    {
        $allValidators = $this->_validators->getIterator();

        $validators = array();
        $scenario = $this->getScenario();
        foreach ($allValidators as $validator) {
            if ($validator->applyTo($scenario)) {
                if ($attribute === null || in_array($attribute, $validator->attributes, true)) {
                    $validators[] = $validator;
                }
            }
        }

        return $validators;
    }
}
