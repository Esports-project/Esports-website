<?php

namespace App\Controller;

use App\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;



class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        
        
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);

        return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): Response
    {
        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    ////////////////////////////////json login ////////////////////
    ///
    ///
/*
    /**
     *
     * @Route ("signinJSON" , name ="app_login_JSON" )
     */
/*
    public  function signinActionJSON(Request  $request){

        $usernamel = $request->query->get("username");
        $passwordl = $request->query->get("password");

        $em=$this->getDoctrine()->getManager();
        $utilisateur = $em->getRepository(Utilisateur::class)->findOneBy(['username'=>$usernamel]);

        if($utilisateur){
            if(password_verify($passwordl,$utilisateur->getPassword())){
                $serializer = new Serializer([new ObjectNormalizer()]);
                $formatted =$serializer->normalize($utilisateur);
                return new JsonResponse($formatted);
            }
            else{
                return  new Response("Password incorrect");
            }

        }
        else{
            return  new Response("Username incorrect");
        }


    }

    */

}
