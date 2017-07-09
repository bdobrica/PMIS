<?php
return (object)(array(
   'slug' => 'team',
   'name' => 'Teams',
   'description' => 'The place where you can manage your teams.',
   'roles' => 
  array (
    0 => '*',
  ),
   'parent' => NULL,
   'requires' => NULL,
   'order' => '2',
   'pages' => 
  array (
    0 => 
    (object)(array(
       'name' => 'Teams',
       'description' => '',
       'slug' => 'teams-page',
       'parent' => 'team',
       'hidden' => false,
       'order' => 0,
       'roles' => 
      array (
        0 => '*',
      ),
    )),
  ),
   'size' => 1,
))
?>