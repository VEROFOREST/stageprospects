<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Entity\Parcours;
use App\Entity\Prospect;
use App\Form\ProspectType;
use App\Repository\EtapeRepository;
use App\Repository\MembreRepository;
use App\Repository\ProspectRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;




/**
 * @Route("/prospect")
 */
class ProspectController extends AbstractController
{
    /**
     * @Route("/", name="prospect_index", methods={"GET"})
     */
    public function index(ProspectRepository $prospectRepository): Response
    {
        return $this->render('prospect/index.html.twig', [
            'prospects' => $prospectRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="prospect_new", methods={"GET","POST"})
     */
    public function new(Parcours $parcour, Request $request, MembreRepository $membreRepository, EtapeRepository $etapeRepository,\Swift_Mailer $mailer): Response
    {
        $prospect = new Prospect();
        $form = $this->createForm(ProspectType::class, $prospect);
        $form->handleRequest($request);
        $date = new \DateTime('@'.strtotime('now'));
       
        $membres = $membreRepository->findBy(['id'=>1]);
        $etapes = $etapeRepository->findBy(['id'=>1]);
        
        //  dd($membres[0]);
        if($form->get('save_and_add')->isClicked() && $form->isSubmitted() && $form->isValid()){
             $entityManager = $this->getDoctrine()->getManager();
            $prospect->setParcours($parcour);
            $prospect->setCreatedAt($date);
            $prospect->setMembre($membres[0]);
            $prospect->setEtape($etapes[0]);
            $prospect->setRole(2);
            $prospect->setActif(-2);
            $entityManager->persist($prospect);
            $entityManager->flush();
            // envoi d'email de confirmation.
            // dd($prospect->getEmail());
            
            $message = (new \Swift_Message('demande de renseignements'))
            ->setFrom('vxforest@gmail.com')
            ->setTo($prospect->getEmail())
            ->setBody(
                    '<h1>Merci de l\'intérêt que vous nous portez, vos coordonnées sont enregistrées, nous vous recontacterons dès que possible.</h1>
                                Ares Formation'
                ,
                    'text/html'
             )
            ;
            $message->SMTPOptions = array(
                'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
                )
                );

            $mailer->send($message); 

            return $this->redirectToRoute('prospect_confirmation',['id'=> $prospect->getId()]);




        }




        
       
        if ($form->isSubmitted() && $form->isValid()) {
           
            $entityManager = $this->getDoctrine()->getManager();
            $prospect->setParcours($parcour);
            $prospect->setCreatedAt($date);
            $prospect->setMembre($membres[0]);
            $prospect->setEtape($etapes[0]);
            $prospect->setRole(2);
            $prospect->setActif(-2);
            $entityManager->persist($prospect);
            $entityManager->flush();

            return $this->redirectToRoute('pre_inscription_new',['id'=> $prospect->getId()]);
        }

        return $this->render('prospect/new.html.twig', [
            'prospect' => $prospect,
            'parcours'=>$parcour,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="prospect_confirmation", methods={"GET"})
     */
    public function show(Prospect $prospect): Response
    {
        return $this->render('prospect/confirmation.html.twig', [
            'prospect' => $prospect,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="prospect_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Prospect $prospect): Response
    {
        $form = $this->createForm(ProspectType::class, $prospect);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('prospect_index');
        }

        return $this->render('prospect/edit.html.twig', [
            'prospect' => $prospect,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="prospect_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Prospect $prospect): Response
    {
        if ($this->isCsrfTokenValid('delete'.$prospect->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($prospect);
            $entityManager->flush();
        }

        return $this->redirectToRoute('prospect_index');
    }
}
