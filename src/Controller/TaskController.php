<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/task')]
final class TaskController extends AbstractController
{
    #[Route('/', name: 'app_task_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(TaskRepository $taskRepository): Response
    {
        return $this->render('task/index.html.twig', [
            'tasks' => $taskRepository->findBy(['owner' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setOwner($this->getUser()); 
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Task $task): Response
    {
        if ($task->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Tento úkol nemůžete zobrazit.');
        }

        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        // 1. BEZPEČNOSTNÍ KONTROLA: Patří ten úkol mně?
         if ($task->getOwner() !== $this->getUser()) {
             // Pokud ne, vyhodíme chybu "Přístup odepřen"
            throw $this->createAccessDeniedException('Na tento úkol nemáte právo.');
        }
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Úkol byl úspěšně upraven.');
            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($task->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Na tento úkol nemáte právo.');
        }


        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
            $this->addFlash('success', 'Úkol byl smazán.');
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/toggle', name: 'app_task_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggle(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        // 1. BEZPEČNOST: Patří úkol mně?
        if ($task->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Na toto nemáte právo.');
        }

        // 2. BEZPEČNOST: CSRF token (aby nikdo nemohl klikat za tebe)
        if (!$this->isCsrfTokenValid('toggle' . $task->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Neplatný bezpečnostní token.');
            return $this->redirectToRoute('app_task_index');
        }

        // 3. LOGIKA: Prohození stavu (true -> false, false -> true)
        $task->setIsCompleted(!$task->isCompleted());
        $task->setUpdatedAt(new \DateTime());

        // Uloží změny do databáze
        $entityManager->flush();

        // 4. ZPĚTNÁ VAZBA
        $status = $task->isCompleted() ? 'dokončen' : 'otevřen';
        $this->addFlash('success', "Úkol byl označen jako $status.");

        // Přesměruje uživatele zpět na seznam úkolů
        return $this->redirectToRoute('app_task_index');
    }
}
