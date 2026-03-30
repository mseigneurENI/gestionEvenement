<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, ['label' => 'Pseudo :', 'required' => true, 'attr' => ['placeholder' => 'Licorne56']])
//            ->add('roles')
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Mot de passe :'],
                'second_options' => ['label' => 'Confirmation :'],
                'invalid_message' => 'Both passwords do not match.',
                'required' => false,
                'mapped' => false
            ])
            ->add('firstname', TextType::class, ['label' => 'Prénom :', 'required' => true, 'attr' => ['placeholder' => 'Ada']])
            ->add('lastname', TextType::class, ['label' => 'Nom :', 'required' => true, 'attr' => ['placeholder' => 'Lovelace']])
            ->add('phoneNb', TelType::class, ['label' => 'Telephone :', 'required' => false, 'attr' => ['placeholder' => '06 06 06 06 06']])
            ->add('email', EmailType::class, ['label' => 'Email :', 'required' => true, 'attr' => ['placeholder' => 'ada.lovelace@eni.fr']])
//            ->add('active')
            ->add('image', FileType::class, ['mapped' => false, 'label' => 'Image de profil :', 'required' => false, 'constraints'=>[new Image(mimeTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/svg', 'image/webp', 'image/tiff'], minWidth: 20)]])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir un campus',
                'query_builder' => function (CampusRepository $campusRepository) {
                    return $campusRepository->createQueryBuilder('c')->addOrderBy('c.name', 'ASC');
                }
            ])
//            ->add('events', EntityType::class, [
//                'class' => Event::class,
//                'choice_label' => 'id',
//                'multiple' => true,
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
