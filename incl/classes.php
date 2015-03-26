<?php if ( !defined('MITH') ) {exit;} ?>
<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class User {

	public $emails;
	public $passwords;
        public $ref;
        public $daten;

        function __construct($emails,$passwords,$referal){
		$this->email=$emails;
		$this->password=md5(md5(trim($passwords)));
                $this->ref=$referal;
                $this->daten=date("Y-m-d H:i:s");
	}

        function validate(){
		if(filter_var($this->email, FILTER_VALIDATE_EMAIL)){
                    $database = new medoo();
                    $reg = $database->query("SELECT id FROM users WHERE email = '".$this->email."'")->fetchAll();

			if(is_numeric($reg['0']['id'])){
                            return FALSE;
			}else{
                            return TRUE;
                        }
                }		
        }

        function randomPassword() {

           $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
           $password = substr( str_shuffle( $chars ), 0, 3 );
           return $password; 

        }
	
	function new_user(){
		if($this->validate()){
                    $database = new medoo();
                    $last_user_id = $database->insert("users", array(
                        "email" => $this->email,
                        "password" => $this->password,
                        "refcode" => $this->randomPassword(),
                        "parent" => $this->ref,
                        "last_log" => "0000-00-00 00:00:00",
                        "reg_log" => $this->daten,
                        "active" => "0"
                        ));
//                                    var_dump($database->error());
//                echo $database->last_query();
                    return $last_user_id;
                    
		}else{
                    $error = "Совпадение";
                    return $error;
		}
	}
        
        function validate_activation(){
		
                    $database = new medoo();
                    $reg = $database->query("SELECT id FROM users WHERE email = '".$this->email."' AND last_log != '0000-00-00 00:00:00' AND active = '1'")->fetchAll();

			if(is_numeric($reg['0']['id'])){
                            return TRUE;
			}else{
                            return FALSE;
                        }
        }

        function validate_pass(){
		
                    $database = new medoo();
                    $reg = $database->query("SELECT id FROM users WHERE email = '".$this->email."' AND password = '".$this->password."'")->fetchAll();

			if(is_numeric($reg['0']['id'])){
                            return TRUE;
			}else{
                            return FALSE;
                        }
        }
        
        function validate_act(){
		
                    $database = new medoo();
                    $reg = $database->query("SELECT id FROM users WHERE email = '".$this->email."' AND last_log = '0000-00-00 00:00:00' AND refcode = '".$this->ref."'")->fetchAll();

			if(is_numeric($reg['0']['id'])){
                            return TRUE;
			}else{
                            return FALSE;
                        }
        }
        
        function validate_user(){
		
                    $database = new medoo();
                    $reg = $database->query("SELECT id FROM users WHERE email = '".$this->email."' AND last_log != '0000-00-00 00:00:00' AND refcode = '".$this->ref."'")->fetchAll();

			if(is_numeric($reg['0']['id'])){
                            return TRUE;
			}else{
                            return FALSE;
                        }
        }

        
        function check_user(){
            if(!$this->validate()){
                if($this->validate_activation()){
                    if($this->validate_pass()){
            
                    $database = new medoo();
                    $reg = $database->query("SELECT id, name, email, refcode FROM users where email = '".$this->email."'")->fetchAll();
                    return $reg;
                                                
                    }else {
                        $error = "Неправильный пароль";
                        return $error;
                    }
                        
                }else{
                        $error = "Неактивирован";
                        return $error;
                }
            }else{
                $error = "Нет в базе";
                return $error;
            }
        }
        
        function activate_user(){
		if(filter_var($this->email, FILTER_VALIDATE_EMAIL)){
                    if($this->validate_act()){
                    $database = new medoo();
                    $database->update("users", array ("last_log" => $this->daten, "active" => "1"), array("email" => $this->email));
                    $reg = $database->query("SELECT name, email, refcode FROM users where email = '".$this->email."'")->fetchAll();
                    return $reg;

                    }else{
                        $error = "Упс";
                        return $error;
                    }
            }
        }
        
        function get_user(){
		if(filter_var($this->email, FILTER_VALIDATE_EMAIL)){
                    if($this->validate_user()){
                    $database = new medoo();
                    $reg = $database->query("SELECT name, email, fio, address, phone, skype, refcode, parent, personal_id, brockern, last_log, reg_log FROM users where email = '".$this->email."'")->fetchAll();
                    return $reg;

                    }else{
                        $error = "Упс";
                        return $error;
                    }
            }
        }
        
        function get_refuser($x){
                    $database = new medoo();
                    $reg = $database->query("SELECT id, personal_id, email FROM users where active = '1' AND parent = '".$x."'")->fetchAll();

                    return $reg;

        }
        
}