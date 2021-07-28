<?php


namespace App\Controller;

use App\Entity\Credit;
use App\Entity\Deposit;
use App\Entity\DepositType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DepositController extends AbstractController
{
	/**
	 * @Route("/deposits", name="deposits")
	 * @return Response|RedirectResponse
	 */
	public function depositsIndex() {
		$em = $this->getDoctrine()->getManager()->getRepository(Deposit::class);
		if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
			$deposits = $em->findAll();
		} else if ($this->getUser()) {
			/**
			 * @var User $user
			 */
			$user = $this->getUser();
			$credits = $user->getCredits();
			$creditIds = [];
			foreach ($credits as $credit) {
				$creditIds[] = $credit->getId();
			}

			$deposits = $em->findBy(['credit' => $creditIds]);
		} else {
			return new RedirectResponse('/');
		}
		return $this->render('deposits.html.twig', ['deposits' => $deposits]);
	}

	/**
	 * @Route("/deposits/{id}", name="viewDeposit", methods={"GET", "HEAD"})
	 * @return Response
	 */
	public function viewDeposit(Request $request, int $id) {
		$em = $this->getDoctrine()->getManager()->getRepository(Deposit::class);
		$deposit = $em->find($id);
		if (empty($deposit)) {
			return new RedirectResponse('/deposits');
		}

		$form = $this->get('form.factory')->createNamed('edit_deposit', \App\Form\DepositType::class, $deposit);

		return $this->render('formEdit.html.twig', [
			'form' => $form->createView(),
			'form_name' => 'Edit deposit',
		]);
	}

	/**
	 * @Route("/newDeposit/{id}", name="newDeposit", methods={"GET", "HEAD", "POST"})
	 * @return Response|RedirectResponse
	 */
	public function createDeposit(Request $request, int $id) {
		$em = $this->getDoctrine()->getManager()->getRepository(Deposit::class);
		$emCredits = $this->getDoctrine()->getManager()->getRepository(Credit::class);
		/**
		 * @var Credit $credit
		 */
		$credit = $emCredits->find($id);
		if (!empty($credit->getAcceptDate())) {
			return new RedirectResponse('/credits/'.$id);
		}
		$deposit = new Deposit();
		$deposit->setCredit($credit);
		// ...

		$form = $this->get('form.factory')->createNamed('new_deposit', \App\Form\DepositType::class, $deposit);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			// $form->getData() holds the submitted values
			// but, the original `$deposit` variable has also been updated

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($deposit);
			$entityManager->flush();

			return $this->redirectToRoute('deposits');
		}

		return $this->render('formEdit.html.twig', [
			'form' => $form->createView(),
			'form_name' => 'Create a new deposit',
		]);
	}

	/**
	 * @Route("/deposits/{id}", name="submitDeposit", methods={"POST"})
	 * @return Response|RedirectResponse
	 */
	public function submitEditDeposit(Request $request, int $id)
	{
		$deposit = new Deposit();

		$form = $this->get('form.factory')->createNamed('edit_deposit', \App\Form\DepositType::class, $deposit);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			// $form->getData() holds the submitted values
			// but, the original `$deposit` variable has also been updated
			$deposit = $form->getData();

			$entityManager = $this->getDoctrine()->getManager();
			$repo = $entityManager->getRepository(Deposit::class);
			/**
			 * @var Deposit $changingDeposit
			 */
			$changingDeposit = $repo->find($id);
			if (empty($changingDeposit)) {
				return new RedirectResponse('/admin');
			}
			$changingDeposit->setName($deposit->getName());
			$changingDeposit->setValue($deposit->getValue());
			$changingDeposit->setAddress($deposit->getAddress());
			$changingDeposit->setType($deposit->getType());
			$changingDeposit->setQuantity($deposit->getQuantity());
			$entityManager->persist($changingDeposit);
			$entityManager->flush();

			return $this->redirectToRoute('deposits');
		}

		return $this->render('formEdit.html.twig', [
			'form' => $form->createView(),
			'form_name' => 'Edit deposit',
		]);
	}

	/**
	 * @Route("/deleteDeposit/{id}", name="deleteDeposit")
	 * @return RedirectResponse
	 */
	public function deleteDeposit(Request $request, int $id)
	{
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager();
		$depositRepo = $em->getRepository(Deposit::class);
		/**
		 * @var Deposit $deposit
		 */
		$deposit = $depositRepo->find($id);
		if (!empty($deposit)) {
			$em->remove($deposit);
			$em->flush();
		}
		return new RedirectResponse("/deposits");
	}

	/**
	 * @Route("/depositTypes", name="depositTypes")
	 * @return Response|RedirectResponse
	 */
	public function depositTypesIndex() {
		$em = $this->getDoctrine()->getManager()->getRepository(DepositType::class);
		if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
			$depositTypes = $em->findAll();
		} else {
			return new RedirectResponse('/admin');
		}
		return $this->render('depositTypes.html.twig', ['depositTypes' => $depositTypes]);
	}

	/**
	 * @Route("/depositTypes/{id}", name="viewDepositType", methods={"GET", "HEAD"})
	 * @return Response|RedirectResponse
	 */
	public function viewDepositType(Request $request, int $id) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager()->getRepository(DepositType::class);
		$depositType = $em->find($id);
		if (empty($depositType)) {
			return new RedirectResponse('/depositTypes');
		}

		$form = $this->get('form.factory')->createNamed('edit_deposit_type', \App\Form\DepositTypeType::class, $depositType);
		return $this->render('formEdit.html.twig', [
			'form' => $form->createView(),
			'form_name' => 'Edit deposit type',
		]);
	}

	/**
	 * @Route("/depositTypes/{id}", name="submitDepositType", methods={"POST"})
	 * @return Response
	 */
	public function submitEditDepositType(Request $request, int $id)
	{
		// just setup a fresh $depositType object (remove the example data)
		$depositType = new DepositType();

		$form = $this->createForm(\App\Form\DepositTypeType::class, $depositType);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			// $form->getData() holds the submitted values
			// but, the original `$depositType` variable has also been updated
			$depositType = $form->getData();

			$entityManager = $this->getDoctrine()->getManager();
			$repo = $entityManager->getRepository(DepositType::class);
			/**
			 * @var DepositType $changingDepositType
			 */
			$changingDepositType = $repo->find($id);
			if (empty($changingDepositType)) {
				return new RedirectResponse('/admin');
			}
			$changingDepositType->setName($depositType->getName());
			$entityManager->persist($changingDepositType);
			$entityManager->flush();

			return $this->redirectToRoute('depositTypes');
		}

		return $this->render('formEdit.html.twig', [
			'form' => $form->createView(),
			'form_name' => 'Edit deposit type',
		]);
	}

	/**
	 * @Route("/addDepositType", name="addDepositType", methods={"GET", "HEAD"})
	 * @return Response|RedirectResponse
	 */
	public function addDepositType(Request $request) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$depositType = new DepositType();
		$form = $this->get('form.factory')->createNamed('new_deposit_type', \App\Form\DepositTypeType::class, $depositType);
		return $this->render('formEdit.html.twig', [
			'form' => $form->createView(),
			'form_name' => 'Create a new deposit type',
		]);
	}

	/**
	 * @Route("/addDepositType", name="submitaddDepositType", methods={"POST"})
	 * @return Response|RedirectResponse
	 */
	public function submitAddDepositType(Request $request)
	{
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_DEBTOR')) {
			return new RedirectResponse('/admin');
		}
		$depositType = new DepositType();

		$form = $this->createForm(\App\Form\DepositTypeType::class, $depositType);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			// $form->getData() holds the submitted values
			// but, the original `$depositType` variable has also been updated
			$depositType = $form->getData();

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($depositType);
			$entityManager->flush();

			return $this->redirectToRoute('depositTypes');
		}

		return $this->render('formEdit.html.twig', [
			'form' => $form->createView(),
			'form_name' => 'Create a new deposit type',
		]);
	}

	/**
	 * @Route("/deleteDepositType/{id}", name="deleteDepositType")
	 * @return Response|RedirectResponse
	 */
	public function deleteDepositType(Request $request, int $id)
	{
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager();
		$depositTypeRepo = $em->getRepository(DepositType::class);
		$depositRepo = $em->getRepository(Deposit::class);
		$hasDeposits = !empty($depositRepo->findBy(['type' => $id]));
		if ($hasDeposits) {
			return new Response("Deposits with this type exists. Delete them first.");
		}
		/**
		 * @var DepositType $depositType
		 */
		$depositType = $depositTypeRepo->find($id);
		$em->remove($depositType);
		$em->flush();
		return new RedirectResponse("/depositTypes");
	}
}