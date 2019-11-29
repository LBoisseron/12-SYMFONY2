<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    use HelperTrait;

    /**
     * démo de l'ajout d'un article avec Doctrine
     * @Route("/demo/article", name="article_demo")
     */
    public function demo()
    {
        # création d'une nouvelle catégorie
        $category = new Category();
        $category->setName('Informatique')
                ->setAlias('informatique');

        # création d'un utilisateur
        $user = new User();
        $user->setFirstname('Léna')
            ->setLastname('BOISSERON')
            ->setEmail('lboisseron@yahoo.fr')
            ->setPassword('test')
            ->setRoles(['ROLE_REDACTRICE_WEB']);

        # création d'un article
        $article = new Article();
        $article->setTitle('Scrum ou Kanban : quelles différences ?')
            ->setAlias('scrum-ou-kanban-quelles-differences')
            ->setContent('<h3>Dans le domaine de la gestion de projet, les méthodes agiles ont le vent en poupe.</h3>')
            ->setImage('scrumkanban.jpg')
            ->setCategory($category)
            ->setUser($user);

        /**
         * récupération du Manager de Doctrine
         * -------------------------------------
         * le EntityManager est une classe qui sait comment persister (enregistrer) d'autres classes
         * (effectuer des opérations CRUD sur nos entitiés)
         * --------------------------------------
         * ici, Doctrine va s'aider des annotations pour gérer nos données
         */
        $em = $this->getDoctrine()->getManager();

        # on précise ce que l'on souhaite sauvegarder
        $em->persist( $category );
        $em->persist( $user );
        $em->persist( $article );

        $em->flush(); // déclencher l'exécution par Doctrine

        # retourne une réponse
        return new Response('Nouvel article :' . $article->getTitle());

    }

    /**
     * formulaire permettant l'ajout d'un article
     * @Route("creer-un-article", name="article_add")
     */
    public function addArticle(Request $request)
    {
        # créer un nouvel article
        $article = new Article();

        $journaliste = $this->getDoctrine()
            ->getRepository(User::class)
            ->find(1);

        # on affecte le User à l'article
        $article->setUser($journaliste);

        # création du formulaire
        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Titre de l\'article'
                ]
            ])
            # category
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => false
            ])
            # article's content
            ->add('content', TextareaType::class, [
                'required' => false,
                'label' => false
            ])
            # image upload
            ->add('image', FileType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'dropify'
                ]
            ])
            # Submit Button
            ->add('submit', SubmitType::class, [
                'label' => 'Publier mon Article'
            ])
            # crée le formulaire
            ->getForm();

        # permet à symfony de gérer les données reçues
        $form->handleRequest($request);

        # si le formulaire est soumis et que les informations sont validées
        if ( $form->isSubmitted() && $form->isValid()) {

            /**
             * @var UploadedFile $imageFile
             */
            $imageFile = $form['image']->getData();


            if ($imageFile) {

                $newFilename = $this->slugify($article->getTitle()) . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('articles_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $article->setImage($newFilename);

            } # fin de l'upload de l'image

                # génération de l'alias de l'article
                $article->setAlias($this->slugify($article->getTitle()));

                # sauvegarde en BDD
                $em = $this->getDoctrine()->getManager();
                $em->persist($article);
                $em->flush();

                # notification
            $this->addFlash('notice',
                'Félicitations, votre article est en ligne !');

                # redirection
                return $this->redirectToRoute('default_article', [
                    'categorie' => $article->getCategory()->getAlias(),
                    'alias' => $article->getAlias(),
                    'id' => $article->getId()
                ]);

        }

        #transmission du formulaire à la vue
        return $this->render('article/form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
