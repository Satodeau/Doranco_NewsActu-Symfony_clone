<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentary;
use App\Form\CommentaryFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaryController extends AbstractController
{
    /**
     * @Route("/ajouter-un-commentaire?article_id={id}", name="add_commentary", methods={"GET|POST"})
     */
    public function addCommentary(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentary = new Commentary();

        $form = $this->createForm(CommentaryFormType::class, $commentary)
            ->handleRequest($request);

        # Cas où le formulaire n'est pas valide. Lorsque le champ 'comment' est vide, il y la contrainte NotBlank qui se déclenche.
            if($form->isSubmitted() && $form->isValid() === false) {
                $this->addFlash('warning', 'Votre commentaire est vide !');

                return $this->redirectToRoute('show_article', [
                    'cat_alias' => $article->getCategory()->getAlias(),
                    'article_alias' => $article->getAlias(),
                    'id' => $article->getId()
                ]);
            }

            if($form->isSubmitted() && $form->isValid()) {

                $commentary->setArticle($article);
                $commentary->setCreatedAt(new Datetime);
                $commentary->setUpdatedAt(new Datetime);

                // On set l'auteur du commentaire
                $commentary->setAuthor($this->getUser());

                $entityManager->persist($commentary);
                $entityManager->flush();

                $this->addFlash('success', "Vous avez commenté l'article <strong> ". $article->getTitle() ." </strong> avec succès !");

                return $this->redirectToRoute('show_article', [
                    'cat_alias' => $article->getCategory()->getAlias(),
                    'article_alias' => $article->getAlias(),
                    'id' => $article->getId()
                ]);
            }

        return $this->render('rendered/form_commentary.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     *  1ère FAÇON =>
     *               Inconvénient : C'est très verbeux
     *                              Les paramètres attendus de la route pour faire un redirectToRoute() peuvent ne pas être accessibles. 
     *               Avantage :     Ka redirection sera STATIQUE tout lees utilisateurs seront redirigés au même endroit
     * 
     * 2ème FAÇON =>
     *               Inconvénient :  La redirection se fera en fonction de l'url de provenance de la requête, à savoir si vous utilisez cette action à plusieurs endroits différents de votre site, l'utilisateur sera redirigé ailleurs que ce que vous avez décidé.
     *               Avantage :      La redirection devient DYNAMIQUE. (elle changera en fonction de la provenance de la requête)
     * 
     * 
     * @Route("/archiver-un-commentaire?article_id={id}", name="soft_delete_commentary", methods={"GET|POST"})
     */
    public function softDeleteCommentary(Commentary $commentary,EntityManagerInterface $entityManager, Request $request) : Response
    {
       /*
        * PARCE QUE nous allons rediriger veers 'show_article' qui attend 3 arguments,nous avons injecté l'objet Request ↑↑↑
        * Cela nous permettrea d'accéder aux superglobales PHP ($_GET et $_POST => appelés dans l'ordre query & server)
        * Nous allons voir 2 façons pour rediriger sur la route souhaité
        */
        $commentary->setDeletedAt(new DateTime());

        #========================== 1ere FAÇON ==========================#
        //        dd($request->query->get('article_alias'));
        #================================================================#

        #========================== 2eme FAÇON ==========================#
        //     dd($request->server->get('HTTP_REFERER'));
        #================================================================#

        $entityManager->persist($commentary);
        $entityManager->flush();

        $this->addFlash('success',"Votre commentaire est archivé");
      
        #========================== 1ere FAÇON ==========================#
        # La construction de l'URL a lieu dans le fichier 'show_article.html.twig' sur l'attibut HTML 'href' de la balise <a>.
        # Cette étape a lieu dans le fichier 'show_article.html.twig' sur l'attribut HTML 'href' de la balise <a>
            # ==> VOIR 'show_article.html.twig' pour la suite de la 1ere FAÇON


                # Ici nous récupérons les valeurs des paramètres passés dans l'url $_GET (query)
        //        return $this->redirectToRoute('show_article', [
        //        'cat_alias' => $request->query->get('cat_alias'),
        //        'article_alias' => $request->query->get('article_alias'),
        //        'id' => $request->query->get('article_id')
        //     ]);
        #================================================================#



        #========================== 2eme FAÇON ==========================#
            # Pour cette façon nous avons retirés les paramètres de l'URL dans le fichier 'show_article.html.twig'
                # ==> VOIR 'show_article.html.twig' pour la suite de la 2ème FAÇON

            # Ici nous utilisons une clée du $_SERVER (server) qui s'appelle 'HTTP_REFERER' . 
            # Cette clé contient l'URL de provenance de la requête ($request) voir le dd plus haut pour + de précision
        return $this->redirect($request->server->get('HTTP_REFERER'));


        #================================================================#



    


      
    }

     /**
     * @Route("/restaurer-un-commentaire/{id}", name="restore_commentary", methods={"GET"})
     */
    public function restoreArticle(Commentary $commentary, EntityManagerInterface $entityManager,Request $request): Response
    {
        $commentary->setDeletedAt();

        $entityManager->persist($commentary);
        $entityManager->flush();

        $this->addFlash('success', "Le commentaire a bien été restauré");

        return $this->redirectToRoute('show_user_commentaries');
    }
}