<?php

namespace CloudPod\ClassroomBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CloudPod\ClassroomBundle\Entity\User;
use CloudPod\ClassroomBundle\Entity\Classroom;
use CloudPod\ClassroomBundle\Entity\Lessons;
use CloudPod\ClassroomBundle\Entity\LessonFileUpload;

use CloudPod\ClassroomBundle\Forms\Type\LessonFormType;
use CloudPod\ClassroomBundle\Forms\Type\LessonUploadFormType;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;


class LessonEditorController extends Controller
{
	public function EditLessonAction(Request $request)
    {
         $loggedUserID = $this->get('security.context')->getToken()->getUser()->getUserID();
         
         $lesson = new Lessons();
        
         $lessonForm = $this->createForm(new LessonFormType, $lesson);
         
         $session = $this->get('session'); 
         $lessonID = $session->get('lesson');

         $currentLesson = $this->getDoctrine()
                            ->getRepository('CloudPodClassroomBundle:Lessons')
                            ->find($lessonID);

          $lessonForm->get('lessonTitle')->setData($currentLesson->getLessonTitle());
          $lessonForm->get('lessonDesc')->setData($currentLesson->getLessonDesc());
          $lessonForm->get('lessonContent')->setData($currentLesson->getLessonContent());

      if($request->isMethod('POST'))
        {         
            $lessonForm->bind($request);             
                
                $title = $lessonForm->get('lessonTitle')->getData();
                $description = $lessonForm->get('lessonDesc')->getData();
                $content = $lessonForm->get('lessonContent')->getData();


                $data = $this->Update($lessonID, $title, $description, $content);
                
              $this->get('session')->getFlashBag()->add('notice', 'Lesson Updated!');

            return $this->render('CloudPodClassroomBundle:FlashBag:FlashBagRender4.html.twig', array(
            'id' => $lessonID )); //return $this->redirect($this->generateUrl('admin_home')
        }

/*--------------------------------------------------
Renders template and array values
-----------------------------------------------*/
        return $this->render('CloudPodClassroomBundle:Classrooms:EditLesson.html.twig', array(
        'lessonForm' => $lessonForm->createView()));       

    }

  
  public function EditLessonMaterialAction(Request $request)
    {
          $session = $this->get('session'); 
          $lessonID = $session->get('lesson');

          $lessonfile = new LessonFileUpload();    
          $fileForm = $this->createForm(new LessonUploadFormType, $lessonfile);

          $currentLesson = $this->getDoctrine()
                                ->getRepository('CloudPodClassroomBundle:Lessons')
                                ->find($lessonID);

          $lessonfile =  $this->getDoctrine()
                              ->getRepository('CloudPodClassroomBundle:LessonFileUpload')
                              ->find($currentLesson->getLessonFileID()->getfileID());

          $session = $this->get('session'); 
          $lessonID = $session->get('lesson');
            
          $lessonfile =  $this->getDoctrine()
                              ->getRepository('CloudPodClassroomBundle:LessonFileUpload')
                              ->find($currentLesson->getLessonFileID()->getfileID());


         $fileForm->get('name')->setData($lessonfile->getName());
         
      if($request->isMethod('POST'))
        {         

            $fileForm->bind($request);
            if ($fileForm->isValid())
            {    
  
                  $lessonfile->setName($fileForm->get('name')->getData());
                  $lessonfile->setFile($fileForm->get('file')->getData());
                  $em = $this->getDoctrine()->getManager();
                  $em->persist($lessonfile);
                  $em->flush();
            
            $this->get('session')->getFlashBag()->add('notice', 'File Uploaded!');
   
            }      
        }  

           return $this->render('CloudPodClassroomBundle:Classrooms:EditLessonFile.html.twig', array(
        'fileForm' => $fileForm->createView()));  

    }

    public function DeleteLessonAction(Request $request)
    {
        $session = $this->get('session'); 
        $id = $session->get('lesson');
        $lessonfile = new LessonFileUpload();

        $currentLesson = $this->getDoctrine()
                            ->getRepository('CloudPodClassroomBundle:Lessons')
                            ->find($id);

        $lessonfile =  $this->getDoctrine()
                            ->getRepository('CloudPodClassroomBundle:LessonFileUpload')
                           ->find($currentLesson->getLessonFileID()->getfileID());       

              $data = $this->Delete($id);

              $data2 = $this->DeleteFile($lessonfile);

              $this->get('session')->getFlashBag()->add('notice', 'Lesson Deleted!');

            return $this->render('CloudPodClassroomBundle:FlashBag:FlashBagRender.html.twig');

    }



public function Update($id, $title, $description ,$content)
  {

     $em =$this->getDoctrine()->getManager();
        $query = $em->createQuery('UPDATE CloudPodClassroomBundle:Lessons l SET l.lessonTitle = :title, l.lessonDesc = :description, l.lessonContent = :content WHERE l.lessonID = :id')
           ->setParameters(array('id' => $id, 'title' => $title, 'description' => $description, 'content' => $content));

        try {
        $query->execute();;
        } catch (\Doctrine\ORM\NoResultException $e) {
        
        return $e;}

  }

  public function Delete($id)
  {

     $em =$this->getDoctrine()->getManager();
      $query = $em->createQuery('DELETE FROM CloudPodClassroomBundle:Lessons l WHERE l.lessonID = :id')
           ->setParameter('id', $id);

        try {
        $query->execute();;
        } catch (\Doctrine\ORM\NoResultException $e) {
        
        return $e;}

  }

    public function DeleteFile($id)
  {

     $em =$this->getDoctrine()->getManager();
      $query = $em->createQuery('DELETE FROM CloudPodClassroomBundle:LessonFileUpload l WHERE l.fileID = :id')
           ->setParameter('id', $id);

        try {
        $query->execute();
        } catch (\Doctrine\ORM\NoResultException $e) {
        
        return $e;}

  }


}