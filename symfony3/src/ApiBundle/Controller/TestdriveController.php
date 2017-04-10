<?php 

namespace ApiBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class TestdriveController extends Controller{

    public function indexAction(){
        $helpers = $this->get('api.helpers');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository("ApiBundle:Testdrive")->createQueryBuilder('t')->getQuery();
        $tests = $query->getResult();
        return $helpers->json($tests);
    }

    public function exportAction(){
        $em = $this->getDoctrine()->getManager();
        // recuperamos los elementos de base de datos que queremos exportar
        $query = $em->getRepository('ApiBundle:Testdrive')
            ->createQueryBuilder('t')
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
            ->setCellValue('H2', 'Empresa')
            ->setCellValue('I2', 'Cargo')
            ->setCellValue('J2', 'Celular')
            ->setCellValue('K2', 'Modelo')
            ->setCellValue('L2', 'Forma de contacto')
            ->setCellValue('M2', 'Comenario')
            ->setCellValue('N2', 'Newsletter')
            ->setCellValue('O2', 'Politicas')
            ->setCellValue('P2', 'Fecha');
            
        
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
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('O')
            ->setWidth(20);
        $phpExcelObject->setActiveSheetIndex(0)
            ->getColumnDimension('P')
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
                ->setCellValue('H'.$row, $item->getEmpresa())
                ->setCellValue('I'.$row, $item->getCargo())
                ->setCellValue('J'.$row, $item->getCelular())
                ->setCellValue('K'.$row, $item->getModelo())
                ->setCellValue('L'.$row, $item->getFormacontacto())
                ->setCellValue('M'.$row, $item->getComentario())
                ->setCellValue('N'.$row, $item->getNewsletter())
                ->setCellValue('O'.$row, $item->getPoliticas())
                ->setCellValue('P'.$row, $item->getFecha()->format('d-M-Y'));
            $row++;
        }

        // se crea el writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // se crea el response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // y por último se añaden las cabeceras
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'testdrives.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}