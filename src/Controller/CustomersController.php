<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class CustomersController extends AbstractController
{
	/**
	 * @Route("/customers", name="customers")
	 * @return Response|RedirectResponse
	 */
	public function customersIndex() {
		$em = $this->getDoctrine()->getManager()->getRepository(User::class);
		if($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
			$customers = $em->findAll();
			return $this->render('customers.html.twig', ['customers' => $customers]);
		}
		return new RedirectResponse('/admin');
	}

	/**
	 * @Route("/customer/{id}", name="customer", methods={"GET", "HEAD"})
	 * @return Response|RedirectResponse
	 */
	public function ViewCustomer(Request $request, int $id) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager()->getRepository(User::class);
		$user = $em->find($id);
		if (empty($user)) {
			return new RedirectResponse('/customers');
		}
		return $this->render('user.html.twig', ['user' => $user]);
	}

	/**
	 * @Route("/saveUser/{id}", name="saveUser", methods={"GET", "HEAD"})
	 * @return RedirectResponse
	 */
	public function saveUser(Request $request, int $id) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager();
		$customerRepo = $em->getRepository(User::class);
		/**
		 * @var User $customer
		 */
		$customer = $customerRepo->find($id);
		$name = $request->query->get('inputName');
		$surname = $request->query->get('inputSurname');
		$personCode = $request->query->get('inputPersonCode');
		$comments = $request->query->get('inputComments');
		$accountNumber = $request->query->get('inputAccountNumber');
		// --------------------------------------------------------------
		// add validation functions validate name validate person code etc.
		//
		// ---------------------------------------------------------------
		if (!empty($name)) {
			$customer->setName($name);
		}
		if (!empty($surname)) {
			$customer->setSurname($surname);
		}
		if (!empty($personCode)) {
			$customer->setPersonCode($personCode);
		}
		if (!empty($comments)) {
			$customer->setComments($comments);
		}
		if (!empty($accountNumber)) {
			$customer->setAccountNumber($accountNumber);
		}
		$em->persist($customer);
		$em->flush();
		return new RedirectResponse('/customer/'.$customer->getId());
	}
}