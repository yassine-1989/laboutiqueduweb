<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    //je dois aller chercher le $stripeSessionId de la bdd
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager=$entityManager;
    }
    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_success")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);
       if (!$order || $order->getUser()!=$this->getUser()){
           return $this->redirectToRoute('home');
       }

        if ($order->getState()==0){
            //Vider la session *cart* vider le panier aprés le paiement

            $cart->remove();
            //Modifier le statut isPaid de ma commande en mettant 1

            $order->setState(1);
            $this->entityManager->flush();

            //Envoyer un mail de confirmation au client
            //Envoyer un mail de notification avec JetMail
            $mail=new Mail();
            $content='Bonjour '.$order->getUser()->getFirstname().'<br/><br/> Merci pour votre commande ';
            $mail->send($order->getUser()->getEmail(),$order->getUser()->getFirstname(),'Votre commande est bien validée',$content);

        }


        //Afficher les informations de la commande à l'utilisateur
        return $this->render('order_success/index.html.twig',[
            'order'=>$order
        ]);
    }
}
