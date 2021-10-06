<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @Route("/inscription", name="register")
     */

//controleur qui gére l'inscription sur mon site
    public function index(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $notification=null;

        $user=new User();
        $form=$this->createForm(RegisterType::class,$user);
        $form->handleRequest($request);

        if (($form->isSubmitted())&&($form->isValid())){
            $user=$form->getData();

            //Verifier si l'utilisateur n'est pas déja inscrit
            $search_email=$this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());
            if(!$search_email){
                $password=$encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($password);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                //Envoyer un mail de notification avec JetMail
                $mail=new Mail();
                        //Corps du mail
                $content='Bonjour '.$user->getFirstname().'<br/><br/> Bienvenue sur la boutique dédiée au 100% made in France ';
                        //Appel de la fonction send()
                $mail->send($user->getEmail(),$user->getFirstname(),'Bienvenue dans La Boutique Du Web', $content);


                $notification="Votre inscription s'est bien déroulée. Vous pouvez dés à présent vous connecter à votre compte";

            }else{

                $notification="L'email que vous avez renseigné existe déjà";

            }



            }
        return $this->render('register/index.html.twig',[
            'form'=>$form->createView(),
            'notification'=>$notification
        ]);
    }
}
