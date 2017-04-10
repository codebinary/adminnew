<?php 

namespace ApiBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ContactoController extends Controller{

    // 
    // Listar todos los datos
    // 
    public function indexAction(){
        $helpers = $this->get('api.helpers');
        $em = $this->getDoctrine()->getManager();
        $contactos = $em->getRepository("ApiBundle:Contacto")->findAll(
            array(),
            array(
                "id" => "DESC"
            )
        );
        return $helpers->json($contactos);
    }

    // 
    // Exportar datos en excel
    // 
    public function exportAction(){
        $em = $this->getDoctrine()->getManager();
        // recuperamos los elementos de base de datos que queremos exportar
        $query = $em->getRepository('ApiBundle:Contacto')
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
            ->setDescription("Listado de Contactos.")
            ->setKeywords("Vertice exportar excel ejemplo");
        
        // establecemos como hoja activa la primera, y le asignamos un título
        $phpExcelObject->setActiveSheetIndex(0);
        $phpExcelObject->getActiveSheet()->setTitle('Ejemplo de exportación');

        // escribimos en distintas celdas del documento el título de los campos que vamos a exportar
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('B2', 'Nombre')
            ->setCellValue('C2', 'Apellido parterno')
            ->setCellValue('D2', 'Apellido materno')
            ->setCellValue('E2', 'dni o extranjeria')
            ->setCellValue('F2', 'Email')
            ->setCellValue('G2', 'Distrito')
            ->setCellValue('H2', 'Celular')
            ->setCellValue('I2', 'Modelo')
            ->setCellValue('J2', 'Forma de contacto')
            ->setCellValue('K2', 'Comenario')
            ->setCellValue('L2', 'Newsletter')
            ->setCellValue('M2', 'Politicas')
            ->setCellValue('N2', 'Fecha');
            
        
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
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('G')
            ->setWidth(20);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('H')
            ->setWidth(20);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('I')
            ->setWidth(20);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('J')
            ->setWidth(20);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('K')
            ->setWidth(20);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('L')
            ->setWidth(20);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('M')
            ->setWidth(20);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('N')
            ->setWidth(20);
        // recorremos los registros obtenidos de la consulta a base de datos escribiéndolos en las celdas correspondientes
        $row = 3;
        foreach ($result as $item) {
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('B'.$row, $item->getNombre())
                ->setCellValue('C'.$row, $item->getApepaterno())
                ->setCellValue('D'.$row, $item->getApematerno())
                ->setCellValue('E'.$row, $item->getDniextranjeria())
                ->setCellValue('F'.$row, $item->getEmail())
                ->setCellValue('G'.$row, $item->getDistrito())
                ->setCellValue('H'.$row, $item->getCelular())
                ->setCellValue('I'.$row, $item->getModelo())
                ->setCellValue('J'.$row, $item->getFormacontacto())
                ->setCellValue('K'.$row, $item->getComentario())
                ->setCellValue('L'.$row, $item->getNewsletter())
                ->setCellValue('M'.$row, $item->getPoliticas())
                ->setCellValue('N'.$row, $item->getFecha()->format('d-M-Y'));
            $row++;
        }

        // se crea el writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // se crea el response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // y por último se añaden las cabeceras
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'contactos.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }


}