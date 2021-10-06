<?php
namespace App\Form;

use App\Classe\Search;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

Class SearchType extends AbstractType
{
    //FormBuilderInterface: fct pour la création du formulaire
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // création de l'input de recherche
            ->add('string',TextType::class,[
                'label'=>'Rechercher',
                'required'=>false,
                'attr'=>[
                    'placeholder'=>'Votre recherche...',
                    'class'=>'form-control-sm'
                ]
            ])
            //création des checkbox
            ->add('categories', EntityType::class, [
                'label'=>false,
                'required'=>false,
                'class'=>Category::class,
                'multiple'=>true,
                'expanded'=>true


            ])
            //création du bouton de recherche
        ->add('submit', SubmitType::class, [
            'label'=>'Filtrer',
            'attr'=>[
                'class'=>'btn-block btn-info'
            ]
        ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' =>Search::class,
            'method'=>'GET',
            'crsf_protection'=>false,

        ]);

    }


    public function getBlockPrefix()
    {
        return '';
    }
}