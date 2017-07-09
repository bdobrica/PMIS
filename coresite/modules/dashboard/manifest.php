<?php
return (object)(array(
   'slug' => 'dashboard',
   'name' => 'Dashboard',
   'description' => '',
   'roles' => 
  array (
    0 => '*',
  ),
   'parent' => NULL,
   'requires' => NULL,
   'order' => NULL,
   'pages' => 
  array (
    0 => 
    (object)(array(
       'name' => 'Dashboard',
       'slug' => 'dashboard-logout',
       'parent' => 'dashboard',
       'hidden' => true,
       'order' => '9999',
       'roles' => 
      array (
        0 => '*',
      ),
    )),
    1 => 
    (object)(array(
       'name' => 'Dashboard',
       'slug' => 'dashboard-page',
       'parent' => 'dashboard',
       'hidden' => false,
       'order' => 0,
       'roles' => 
      array (
        0 => '*',
      ),
    )),
  ),
   'size' => 1,
   'order' => 0,
))
?>
