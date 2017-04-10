<?php 

namespace ApiBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use ApiBundle\Entity\User;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;


class UserController extends Controller{

    // 
    // Login
    // 
    public function loginAction(Request $request){

        $helpers = $this->get("api.helpers");
        $jwt_auth = $this->get("api.jwt_auth");

        $json   = $request->get('json', null);

        if($json != null){
            $params = json_decode($json);
            //Obtenemos los valores email y password
            $email = (isset($params->email) ? $params->email : null);
            $password = (isset($params->password) ? $params->password : null);
            $gethash = (isset($params->gethash) ? $params->gethash : null);
            //Restricción de email
            $emailContraint = new Assert\Email();
            $validateEmail = $this->get("validator")->validate($email, $emailContraint);
            //Ciframos la contraseña
            $pwd = hash('sha256', $password);
            if (!empty($validateEmail) && !empty($password)) {
            //     var_dump($params);
            // exit();
                if ($gethash == null) {
                    $signup = $jwt_auth->signup($email, $pwd);
                }else{
                    $signup = $jwt_auth->signup($email, $pwd, "hash");
                }
                return new JsonResponse($signup);
            }   
        }
    }

    // 
    // Lists all Users entities.
    // 
    public function indexAction(Request $request){

        $helpers = $this->get('api.helpers');

        $em = $this->getDoctrine()->getManager();
        //$queryBuilder = $em->getRepository("ApiBundle:User")->createQueryBuilder('u');
        $dql = "SELECT u FROM ApiBundle:User u ORDER BY u.id DESC";
        $query = $em->createQuery($dql);
        $data = $query->getResult();

        return $helpers->json($data);
        // var_dump($query);
        // exit();
    }

