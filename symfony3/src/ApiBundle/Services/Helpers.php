<?php

namespace ApiBundle\Services;

class Helpers{
    public $jwtAuth;

    public function __contructor($jwtAuth){
        $this->jwtAuth = $jwtAuth;
    }

    // 
    // Método que comprueba si el token es correcto o incorrecto
    // 
    public function authCheck($hash, $getIdentity = false){
        $jwtAuth = $this->jwtAuth;
        $auth = false;
        if(!empty($hash)){
            if($getIdentity == false){
                $checkToken = $jwtAuth->checkToken($hash);
                if($checkToken == true){
                    $auth = true;
                } 
            }else{
                $checkToken = $jwtAuth->checkToken($hash, true);
                if(is_object($checkToken)){
                    $auth = $checkToken;
                }
            }
        }
    }

    // 
    // Método que se utiliza para responder en formato json
    // 
    public function json($data){

        $normalizers = array(new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer());
        $encoders = array("json" => new \Symfony\Component\Serializer\Encoder\JsonEncoder());

        $serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);
        $json = $serializer->serialize($data, "json");

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setContent($json);
        $response->headers->set("Content-Type", "application/json");

        return $response;
    }

    // 
    // Response json with status
    //  
    public function response($status, $code, $msg){
        $response = array(
            "status" => $status,
            "code" => $code,
            "message" => $msg
        );

        return $this->json($response);       
    }


}