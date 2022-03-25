<?php

namespace App\Controller;

use App\Entity\Commentary;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @Route("/profile/tout-mes-commentaires", name="show_user_commentaries", methods={"GET"})
     */
    public function showUserCommentaries(EntityManagerInterface $entityManager) : Response
    {
        $commentaries =$entityManager->getRepository(Commentary::class)->findBy(['author' => $this->getUser()]);

        // Statistiques depuis le Controller (voir la vue show_user_commentaries.html.twig)

        $total = count($commentaries);
        $totalInline = count($entityManager->getRepository(Commentary::class)->findBy(['deletedAt' => null, 'author' => $this->getUser()]));
        $totalArchived = $total - $totalInline;

        // foreach($commentaries as $commentary)
        //     if($commentary->getDeletedAt() != null)
        //     {
        //         ++$totalArchived;
        //     }

        return $this->render('profile/show_user_commentaries.html.twig',[
            'commentaries' => $commentaries,
            'total' => $total,
            'totalInline' => $totalInline,
            'totalArchived' => $totalArchived,
        ]);

    }
}
