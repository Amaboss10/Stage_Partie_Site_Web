<?php

namespace App\Controller;

use App\Entity\Marque;
use App\Form\CreerModifierMarqueType;
use App\Repository\AnnonceRepository;
use App\Repository\MarqueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MarqueController extends AbstractController
{
    /**
     * @Route("/secondLife/admin/marques", name="secondLife_admin_gerer_marques", methods={"GET"})
     */
    public function index(MarqueRepository $marqueRepository): Response
    {
        return $this->render('marque/admin/gerer_marques.html.twig', [
            'titre_page'=>'Marques',
            'marques' => $marqueRepository->findAll(),
        ]);
    }

    /**
     * @Route("/secondLife/admin/marques/creer", name="secondLife_admin_creer_marque", methods={"GET","POST"})
     */
    public function creerMarque(Request $request): Response
    {     
        $marque = new Marque();
        $form = $this->createForm(CreerModifierMarqueType::class, $marque);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($marque);
            $entityManager->flush();

            return $this->redirectToRoute('secondLife_admin_gerer_marques');
        }

        return $this->render('marque/admin/creer_marque.html.twig', [
            'titre_page'=>'Créer/Ajouter une marque',
            'marque' => $marque,
            'form' => $form->createView(),

        ]);
    }

    /**
     * @Route("/secondLife/admin/marques/{id}/annonces", name="secondLife_admin_afficher_annonces_de_la_marque", methods={"GET"})
     */
    public function afficherAnnoncesMarque(Marque $marque,AnnonceRepository $annonceRepos): Response
    {
        $annonces=$annonceRepos->findAnnoncesByMarque($marque);
        return $this->render('marque/admin/afficher_annonces_marque.html.twig', [
            'titre_page'=>'Annonces de la marque '. $marque->getNomMarque(),
            'marque' => $marque,
            'annonces'=>$annonces,
            'nb_annonces'=>count($annonces),
        ]);
    }

    /**
     * @Route("/secondLife/admin/marques/{id}/modifier", name="secondLife_admin_modifier_marque", methods={"GET","POST"})
     */
    public function modifierMarque(Request $request, Marque $marque): Response
    {
        $form = $this->createForm(CreerModifierMarqueType::class, $marque);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('secondLife_admin_gerer_marques');
        }

        return $this->render('marque/admin/modifier_marque.html.twig', [
            'titre_page'=>'Modifier la marque',
            'marque' => $marque,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/secondLife/admin/marques/{id}/supprimer", name="secondLife_admin_supprimer_marque", methods={"DELETE"})
     */
    public function delete(Request $request, Marque $marque,AnnonceRepository $annonceRepos): Response
    {
        $annonces=$annonceRepos->findAnnoncesByMarque($marque);
        if($annonces==null){
            //si la marque ne possede aucune annonce, on peut la supprimer
            if ($this->isCsrfTokenValid('delete'.$marque->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($marque);
                $entityManager->flush();
                return $this->redirectToRoute('secondLife_admin_gerer_marques');
            }
        }
        echo "<p>Vous ne pouvez pas supprimer une marque qui possede des annonces</p>";
        return $this->redirectToRoute('secondLife_admin_afficher_annonces_marque');
        
    }
}