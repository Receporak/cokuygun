<?php

namespace App\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminHomePageController extends AbstractController
{
    #[Route('/', name: 'admin_index', methods: ['GET'])]
    #[isGranted('ROLE_ADMIN')]
    public function index(Request $request): Response
    {
        return $this->render('admin_home_page/index.html.twig', [
            'controller_name' => 'AdminHomePageController',
        ]);
    }
}
