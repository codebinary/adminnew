<?php

namespace ApiBundle\Services;
use Firebase\JWT\JWT;

class JwtAuth{

    public $manager;
    public $key;

    public function __construct($manager){
		$this->manager = $manager;
		$this->key = "clave-secreta";
	}

    // 
    // Método para la validación de login
    // 
    public function signup($email, $password, $getHash = NULL){

        $key = $this->key;

        $user = $this->manager->getRepository("ApiBundle:User")->findOneBy(
            array(
                "email" => $email,
                "password" => $password
            )
        );
        // var_dump($user);
        // exit();
        $signup = false;
        if(is_object($user)){
            $signup = true;
        }
        if($signup == true){
            $token = array(
                "sub" 		=> $user->getId(),
				"email" 	=> $user->getEmail(),
				"name" 		=> $user->getName(),
				"surname" 	=> $user->getSurname(),
				"password" 	=> $user->getPassword(),
				"image" 	=> $user->getImage(),
				"iat" 		=> time(),//Cuando se ha creado el token
				"exp" 		=> time() + (60 * 60)//Fecha de expiración
            );

            $jwt = JWT::encode($token, $key, "HS256");
            $decode = JWT::decode($jwt, $key, array("HS256"));

            if($getHash != null){
                return $jwt;
            }else{
                return $decode;
            }
        }else{
            return array("status" => "error", "data" => "Login failed");
        }
    }

    //Método que recibe el token y lo validará
	public function checkToken($jwt, $getIdentity = false){

		$key = $this->key; 
		$auth = false;
		try{
			$decoded = JWT::decode($jwt, $key, array('HS256'));
		}catch(\UnexpectedValueException $e){

			$auth = false;

		}catch(\DomainException $e){
			$auth = false;
		}
		//Si existe la propiedad entonces el token es correcto
		//(Sub es el id del usuario) pero puede ser cualquier datos del user
		if (isset($decoded->sub)) {
			$auth = true;
		}else{
			$auth = false;
		}
		if ($getIdentity == true) {
			return $decoded;
		}else{
			return $auth;
		}


	}

}