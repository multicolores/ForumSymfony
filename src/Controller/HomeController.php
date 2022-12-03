<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class HomeController extends AbstractController
{

    /**
     * @Route("/", name="home", methods={"GET", "POST"})
     */
    public function home(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        // $entityManager = $doctrine->getManager();
        // $theme = new Theme();

        // $theme->setName("nom7")
        //     ->setStatus(true);
        // $entityManager->persist($theme);
        // $entityManager->flush();

        $repository = $doctrine->getRepository(Theme::class);
        $donnees = $repository->findAll();

        $themes = $paginator->paginate(
            $donnees,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('home/home.html.twig', [
            'themes' => $themes,
        ]);
    }
}
