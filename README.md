# yii-csdynamicmodel

CSDynamicModel is a model class primarily used to support ad-hoc data validation.

The typical usage of CSDynamicModel is as follows,
```php
public function actionSearch($name, $email) {
    $model = CSDynamicModel::validateData(compact('name', 'email'), array(
        array('name, email', 'length', 'max' => 128),
        array('email', 'email'),
   ));
    if ($model->hasErrors()) {
        // validation fails
    } else {
        // validation succeeds
    }
}
```
The above example shows how to validate `$name` and `$email` with the help of CSDynamicModel. The `validateData()` method creates an instance of CSDynamicModel, defines the attributes using the given data (`name` and `email` in this example), and then calls `Model::validate()`.

You can check the validation result by `hasErrors()`, like you do with a normal model. You may also access the dynamic attributes defined through the model instance, e.g., `$model->name` and `$model->email`.

Alternatively, you may use the following more "classic" syntax to perform ad-hoc data validation:
```php
$model = new CSDynamicModel(compact('name', 'email'));
$model->addRule(array('name, email', 'string', 'max' => 128))
    ->addRule(array('email', 'email'))
    ->validate();
if ($model->hasErrors()) {
    // validation fails
} else {
    // validation succeeds
}
```
CSDynamicModel implements the above ad-hoc data validation feature by supporting the so-called "dynamic attributes". It basically allows an attribute to be defined dynamically through its constructor or `defineAttribute()`.

© 2014 Redguy (http://3e.pl/)
