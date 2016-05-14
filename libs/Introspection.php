<?php



class Introspection {

    public function __construct(){

}

    /**
     * getCallableMethods
     * this is a function to list the methods which are unique to the class
     * @param $object
     * @return array
     */
    static function getCallableMethods($object)
    {
        $methods = get_class_methods(get_class($object));
        if (get_parent_class($object)) {
            $parent_methods = get_class_methods(get_parent_class($object));
            $methods = array_diff($methods, $parent_methods);
        }
        return $methods;
    }


    /**
     * getInheritedMethods
     * this is a function to list the inherited methods of a child class
     * @param $object
     *@return return an array of inherited methods
     */
    static function getInheritedMethods($object)
    {
        $methods = get_class_methods(get_class($object));
        if (get_parent_class($object)) {
            $parentMethods = get_class_methods(get_parent_class($object));
            $methods = array_intersect($methods, $parentMethods);
        }
        return $methods;
    }

    /**
     * @param $object
     * @return array an array of properties which belong to the specified class
     */
    function getClassProperties($object){
       return $properties = get_class_vars(get_class($object));
    }


    /**
     * function to find the properties that are assigned to an instance of a class
     * @param $object Object the instance object
     * @return array an array of properties which belong to an instance of a class
     */
    public static function getObjectProperties($object)
    {
        $vars = get_object_vars($object);

        if (get_parent_class($object)) {
            $parent = get_parent_class($object);
            $parent_vars = get_object_vars(new $parent);
            foreach ($vars as $key=>$value) {
                if(array_key_exists($key,$parent_vars)){
                    unset($vars[$key]);
                }
            }
        }
        return $vars;
    }



    /**
     * getLineage
     * @param $object
     * @return return an array of superclasses
     */
    static function getLineage($object)
    {
        if (get_parent_class($object)) {
            $parent = get_parent_class($object);
            $parentObject = new $parent;
            $lineage = getLineage($parentObject);
            $lineage[] = get_class($object);
        }
        else {
            $lineage = array(get_class($object));
        }
        return $lineage;
    }


    /**
     * getChildClasses
     * @param $object
     * @return return an array of subclasses
     */
    static function getChildClasses($object)
    {
        $classes = get_declared_classes();
        $children = array();
        foreach ($classes as $class) {
            if (substr($class, 0, 2) == '__') {
                continue;
            }
            $child = new $class;
            if (get_parent_class($child) == get_class($object)) {
                $children[] = $class;
            }
        }
        return $children;
    }




}