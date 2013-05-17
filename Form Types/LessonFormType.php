<?php
namespace CloudPod\ClassroomBundle\Forms\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class LessonFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('lessonTitle', 'text', array('label' => 'Lesson Name :'))
				->add('lessonDesc', 'text', array('label' => 'Description :'))
				->add('lessonContent', 'textarea', array(
			'label' => 'Content :',
        	'attr' => array(
            'class' => 'tinymce',
            'data-theme' => 'medium',
            'style' => 'width: 100%; height: 370px;'))); // simple, advanced, bbcode
				

	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => 'CloudPod\ClassroomBundle\Entity\Lessons',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'LessonForm',
        ));
    }

    public function getName()
	{
		return 'ClassLessonForm';
	}

}