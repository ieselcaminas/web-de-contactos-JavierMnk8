<?php

namespace App\Controller;

use App\Repository\ContactosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Contactos;
use App\Entity\Provincia;
use PSpell\Config;
use App\Form\ContactoFormType as ContactoType;
use Symfony\Component\HttpFoundation\Request;

class PageController extends AbstractController
{
    private $contactos = [
        1 => ["nombre" => "Juan Pérez", "telefono" => "524142432", "email" => "juanp@ieselcaminas.org"],
        2 => ["nombre" => "Ana López", "telefono" => "58958448", "email" => "anita@ieselcaminas.org"],
        5 => ["nombre" => "Mario Montero", "telefono" => "5326824", "email" => "mario.mont@ieselcaminas.org"],
        7 => ["nombre" => "Laura Martínez", "telefono" => "42898966", "email" => "lm2000@ieselcaminas.org"],
        9 => ["nombre" => "Nora Jover", "telefono" => "54565859", "email" => "norajover@ieselcaminas.org"]
    ];     

    #[Route('/contacto/editar/{codigo}', name: 'editar', requirements:["codigo"=>"\d+"])]

public function editar(ManagerRegistry $doctrine, Request $request, int $codigo) {

    $repositorio = $doctrine->getRepository(Contactos::class);

    //En este caso, los datos los obtenemos del repositorio de contactos

    $contacto = $repositorio->find($codigo);

    if ($contacto){

        $formulario = $this->createForm(ContactoType::class, $contacto);



        $formulario->handleRequest($request);



        if ($formulario->isSubmitted() && $formulario->isValid()) {

            //Esta parte es igual que en la ruta para insertar

            $contacto = $formulario->getData();

            $entityManager = $doctrine->getManager();

            $entityManager->persist($contacto);

            $entityManager->flush();

            return $this->redirectToRoute('ficha_contacto', ["codigo" => $contacto->getId()]);

        }

        return $this->render('nuevo.html.twig', array(

            'formulario' => $formulario->createView()

        ));

    }else{

        return $this->render('ficha_contacto.html.twig', [

            'contacto' => NULL

        ]);

    }

}

    #[Route('/contacto/nuevo', name: 'nuevo')]

public function nuevo(ManagerRegistry $doctrine, Request $request) {
    $contacto = new Contactos();
    $formulario = $this->createForm(ContactoType::class, $contacto);
    $formulario->handleRequest($request);

    if ($formulario->isSubmitted() && $formulario ->isValid()) {
        $contacto = $formulario->getData();

        $entityManager = $doctrine->getManager();
        $entityManager->persist($contacto);
        $entityManager->flush();
        return $this->redirectToRoute('ficha_contacto', ["codigo" => $contacto->getId()]);
    }
    return $this->render('nuevo.html.twig', array(
        'formulario' => $formulario->createView()
    ));
}

    #[Route('/', name: 'inicio')]
    public function inicio(): Response
    {
        return $this->render('inicio.html.twig');
    }

    public function buscar (ManagerRegistry $doctrine, $texto): Response{
        $repositorio = $doctrine->getRepository(Contactos::class);
        
        $contactos = $repositorio->findByName($texto);

        return $this->render('lista_contactos.html.twig', [
            'contactos' => $contactos
        ]);
    }



    #[Route('/contacto/insertar', name: 'insertar_contacto')]
    public function insertar(ManagerRegistry $doctrine)
{
    $entityManager = $doctrine->getManager();
    foreach($this->contactos as $c){
        $contacto = new Contactos();
        $contacto -> setNombre($c["nombre"]);
        $contacto -> setTelefono($c["telefono"]);
        $contacto -> setEmail($c["email"]);
        $entityManager -> persist($contacto);
    }

    try
    {

    $entityManager -> flush();
    return new response ("Contactos insertados");
    } catch(\Exception $e){
        return new Response("Error insertando objetos");
    }
}
#[Route('/contacto/{codigo}', name: 'ficha_contacto')]
   public function ficha(ManagerRegistry $doctrine, $codigo): Response{
        $repositorio = $doctrine->getRepository(Contactos::class);
        $contacto = $repositorio->find($codigo);
        
        return $this->render('ficha_contacto.html.twig', [
            'contacto' => $contacto
        ]);
    }


    public function update (ManagerRegistry $doctrine, $id, $nombre): Response{
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contactos::class);
        $contacto = $repositorio->find($id);
        if($contacto){
            $contacto->setNombre($nombre);
            try
            {
                $entityManager -> flush();
                return $this->render('ficha_contacto.html.twig', [
                    'contacto' => $contacto
                ]);
            } catch(\Exception $e){
                return new Response("Error en la inserción");
            }
        }else
        return $this->render('ficha_contacto.html.twig', [
            'contacto' => null
        ]);
    }

    public function delete (ManagerRegistry $doctrine, $id): Response{
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contactos::class);
        $contacto = $repositorio->find($id);
        if($contacto){
            $entityManager -> remove($contacto);
            try
            {
                $entityManager -> flush();
                return new Response ("Contacto eliminado");
            } catch(\Exception $e){
                return new Response("Error en la eliminación");
            }
        }else
        return new Response ("No existe el contacto");
    }

    public function insertarConProvincia(ManagerRegistry $doctrine): Response{
        $entityManager = $doctrine -> getManager();
        $provincia = new provincia();
        
        $provincia -> setNombre("Alicante");
        $contacto = new Contactos();

        $contacto -> setNombre("Inserción con provincia");
        $contacto -> setTelefono("900220022");
        $contacto -> setEmail("insercion.de.prueba.provincia@contacto.es");
        $contacto -> setProvincia($provincia);

        $entityManager -> persist($provincia);
        $entityManager -> persist($contacto);

        $entityManager -> flush();
        return $this -> render('ficha_completa.html.twig', [
            'contacto' => $contacto
        ]);
    }

    public function insertarSinProvincia(ManagerRegistry $doctrine): Response{
        $entityManager = $doctrine -> getManager();
        $repositorio = $doctrine -> getRepository(Provincia::class);;
    
        $provincia = $repositorio -> findOneBy(["nombre" => "Alicante"]);

        $contacto = new Contactos();

        $contacto -> setNombre("Inserción sin provincia");
        $contacto -> setTelefono("900220022");
        $contacto -> setEmail("insercion.de.prueba.sin.provincia@contacto.es");
        $contacto -> setProvincia($provincia);

        $entityManager -> persist($contacto);
        
        $entityManager -> flush();
        return $this -> render ('ficha_contacto.html.twig', [
            'contacto' => $contacto
        ]);
        
}


}
