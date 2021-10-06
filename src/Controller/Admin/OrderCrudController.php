<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;

class OrderCrudController extends AbstractCrudController
{
    private $entityManager;
    //$crudUrlGenerator: ce qui va me permettre de manager une url de redirection une fois que j'ai terminé mon traitement
    //dans updatePreparation
    private $crudUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager, CrudUrlGenerator $crudUrlGenerator)
    {
        $this->entityManager=$entityManager;
        $this->crudUrlGenerator=$crudUrlGenerator;
    }
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    //Ajout: voir dans order Dashboard
    public function configureActions(Actions $actions): Actions
    {
        $updatePreparation=Action::new('updatePreparation','Préparation en cours','fas fa-box-open')->linkToCrudAction('updatePreparation');
        $updateDelivery=Action::new('updateDelivery','Livraison en cours','fas fa-truck')->linkToCrudAction('updateDelivery');

        return $actions
            ->add('detail',$updatePreparation)
            ->add('detail',$updateDelivery)
            ->add('index','detail');
    }

    public function updatePreparation(AdminContext $context)
    {
        $order=$context->getEntity()->getInstance();
        $order->setState(2);
        $this->entityManager->flush();

        $this->addFlash('notice',"<span style='color:green;'><strong>La commande".$order->getReference()." est bien <u> en cours de préparation</u></strong></span>");

        $url=$this->crudUrlGenerator->build()
            ->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();
        return $this->redirect($url);
    }

    public function updateDelivery(AdminContext $context)
    {
        $order=$context->getEntity()->getInstance();
        $order->setState(3);
        $this->entityManager->flush();

        $this->addFlash('notice',"<span style='color:green;'><strong>La commande".$order->getReference()." est bien <u> en cours de livraison</u></strong></span>");

        $url=$this->crudUrlGenerator->build()
            ->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();
        return $this->redirect($url);
    }


        //configureCrud fonction pour tri DESC de l'affichage des commandes
    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id'=>'DESC']);
    }

    //Configuration des fields à la main
    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('user.fullname')->setLabel('Client'),
            TextEditorField::new('Delivery','Adresse de livraison')->onlyOnDetail(),
            DateTimeField::new('CreatedAt','Passée  le'),
            MoneyField::new('total','Total produit')->setCurrency('EUR'),
            TextField::new('carrierName')->setLabel('Transporteur'),
            MoneyField::new('carrierPrice','Frais de port')->setCurrency('EUR'),
            ChoiceField::new('state')->setChoices([
                'Non payée'=>0,
                'Payée'=>1,
                'Préparation en cours'=>2,
                'Livraison en cours'=>3
            ]),
            ArrayField::new('orderDetails','Produits achetés')->hideOnIndex()
        ];
    }
}
