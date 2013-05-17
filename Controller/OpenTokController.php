<?php

namespace CloudPod\ClassroomBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CloudPod\UserBundle\Entity\User;
use CloudPod\ClassroomBundle\Entity\Classroom;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class OpenTokController extends Controller
{
    public function openSessionAction($classID)
    {
        $this->includeOpentokSdk();
        $tok = new \OpenTokSDK(22579242,"856e7cc1e1214a67d38e5bad8ae8f119c2b8e2ea");
        
    
        $loc = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    
        $session = $this->get('session'); 
        $userName = $session->get('userName'); 

       

        //FLUSHING Open Tok Session to DB

        $classroom = $this->classroom($classID);

        if (!$classroom->getOpenTokSession()=="")
        {
             $sessionId = $classroom->getOpenTokSession();
        }
        else
        {
        $Toksession = $tok->create_session();
        $sessionId = $Toksession->getSessionId();

        $classroom->setOpenTokSession($sessionId);
        $classroom->setOpenTokIsOnline(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();
        }



        $connectionMetaData = "username=".$userName.",userLevel=1";
        $toktoken = $tok->generate_token($sessionId, "moderator", null, $connectionMetaData);

        return $this->render('CloudPodClassroomBundle:Classrooms:OnlineClassWebConfe.html.twig', array(
        'sessionID' =>  $sessionId,
        'openTokToken' => $toktoken,
        'classroom' => $classroom));
        
    }

    protected function includeOpentokSdk()
    {
      require_once $this->get('kernel')->getRootDir().'/../src/opentok/lib/OpenTok/src/OpenTokSDK.php'
      ;    

    }

    public function joinSessionAction($classID,$sessionvar)
    {
        $this->includeOpentokSdk();
        $tok = new \OpenTokSDK(22579242,"856e7cc1e1214a67d38e5bad8ae8f119c2b8e2ea");

        $loc = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

        $session = $this->get('session'); 
        $userName = $session->get('userName'); 

        $classroom = $this->classroom($classID);

        $sessionId = $sessionvar;
        $connectionMetaData = "username=".$userName.",userLevel=1";
        $toktoken = $tok->generate_token($sessionId, "publisher", null, $connectionMetaData);

        return $this->render('CloudPodClassroomBundle:Classrooms:OnlineClassWebConfe.html.twig', array(
        'sessionID' =>  $sessionId,
        'openTokToken' => $toktoken,
        'classroom' => $classroom));



    }

    function classroom($id)
    {
        $class_repository = $this->getDoctrine()->getRepository('CloudPodClassroomBundle:Classroom');
        $classroom = $class_repository->find($id);

        return $classroom;
    }
}