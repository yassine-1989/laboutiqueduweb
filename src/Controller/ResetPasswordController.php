<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager )
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request)
    {
        if($this->getUser())
        {
            return $this->redirectToRoute('home');
        }
        if($request->get('email'))
        {
            $user=$this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));
            if($user)
            {
                //1: enregistrer en base la demande de reset_password avec user, CreatedAt et le token
                $reset_password=new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new \DateTime(
                ));
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                //2: envoyer un email à l'utilisateur avec un lien permettant de mettre à jour le mdp
                $url =$this->generateUrl('update_password', [
                    'token'=>$reset_password->getToken(),
                ]);
                $content="Bonjour ".$user->getFirstname()."<br/> Vous avez demandé à réinisialiser votre mot de passe sur La Boutique Du Web
                <br/><br/>";
                $content.=" Veuillez cliquer sur le lien suivant pour <a href='".$url."'> mettre à jour votre mot de passe.</a>";

                $mail=new Mail();
                $mail->send($user->getEmail(), $user->getLastname().$user->getFirstname(),'Réinisialiser votre
                mot de passe sur La Boutique Du Web',$content);
                $this->addFlash('notice','Vous allez reçevoir un email avec la procédure pour réinisialiser votre mot de passe.');

            }else{
                $this->addFlash('notice','Cette adresse email est inconnue.');

            }
        }
        return $this->render('reset_password/index.html.twig');
    }

    /**
     * @Route("/modifier-mot-de-passe/{token}", name="update_password")
     */
    public function update(Request $request, $token, UserPasswordEncoderInterface $encoder)
    {
        $reset_password=$this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);
        if(!$reset_password)
        {
            return $this->redirectToRoute('reset_password');
        }

        //Vérifier si le createdAt= now - 1h
        $now=new \DateTime();
        if($now>($reset_password->getCreatedAt()->modify('+ 1 hour'))){
                $this->addFlash('notice','Votre demande a expiré, merci de la renouveller.');
                return $this->redirectToRoute('reset_password');
        }
        //Rendre une vue avec mot de passe et confirmez votre mot de passe
        $form=$this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $new_pwd=$form->get('new_password')->getData();            ;
            //Encodage du mdp
            $password=$encoder->encodePassword($reset_password->getUser(), $new_pwd);
            $reset_password->getUser()->setPassword($password);
            //Flush les données
            $this->entityManager->flush();

            //Redirection de l'utilisateur vers la page de connexion
            $this->addFlash('notice','Votre mot de passe a bien été mis à jour');
            return  $this->redirectToRoute('app_login');
          //  dd($new_pwd);

        }
        return $this->render('reset_password/update.html.twig',[
            'form'=>$form->createView()
        ]);



    }
}
