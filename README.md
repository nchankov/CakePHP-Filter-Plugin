Help you to filter the data in your index actions easily. By using normal CakeForm helper you can filter even complex lists with ease. 
The filter uses session, so even on refresh the data is still filtered.

## Quick start
* Download or checkout the plugin and place it in your `/app/Plugins` folder.
   * `git clone git@github.com:nchankov/CakePHP-Filter-Plugin.git Filter`
* Ensure the plugin is loaded in app/Config/bootstrap.php by `CakePlugin::load('Filter')` or `CakePlugin::loadAll()`;
* Include the component in your AppController.php: 
   * `public $components = array('Filter.Filter');`
* Add an add form in you index action view (you can modify the fields and form itself on your taste and needs) 
    
##Example usage:

```php
/* View/Posts/index.ctp */
<?php echo $this->Form->create('Post');?>
<?php echo $this->Form->input('name');?>
<?php echo $this->Form->end(__('Submit'));?>
```

Add the following code in your controller:

```php
/* Controller/PostsController.php */
class Posts externds AppController {
  public $components = array('Filter.Filter');
  
  public function index(){
    $filter = $this->Filter->process($this); //This make the magic
    $this->set('posts', $this->paginate($filter)); //Here we set $filter as pagination filter
  }
}
```

Assuming that there is a db table `posts` and it has a column `name` by submitting the form it will filter the results.

##Advanced Example Usage

```php
/* In your View/Posts/index.ctp */
<?php echo $this->Form->create('Post');?>
<?php echo $this->Form->input('OR.Post.name');?>
<?php echo $this->Form->end(__('Submit'));?>
```

```php
/* Controller/PostsController.php */
class Posts externds AppController {
  public $components = array('Filter.Filter');
  
  public function index(){
    if(!empty($this->request->data)){
			$this->request->data['OR']['Post']['author'] = $this->request->data['OR']['Posts']['name'];
			$this->request->data['OR']['Post']['slug'] = $this->request->data['OR']['Posts']['slug'];
		}
    $filter = $this->Filter->process($this); //This make the magic
    $this->set('posts', $this->paginate($filter)); //Here we set $filter as pagination filter
  }
}
```
This way with one field you can search in multiple db columns.
