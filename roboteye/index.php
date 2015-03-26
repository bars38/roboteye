<?php if ( !defined('MITH') ) {exit;} ?>
<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$loader = new Twig_Loader_Filesystem('templates/backend/');
$twig = new Twig_Environment($loader); 


if ($_SESSION['id']){
    print "ok".$_SESSION['id'];

    
}else{
    
    if (Flight::request()->method == "POST"){
        
        $forms = Flight::get('forms');
        echo "POST".$forms['email'];
        
    }
  
    try {


        $template = $twig->loadTemplate('login.html');

        echo $template->render(array (
            'name' => 'Alex',
            'rpath' => Flight::get('rpath'),
            'site' => Flight::get('site')
          ));

    } catch (Exception $e) {

        die ('ERROR: ' . $e->getMessage());

    }
    
}