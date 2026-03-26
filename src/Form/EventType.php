<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\Place;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{

    public function __construct(private Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $user = $this->security->getUser();

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie',
        ])
            ->add('beginDateEvent', DateTimeType::class, [
                'label' => 'Date de début de l\'événement'
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Date de fin de l\'événement'
            ])
            ->add('limitDateRegistration', DateTimeType::class, [
                'label' => 'Date limite d\'inscription'
            ])

            ->add('registrationMaxNb', NumberType::class, [
                'label' => 'Nombre maximum de participant·e·s'
            ])

            ->add('details', TextType::class, [
                'label' => 'Détails de la sortie'
            ])
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name',
                'label' => 'Lieu de la sortie'
            ])

            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'label' => 'Campus',
                'query_builder' => function (CampusRepository $campusRepository) {
                    return $campusRepository->createQueryBuilder('c')->addOrderBy('c.name');

                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
