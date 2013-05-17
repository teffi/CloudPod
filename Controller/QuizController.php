<?php

namespace CloudPod\ClassroomBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CloudPod\UserBundle\Entity\User;
use CloudPod\ClassroomBundle\Entity\Classroom;
use CloudPod\ClassroomBundle\Entity\Quizzes;
use CloudPod\ClassroomBundle\Entity\QuizQuestions;
use CloudPod\ClassroomBundle\Entity\QuizSubmission;
use CloudPod\ClassroomBundle\Entity\QuizSubmissionAnswer;


use Symfony\Component\HttpFoundation\Session\Session;



use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class QuizController extends Controller
{
    public function createQuizAction($classID, Request $request)
    {
        //renders the quiz form page
         return $this->render('CloudPodClassroomBundle:Forms:QuizForm.html.twig'); 
    }

    public function QuizAction(Request $request, $classID)
    {
        //called when quiz is submitted

        /*-----------------------
        Gets the data from ajax post
        -------------------------*/

        $quizTitle = $request->request->get("qtitle");
        $numberofquestions = $request->request->get("numq");
        $deadlinedate = $request->request->get("deadline");
        $em = $this->getDoctrine()->getManager();
        
   if($request->isMethod('POST'))
        {
            $classroom = $this->classroomObj($classID); 
            /*----------------------------------------------------------------------
            Create an object of Quizzes. Sets the data to their respective properties
            ---------------------------------------------------------------------*/

            $quizObj = new Quizzes();
            $quizObj->setQuizTitle($quizTitle)
                    ->setNumberOfQuestions($numberofquestions)
                    ->setDeadlineAt(new \DateTime($deadlinedate));
            $em->persist($quizObj);
            $em->flush();

            /*-------------------------------------------------------------------------
            Adds the current quiz to the array of classquizzes inside classroom entity
            ----------------------------------------------------------------------------*/

            $classroom->addClassQuizzes($quizObj);
            $em->persist($classroom);
            $em->flush();

            /*---------------------------------------------------------------------------
            Loops through every question entered in the form
            -----------------------------------------------------------------------------*/

            for($i=1; $i <= $numberofquestions; $i++)
           
            {
                $newQuestion = $request->request->get("q".$i);

                /*------------------------------------------------------------------------------
                Object of QuizQuestions.
                --------------------------------------------------------------------------------*/
                $questionObj = new QuizQuestions();
                $questionObj->setQuestionText($newQuestion);

                $em->persist($questionObj);
                $em->flush();


                /*------------------------------------------------------------------------------
                Adds every question to the array 'question'
                --------------------------------------------------------------------------------*/

                $quizObj->addQuestion($questionObj);
                $em->persist($quizObj);
                $em->flush();           
            }

             $return = array("responseCode"=>200, 
                            "q" => "Quiz Successfully Created" );
        $return = json_encode($return);
        return new Response($return,200,array('Content-Type'=>'application/json'));

        }
    }

    public function ViewQuizPanelAction($classID)
    {
        /*------------------------------------------------------------------------------
         Rendering the view for the Quiz Panel Page
        --------------------------------------------------------------------------------*/
        
        
        $classroom = $this->classroomObj($classID);
        $classQuizzes = $classroom->getClassQuizzes();

         return $this->render('CloudPodClassroomBundle:Classrooms:ClassQuizPanel.html.twig',array(
            'quiz' => $classQuizzes
            ));
    }

    public function ViewQuizAction($classID, $quizID)
    {
        /*------------------------------------------------------------------------------
        Rendering the view of the Quiz selected
        --------------------------------------------------------------------------------*/
        
        $quiz = $this->quizObj($quizID);
        $questions = $quiz->getQuestions(); 

        return $this->render('CloudPodClassroomBundle:Classrooms:ClassQuizView.html.twig',array(
            'quiz' => $quiz,
            'quizQuestions' => $questions));

    }

     public function EditQuizAction($classID, $quizID)
    {
        /*------------------------------------------------------------------------------
        Rendering the view of the Edit Quiz Page
        --------------------------------------------------------------------------------*/

        $quiz = $this->quizObj($quizID);
        $questions = $quiz->getQuestions(); 

        return $this->render('CloudPodClassroomBundle:Classrooms:EditQuiz.html.twig',array(
            'quiz' => $quiz,
            'quizQuestions' => $questions));

    }

    public function UpdateQuizAction(Request $request,$classID, $quizID)
    {
       //Call to Update Quiz
        $em = $this->getDoctrine()->getManager();       
        $quiz = $this->quizObj($quizID);
        $numberofquestions = $quiz->getNumberOfQuestions();
        $title = $request->request->get("qtitle");
        $deadline = $request->request->get("deadline");
       $deadlineHidden = $request->request->get("deadlineHidden");

        if($request->isMethod('POST'))
        {
             if($deadline < $deadlineHidden)
            {
                $this->get('session')->getFlashBag()->add('update', 'Sorry but you cant change your deadline to an earlier date.'); 
            }
            else
            {

            $quiz->setQuizTitle($title)
                 ->setDeadlineAt(new \DateTime($deadline));
            $em->persist($quiz);
            $em->flush();

             for($i=1; $i <= $numberofquestions; $i++)
               
                {
                    $questionText = $request->request->get("q".$i); 
                    $questionID = $request->request->get("hidID".$i); //hidden input that contains the question id

                    $questions_repository = $em->getRepository('CloudPodClassroomBundle:QuizQuestions');
                    $questionObj = $questions_repository->find($questionID);

                    $questionObj->setQuestionText($questionText);


                    $em->persist($questionObj);
                    $em->flush();           
                } 

            $this->get('session')->getFlashBag()->add('update', 'Quiz Updated');      

        }                

    }
        return $this->redirect($this->generateUrl('view_class_quiz', array('classID' => $classID,'quizID' => $quizID)));

    }

    public function ViewAnswerSheetAction($quizID)
    {
        $session = $this->get('session'); 
        $uname = $session->get('username');

        $user_repository = $this->getDoctrine()->getRepository('CloudPodUserBundle:User');
        $username =   $user_repository->findOneByUserName($uname);
        $userID =  $username->getUserID();

        $em = $this->getDoctrine()->getManager();

        //checks the database if the user has already submitted the quiz before.
        $query = $em->createQuery('SELECT s FROM CloudPodClassroomBundle:QuizSubmission s WHERE s.user = :userID and s.quiz = :quizID')
                    ->setParameters(array('userID' => $userID, 'quizID' => $quizID));

   
        try {
        $query->getSingleResult(); 
           return new Response("Sorry but you have already taken the quiz.");
        } catch (\Doctrine\ORM\NoResultException $e) {
        //if the user have not yet taken the quiz.
         $quiz = $this->quizObj($quizID);
        $questions = $quiz->getQuestions(); 

        return $this->render('CloudPodClassroomBundle:Classrooms:ClassQuizAnswerSheet.html.twig',array(
            'quiz' => $quiz,
            'quizQuestions' => $questions));}
      
        }


    public function SubmitAnswerSheetAction($quizID, Request $request ,$classID)
    {
        
        $em = $this->getDoctrine()->getManager();

        $session = $this->getRequest()->getSession();
        $user = $this->UserObj($session->get('username'));

        $quiz = $this->quizObj($quizID);
        $numberofquestions = $quiz->getNumberOfQuestions();

         if($request->isMethod('POST'))
        {

            $submissionObj = new QuizSubmission();
            $submissionObj->setUser($user)
                          ->setQuiz($quiz);

            for($i=1; $i <= $numberofquestions; $i++)
           
            {
                $questionID = $request->request->get("qID".$i);
                $newAnswer = $request->request->get("answer".$i);

                $questionObj = $this->questionObj($questionID);

                $answerObj = new QuizSubmissionAnswer();
                $answerObj->setAnswerText($newAnswer)
                          ->setQuestion($questionObj);
                $em->persist($answerObj);
                $em->flush();

                $submissionObj->addAnswer($answerObj);
                $em->persist($submissionObj);
                $em->flush();
 
            }

            $quiz->addSubmission($submissionObj); 
            $em->persist($quiz);
            $em->flush();         
        
          $this->get('session')->getFlashBag()->add('notice', 'Answers Submitted! Good Luck!');
           
         }
        
      return $this->redirect($this->generateUrl('view_class_quiz_panel', array('classID' => $classID)));

    }

    public function ViewQuizSubmitsListAction($quizID)
    {
        /*----------------------------------
        Displays the list of quiz submits
        ------------------------------------*/
        $quiz = $this->quizObj($quizID);
        $submits = $quiz->getSubmissions();

        return $this->render('CloudPodClassroomBundle:Classrooms:ClassQuizViewSubmitsList.html.twig', array(
            'submits' => $submits));

    }

    public function ViewStudentQuizSubmitAction($submissionID)
    {
         /*-------------------------------------------------
        Displays the content of the student's submitted quiz
        ----------------------------------------------------*/
        $submission = $this->submissionObj($submissionID);
        $answers = $submission->getAnswers();

        return $this->render('CloudPodClassroomBundle:Classrooms:ClassQuizViewSubmit.html.twig', array(
            'submission' =>$submission,
            'answers' => $answers));
    }

    public function DeleteStudentQuizSubmissionAction($classID, $submissionID,$quizID)
    {
        $em = $this->getDoctrine()->getManager();
        $submission = $this->submissionObj($submissionID);
        $em->remove($submission);
        $em->flush();    

        $this->get('session')->getFlashBag()->add('deletenotice', 'Quiz submission deleted!');
        return $this->redirect($this->generateUrl('lecturer_student_quiz_submission_list', array('classID' => $classID,'quizID' => $quizID)));

    }


    public function DeleteQuizAction($classID, $quizID)
    {

        $em = $this->getDoctrine()->getManager();
        $quiz = $this->quizObj($quizID);
        $em->remove($quiz);
        $em->flush();    

        $this->get('session')->getFlashBag()->add('deletenotice', 'Quiz Deleted!');
        return $this->redirect($this->generateUrl('view_class_quiz_panel', array('classID' => $classID)));

    }

    /*------------------------------------
    FUNCTIONS! 
    --------------------------------------*/

    function classroomObj($id)
    {
        $em = $this->getDoctrine()->getManager();
        $class_repository = $em->getRepository('CloudPodClassroomBundle:Classroom');
        $classroom = $class_repository->find($id);

        return $classroom;

    }
    
    function quizObj($id)
    {
        $em = $this->getDoctrine()->getManager();
        $quiz_repository = $em->getRepository('CloudPodClassroomBundle:Quizzes');
        $quiz = $quiz_repository->find($id);

        return $quiz;

    }

    function UserObj($uname)
    {

        $user = $this->getDoctrine()->getRepository('CloudPodUserBundle:User')
                       ->findOneByUserName($uname);

        return $user;

    }

    function questionObj($qID)
    {
        $em = $this->getDoctrine()->getManager();
        $question_repository = $em->getRepository('CloudPodClassroomBundle:QuizQuestions');
        $question = $question_repository->find($qID);

        return $question;

    }

    function submissionObj($id)
    {
        $em = $this->getDoctrine()->getManager();
        $submission_repository = $em->getRepository('CloudPodClassroomBundle:QuizSubmission');
        $submission = $submission_repository->find($id);

        return  $submission;

    }




}