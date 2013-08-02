<?php
/**
 * Filter component
 * Benefits:
 * 1. Keep the filter criteria in the Session
 * 2. Give ability to customize the search wrapper of the field types

 **
 * @author  Nik Chankov
 * @website http://nik.chankov.net
 * @version 1.0.0
 *
 */

class FilterComponent extends Component {
    /**
     * fields which will replace the regular syntax in where i.e. field = 'value'
     */
    var $fieldFormatting    = array(
                    "string"=>array("%1\$s LIKE", "%2\$s%%"),
                    "text"=>array("%1\$s LIKE", "%2\$s%%"),
                    "integer"=>array("%1\$s LIKE", "%2\$s%%"),
                    "date"=>array("DATE_FORMAT(%1\$s, '%%d.%%m.%%Y')", "%2\$s"),
                    "datetime"=>array("DATE_FORMAT(%1\$s, '%%d.%%m.%%Y')", "%2\$s")
                    );
    /**
     * extra identifier (if needed to specify extra location (like requestAction))
     */
    var $identifier = '';
    
    /**
     * Function which will change controller->data array
     *
     * @param object $controller the class of the controller which call this component
     * @access public
     */
    function process(&$controller){
        $this->prepareFilter($controller);
        $ret = $this->generateCondition($controller, $controller->request->data);
        return $ret;
    }
    
    /**
     * Function which loop the provided data and generate the proper where clause
     * @param object Controller or The model in the controller which has been provided in the post
     * @param array $data data which is posted from the filter
     */
    function generateCondition(&$object, $data=false){
        $ret = array();
        if(isset($data) && is_array($data)){
            //Loop for models
            foreach($data as $model=>$filter){
                if($model == 'OR'){
                    $ret = am($ret, array('OR'=>$this->generateCondition($object, $filter)));
                    unset($data[$model]);
                }
                if(isset($object->{$model}) OR isset($object->{$object->modelClass}->belongsTo[$model])){ //This is object under current object.
                    $columns = (isset($object->{$model})) ? $object->{$model}->getColumnTypes() : $object->{$object->modelClass}->{$model}->getColumnTypes();
                    foreach($filter as $field=>$value){
                        if(is_array($value)){ //Possible that this node is another model
                            if(in_array($field, array_keys($columns))){ //The field is from the model, but it has special formatting
                                if(isset($value['BETWEEN'])){ //BETWEEN case
                                    if(!isset($value['BETWEEN'][0])){
                                        $value['BETWEEN'][0] = '';
                                    }
                                    if(!isset($value['BETWEEN'][1])){
                                        $value['BETWEEN'][1] = '';
                                    }
                                    if($value['BETWEEN'][0] != '' && $value['BETWEEN'][1] != ''){
                                        $ret[$model.'.'.$field.' BETWEEN ? AND ?']=$value['BETWEEN'];
                                    } elseif ($value['BETWEEN'][0] == '' && $value['BETWEEN'][1] != ''){
                                        $ret[$model.'.'.$field.' <=']=$value['BETWEEN'][1];
                                    } elseif ($value['BETWEEN'][0] != '' && $value['BETWEEN'][1] == ''){
                                        $ret[$model.'.'.$field.' >=']=$value['BETWEEN'][0];
                                    }
                                }
                            } else {
                                $ret = am($ret, $this->generateCondition($object->{$model}, array($field=>$value)));
                            }
                            unset($value);
                        } else {
                            if($value != ''){
                                //Trim the value
                                $value=trim($value);
                                if(isset($columns[$field])){ //if the field exist. if not exist probably it's a helper field, so don't use it.
                                    //Check if there are some fieldFormatting set
                                    if(isset($this->fieldFormatting[$columns[$field]])){
                                        $ret[sprintf($this->fieldFormatting[$columns[$field]][0], $model.'.'.$field, $value)] = sprintf($this->fieldFormatting[$columns[$field]][1], $model.'.'.$field, $value);
                                    } else {
                                        $ret[$model.'.'.$field] = $value;
                                    }
                                }
                            }
                        }
                    }
                    //unsetting the empty forms
                    if(count($filter) == 0){
                        unset($object->request->data[$model]);
                    }
                }
            }
        }
        return $ret;
    }
   
    /**
     * function which will take care of the storing the filter data and loading after this from the Session
     * @param object $controller
     * @return void
     */
    protected function prepareFilter(&$controller){
        if(isset($controller->request->data) && $controller->request->data != array()){
            $controller->request->data = $this->setFields($controller->request->data);
            if(isset($controller->request->data['clear'])){ //reset the filter
                $controller->Session->write($controller->name.'.'.$controller->params['action'].$this->identifier, array());
                $controller->request->data = array();
            } else {
                $controller->Session->write($controller->name.'.'.$controller->params['action'].$this->identifier, $controller->request->data);
            }
        }
        $filter = $controller->Session->read($controller->name.'.'.$controller->params['action'].$this->identifier);
        $controller->request->data = $filter;
    }
    
    protected function setFields($data = array()){
        $new = array();
        foreach($data as $k=>$v){
            if(is_array($v)) {
                $new[$k] = $this->setFields($v);
                if($new[$k] == array()){
                    unset($new[$k]);
                }
            } else {
                if($data[$k] != ''){
                    $new[$k] = $v;
                }
            }
        }
        return $new;
    }
}