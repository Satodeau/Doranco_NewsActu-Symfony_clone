<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="show_profile", methods={"GET"})
     */
    public function showProfile() : Response
    {
        return $this->render('profile/show_profile.html.twig');
    }
}
