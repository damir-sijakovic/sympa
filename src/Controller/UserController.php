<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Helper\UtilityHelper;

class UserController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $utilityHelper;
    
	public function __construct(EntityManagerInterface $entityManager, 
        UtilityHelper $utilityHelper,
        UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;	
        $this->utilityHelper = $utilityHelper;	
	}
    

    public function loginPost(Request $request): Response
    {
        $session = $request->getSession();	
        $email = $request->request->get('email');
		$password = $request->request->get('password');
        
        $userRepository = $this->entityManager->getRepository(User::class);
		$user = $userRepository->findOneBy(['email' => $email]);
        
        if (!$user) {
			return $this->json(['message' => 'User not found!'], Response::HTTP_NOT_FOUND);
		}
        
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
			return $this->json(['message' => 'Password is invalid!'], Response::HTTP_NOT_FOUND);
		}
		
        $session->set('userLoggedIn', time());
		$session->set('sessionId', $this->utilityHelper->generateRandomString(32));
		$session->set('userId', $email);
        
        return $this->json(['message' => 'User logged on.']);   
    }


    public function loginGet(): Response
    {
		return $this->render('/user/login.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

	
    public function registerPost(Request $request): Response
    {
        $email = $request->request->get('email');
		$password = $request->request->get('password');
		
		$userRepository = $this->entityManager->getRepository(User::class);
		$user = $userRepository->findOneBy(['email' => $email]);
		
		if ($user) {
			return $this->json(['message' => 'User with this email already exists!'], Response::HTTP_NOT_FOUND);
		}
		
		$hashedPassword = $this->passwordHasher->hashPassword(new User(), $password);

		$user = new User();
		$user->setEmail($email)
			 ->setPassword($hashedPassword)
			 ->setCreatedAt(new \DateTimeImmutable())
			 ->setModifiedAt(new \DateTimeImmutable())
			 ->setActive(1); 
			 
		$this->entityManager->persist($user);
		$this->entityManager->flush();

		return $this->json(['message' => 'User registrated!']);
    }



    public function registerGet(): Response
    {
		return $this->render('/user/register.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }


    public function logoutGet(Request $request): Response
    {
        $session = $request->getSession();	
        $session->remove('userLoggedIn');
		$session->remove('sessionId');
		$session->remove('userId');
        
        return new RedirectResponse('/');
    }



	

};
