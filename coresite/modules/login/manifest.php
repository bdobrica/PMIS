<?php
return (object)(array(
   'slug' => 'login',
   'name' => 'Login',
   'description' => '',
   'roles' => NULL,
   'parent' => NULL,
   'requires' => NULL,
   'order' => NULL,
   'pages' => 
  array (
    0 => 
    (object)(array(
       'name' => 'Register',
       'slug' => 'register-form',
       'parent' => 'login',
       'hidden' => false,
       'order' => '1',
       'roles' => NULL,
    )),
    1 => 
    (object)(array(
       'name' => 'Password Recovery',
       'slug' => 'recover-form',
       'parent' => 'login',
       'hidden' => false,
       'order' => '2',
       'roles' => NULL,
    )),
    2 => 
    (object)(array(
       'name' => 'Login',
       'slug' => 'login-form',
       'parent' => 'login',
       'hidden' => false,
       'order' => 0,
       'roles' => NULL,
    )),
  ),
   'size' => 3,
))
?>