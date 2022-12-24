<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


class ListUserController extends AbstractController
{
    /**
     * @Route("/admin/userlist", name="user_list", methods={"GET", "POST"})
     */
    public function user_list(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {

        $repository = $doctrine->getRepository(User::class);
        $donnees = $repository->findAll();

        $users = $paginator->paginate(
            $donnees,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('/userList/user-list.html.twig', [
            'users' => $users,
        ]);
    }
}
