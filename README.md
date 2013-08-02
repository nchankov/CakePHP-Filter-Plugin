About info - not done yet

## Quick start
* Download or checkout the plugin and place it in your `/app/Plugins` folder.
   * `git clone git@github.com:nchankov/CakePHP-Filter-Plugin.git Filter`
* Ensure the plugin is loaded in app/Config/bootstrap.php by `CakePlugin::load('Filter')` or `CakePlugin::loadAll()`;
* Include the component in your AppController.php: 
   * `public $components = array('Filter.Filter');`
* Add an add form in you index action view (you can modify the fields and form itself on your taste and needs) As example:

```php
<?php echo $this->Form->create('Post');?>
<?php echo $this->Form->input('name');?>
<?php echo $this->Form->end(__('Submit'));?>
```
