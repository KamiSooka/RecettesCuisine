<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{
    #[Route('/recette', name: 'recipe.index')]
    public function index(Request $request, RecipeRepository $repository): Response
    {
        $recipes = $repository->findWithDurationLowerThan(20);
        return $this->render('recipe/index.html.twig', [
           'recipes' => $recipes
       ]);

    }
    #[Route('/recettes/{slug}-{id}', name: 'recipe.show', requirements: ['id' => '\d+', 'slug' => '[\p{L}0-9-]+'])]
    public function show(Request $request, string $slug, int $id, RecipeRepository $repository): Response
    {
        $recipe = $repository->find($id);
        if ($recipe->getSlug() !== $slug) {
            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId(), 'slug' => $recipe->getSlug()]);
        }
        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,

        ]);
    }

    #[Route('/recettes/{id}/edit', name: 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $em): Response{
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'La recette a bien été modifiée');
            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView()
        ]);
    }

    #[Route('/recette/create', name: 'recipe.create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'La recette a bien été créee');
            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('recipe/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/recettes/{id}', name: 'recipe.delete', methods: ['DELETE'])]
    public function remove(Recipe $recipe, EntityManagerInterface $em): Response
    {
        $em->remove($recipe);
        $em->flush();
        $this->addFlash('success', 'La recette a bien été supprimée');
        return $this->redirectToRoute('recipe.index');
    }
}
