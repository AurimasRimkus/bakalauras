<?php


namespace App\Controller;

use App\Entity\Credit;
use App\Entity\Payment;
use App\Entity\Pricelist;
use App\Entity\User;
use App\Entity\Writeoff;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
	/**
	 * @Route("/users", name="users")
	 */
	public function UsersIndex() {
		$em = $this->getDoctrine()->getManager()->getRepository(User::class);
		if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$users = $em->findAll();
		return $this->render('users.html.twig', ['users' => $users]);
	}

	/**
	 * @Route("/roles/{action}/{id}", name="roles", methods={"GET", "HEAD"})
	 */
	public function rolesManagement(string $action, int $id) {
		$em = $this->getDoctrine()->getManager();
		$userRepo = $em->getRepository(User::class);
		if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		/**
		 * @var User $user
		 */
		$user = $userRepo->find($id);
		switch ($action) {
			case 'mAdmin':
				if (!$user->hasRole('ROLE_ADMIN')) {
					$user->addRole('ROLE_ADMIN');
					$em->persist($user);
				}
			break;
			case 'rAdmin':
				if ($user->hasRole('ROLE_ADMIN')) {
					$user->removeRole('ROLE_ADMIN');
					$em->persist($user);
				}
			break;
			case 'mSuperAdmin':
				if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
					$user->addRole('ROLE_SUPER_ADMIN');
					$em->persist($user);
				}
			break;
			case 'rSuperAdmin':
				if($user->hasRole('ROLE_SUPER_ADMIN')) {
					$user->removeRole('ROLE_SUPER_ADMIN');
					$em->persist($user);
				}
			break;
		}
		$em->flush();
		return new RedirectResponse('/users');
		//return $this->render('users.html.twig', ['users' => $users]);
	}
}