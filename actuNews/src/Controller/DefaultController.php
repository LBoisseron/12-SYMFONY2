<?php


namespace App\Controller;


use App\Entity\Article;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    public function index()
    {
        # récupération de tous les articles
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findAll();

        # transmission à la vue
        return $this->render('default/index.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route("/category/{alias}", name="default_category", methods={"GET"})
     * @return Response
     */
    public function category($alias)
    {
        # récupération de la catégorie via l'alias
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy(['alias' => $alias]);

        /**
         * grâce à la relation entre Article et Catégorie (OneToMany),
         * je suis en mesure de récupérer les articles de la catégorie
         */
        $articles = $category->getArticles();

        return $this->render('default/category.html.twig', ['articles' => $articles, 'category' => $category ]);
    }

    /**
     * @Route("/{categorie}/{alias}_{id}.html", name="default_article", methods={"GET"})
     * @return Response
     */
    public function article(Article $article)
    {

        # récupération de l'article
        # $article = $this->getDoctrine()
        #    ->getRepository(Article::class)
        #    ->find($id);

        # dump($article);

        return $this->render('default/article.html.twig' , [
            'article' => $article
            ]) ;
    }

    public function menu()
    {
        # récupération des catégories
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        # transmission à la vue
        return $this->render('components/_nav.html.twig', ['categories' => $categories]);
    }

    public function footer()
    {
        # récupération des catégories
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        # transmission à la vue
        return $this->render('components/footer.html.twig', ['categories' => $categories]);
    }

}