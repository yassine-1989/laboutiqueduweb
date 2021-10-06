<?php

namespace App\Controller;

use App\Classe\Search;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager )
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @Route("/nos-produits", name="products")
     */
    public function index(Request $request): Response
    {
        //appel du formulaire de categries
        $search=new Search();                                       //initialiser/instancier  la classe Search
        $form=$this->createForm(SearchType::class, $search);   //creation du formulaire
        $form->handleRequest($request);                             //Ecouter notre formulaire
        if ( ( $form->isSubmitted() ) && ( $form->isValid() )  )    //Si le formulaire est soumis et valide
        {
            //Appel de notre ProductRepository et de la fct FindWithSearch
            $products=$this->entityManager->getRepository(Product::class)->FindWithSearch($search);
          //  $search=$form->getData();
        }else
        {
            $products=$this->entityManager->getRepository(Product::class)->findAll();

        }
        return $this->render('product/index.html.twig',[
            'products'=>$products,
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/produit/{slug}", name="product")
     */
    public function show($slug): Response
    {
        $product=$this->entityManager->getRepository(Product::class)->findOneBySlug($slug);

        //pour refaire un affichage avec isBest

        $products=$this->entityManager->getRepository(Product::class)->findByIsBest(1);


        if(!$product){
            return $this->redirectToRoute('products');
        }
        return $this->render('product/show.html.twig',[
            'product'=>$product,
            'products'=>$products
        ]);
    }
}
