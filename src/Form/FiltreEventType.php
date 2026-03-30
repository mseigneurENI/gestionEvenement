<?php

namespace App\Form;

use App\Entity\Campus;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'required' => false,
                'query_builder' => function (CampusRepository $campusRepository) {
                return $campusRepository->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                }
            ])
            ->add('search', SearchType::class, [
                'label' => 'Le nom de la sortie contient :',
                'required' => false,
            ])
            ->add('beginDate', DateTimeType::class, [
                'label' => 'Entre',
                'required' => false,
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'et',
                'required' => false,
            ])
            ->add('checkbox', ChoiceType::class, [
                'choices' => [
                    'Sorties dont je suis l\'organisateur/trice' => 'organisateur',
                    'Sorties auxquelles je suis inscrit/e' => 'enregistre',
                    'Sorties auxquelles je ne suis pas inscrit/e' => 'libre',
                    'Sorties passées' => 'terminee'
                ],
                'expanded' => true,
                'multiple' => true
            ])
//            ->add('submit', SubmitType::class, ['label' => 'Rechercher'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        'data_class' => null,
            'required' => false,
            'csrf_protection' => false,
            // Configure your form options here
        ]);
    }
}