    // 
    // create new Users entity
    // 
    public function newAction(Request $request){

        $helpers = $this->get('api.helpers');

        $json = $request->get('json', null);
        $params = json_decode($json);

        if($json != null){
            $createdAt = new \DateTime("now");
            $image = null;
            //$role = "ROLE_USER";

            $email      = (isset($params->email)) ? $params->email : null;
            $password   = (isset($params->password)) ? $params->password : null;
            $name       = (isset($params->name)) ? $params->name : null;
            $surname    = (isset($params->surname)) ? $params->surname : null;
            $role       = (isset($params->role)) ? $params->role : null;

            //Validamos el email
            $emailContraint = new Assert\Email();
            $validateEmail = $this->get('validator')->validate($email, $emailContraint);

            if( !empty($email) && !empty($validateEmail) && 
                !empty($password) && !empty($name) && 
                !empty($surname) && !empty($role) ){
                    //Creamos el usuario
                    $user = new User();
                    $user->setCreatedAt($createdAt);
                    $user->setImage($image);
                    $user->setRole($role);
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);
                    //Cifrar password
                    $pass = hash('sha256', $password);
                    $user->setPassword($pass);

                    $em = $this->getDoctrine()->getManager();
                    // Buscamos el email para saber que no está y guardarlo
                    $issetUser = $em->getRepository("ApiBundle:User")->findBy(
                        array(
                            "email" => $email
                        )
                    );
                    if(count($issetUser) == 0){
                        $em->persist($user);
                        $em->flush();                                            
                        return $helpers->response("success", 200, "New user created");
                    }else{
                        return $helpers->response("error", 400, "User not created, duplicated");   
                    }
            }
        }else{
          return $helpers->response("error", 422, "Unprocessable entity");  
        }
    }

    // 
    // Método que editara un users
    // 
    public function editAction(Request $request){

        $helpers = $this->get('api.helpers');

        $json = $request->get("json", null);
        $params = json_decode($json);

        if($json != null){

            $createdAt = new \DateTime('now');
            $image = null;
            $role = "ROLER_USER";

            $id         = (isset($params->id)) ? $params->id : null;
            $email      = (isset($params->email)) ? $params->email : null;
            $password   = (isset($params->password)) ? $params->password : null;
            $name       = (isset($params->name)) ? $params->name : null;
            $role       = (isset($params->role)) ? $params->role : null;
            $surname    = (isset($params->surname)) ? $params->surname : null;

            $emailContraint = new Assert\Email();
            $validateEmail = $this->get('validator')->validate($email, $emailContraint);

            if(!empty($email) && !empty($password) &&
               !empty($name) && !empty($surname) && !empty($id)){

                    $em = $this->getDoctrine()->getManager();
                    //Obtenemos el usuario a editar
                    $user = $em->getRepository("ApiBundle:User")->findOneBy(
                        array(
                            "id" => $id
                        )
                    );

                    $user->setCreatedAt($createdAt);
				    $user->setImage($image);
					$user->setRole($role);
					$user->setEmail($email);
					$user->setName($name);
					$user->setSurname($surname);
                   
                    $pass = hash('sha256', $password);
                    $user->setPassword($pass);
                    // var_dump($role);
                    // exit();
                    $em->persist($user);
                    $em->flush($user);
                    return $helpers->response("success", 200, "User updated created");
                }else{
                    return $helpers->response("error", 422, "Unprocessable entity");    
                }
            }else{
                return $helpers->response("error", 422, "Unprocessable entity");
            }
    }


    // 
    // Eliminar un usuario
    // 
    public function deleteAction($id){

        $helpers = $this->get('api.helpers');

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("ApiBundle:User")->findOneBy(
            array(
                "id" => $id
            )
        );
        if(isset($user)){
            $em->remove($user);
            $em->flush($user);
            return $helpers->response("success", 200, "User deleted success");
        }else{
            return $helpers->response("error", 404, "User not deleted");
        }
    }


    // 
    // Método para exportar a excel
    // 
    public function exportAction(){

        $em = $this->getDoctrine()->getManager();
        // recuperamos los elementos de base de datos que queremos exportar
        $query = $em->getRepository('ApiBundle:User')
            ->createQueryBuilder('e')
            ->getQuery();
        $result = $query->getResult();

        // solicitamos el servicio 'phpexcel' y creamos el objeto vacío...
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        // ...y le asignamos una serie de propiedades
        $phpExcelObject->getProperties()
            ->setCreator("Vertice")
            ->setLastModifiedBy("Vertice")
            ->setTitle("Ejemplo de exportación")
            ->setSubject("Ejemplo")
            ->setDescription("Listado de Usuarios.")
            ->setKeywords("Vertice exportar excel ejemplo");
        
        // establecemos como hoja activa la primera, y le asignamos un título
        $phpExcelObject->setActiveSheetIndex(0);
        $phpExcelObject->getActiveSheet()->setTitle('Ejemplo de exportación');

        // escribimos en distintas celdas del documento el título de los campos que vamos a exportar
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('B2', 'Nombre')
            ->setCellValue('C2', 'Apellido')
            ->setCellValue('D2', 'Rol')
            ->setCellValue('E2', 'Email')
            ->setCellValue('F2', 'Fecha de creación');
        
        // fijamos un ancho a las distintas columnas
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('B')
            ->setWidth(30);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('C')
            ->setWidth(25);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('D')
            ->setWidth(15);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('E')
            ->setWidth(20);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('F')
            ->setWidth(20);
        
        // recorremos los registros obtenidos de la consulta a base de datos escribiéndolos en las celdas correspondientes
        $row = 3;
        foreach ($result as $item) {
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('B'.$row, $item->getName())
                ->setCellValue('C'.$row, $item->getSurname())
                ->setCellValue('D'.$row, $item->getRole())
                ->setCellValue('E'.$row, $item->getEmail())
                ->setCellValue('F'.$row, $item->getCreatedAt()->format('d-M-Y'));
            $row++;
        }

        // se crea el writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // se crea el response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // y por último se añaden las cabeceras
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'ejemplo.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;

    }

    



}

