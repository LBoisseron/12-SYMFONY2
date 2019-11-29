<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
class UserController extends AbstractController
{
    /**
     * @Route("/connexion.html", name="user_login", methods={"GET|POST"})
     */
    public function login()
    {
        return $this->render('user/login.html.twig');
    }
    /**
     * @Route("/inscription.html", name="user_register", methods={"GET|POST"})
     */
    public function register()
    {
        # 1. création d'un nouvel utilisateur
        # 2. création du formulaire
        # 3. verificationde la soumission
        # 4. encodage du MdP
        # 5. sauvegarde en BDD
        # 6. notification flash
        # 7. redirection sur la page de connexion

        return $this->render('user/register.html.twig');
    }
}