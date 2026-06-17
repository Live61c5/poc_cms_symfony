<?php

namespace App\Controller;

use App\Entity\Block;
use App\Form\BlockType;
use App\Repository\BlockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/block')]
class BlockController extends AbstractController
{
    #[Route('/', name: 'app_block_index', methods: ['GET'])]
    public function index(BlockRepository $blockRepository): Response
    {
        return $this->render('block/index.html.twig', [
            'blocks' => $blockRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_block_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $block = new Block();
        $form = $this->createForm(BlockType::class, $block);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($block);
            $entityManager->flush();

            return $this->redirectToRoute('app_block_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('block/new.html.twig', [
            'block' => $block,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_block_show', methods: ['GET'])]
    public function show(Block $block): Response
    {
        return $this->render('block/show.html.twig', [
            'block' => $block,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_block_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Block $block, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BlockType::class, $block);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_block_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('block/edit.html.twig', [
            'block' => $block,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_block_delete', methods: ['POST'])]
    public function delete(Request $request, Block $block, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$block->getId(), $request->request->get('_token'))) {
            $entityManager->remove($block);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_block_index', [], Response::HTTP_SEE_OTHER);
    }
}
