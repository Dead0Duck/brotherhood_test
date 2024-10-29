<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Developer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ProjectController extends AbstractController
{
    #[Route('/project', name: 'add_project', methods: ['POST'])]
    public function addProject(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
		$body = $request->request->all();

        $project = new Project();
		try
		{
			$project->setName($body['name']);
			$project->setClient($body['client']);
		} catch(\Exception $e) {
			return new Response("Error: ".$e->getMessage(), 400);
		}

		$errors = $validator->validate($project);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

		$entityManager->persist($project);
		$entityManager->flush();

		return new Response('Saved new project with id '.$project->getId().' it name is '.$project->getName());
    }

	#[Route('/project/edit/{id}', name: 'edit_project', methods: ['POST'])]
    public function editProject(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, int $id): Response
    {
		$project = $entityManager->getRepository(Project::Class)->find($id);
        if (!$project) {
			return new JsonResponse(null,404);
		}

		$body = $request->request->all();

		try
		{
			$project->setName($body['name'] ?? $project->getName());
			$project->setClient($body['client'] ?? $project->getClient());
		} catch(\Exception $e) {
			return new Response("Error: ".$e->getMessage(), 400);
		}

		$errors = $validator->validate($project);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

		$entityManager->persist($project);
		$entityManager->flush();

		return new Response('Edited project with id '.$project->getId().' it name is '.$project->getName());
    }

	#[Route('/project/{id}', name: 'get_project', methods: ['GET'])]
    public function getProject(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $project = $entityManager->getRepository(Project::Class)->find($id);
        if (!$project) {
			return new JsonResponse(null,404);
        }

		$encoders = [new JsonEncoder()];
		$normalizers = [new ObjectNormalizer()];
		$serializer = new Serializer($normalizers, $encoders);

		$serialized = $serializer->serialize($project, 'json', [
			'circular_reference_handler' => function (object $object) {
				return $object->getId();
			}
		]);

		$response = new JsonResponse([
			'result' => json_decode($serialized)
		]);
		$response->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        return $response;
    }

	#[Route('/project/{id}', name: 'delete_project', methods: ['DELETE'])]
	public function deleteProject(EntityManagerInterface $entityManager, int $id): Response
    {
		$project = $entityManager->getRepository(Project::Class)->find($id);
        if (!$project) {
			return new Response('Project with id '.$id.' not found',404);
        }

		$entityManager->remove($project);
		$entityManager->flush();

		return new Response('Removed project with id '.$id);
	}

	#[Route('project/{id}/devs/{id2}', name:'add_dev_project', methods: ['PUT'])]
	public function addDevToProject(EntityManagerInterface $entityManager, int $id, int $id2): Response
	{
		$project = $entityManager->getRepository(Project::Class)->find($id);
        if (!$project) {
			return new Response('Project with id '.$id.' not found',404);
        }

		$developer = $entityManager->getRepository(Developer::Class)->find($id2);
		if (!$developer) {
			return new Response('Developer with id '.$id.' not found',404);
		}

		$project->addTeam($developer);

		$entityManager->persist($project);
		$entityManager->flush();

		return new Response('Added developer to project with id '.$project->getId().'. Dev id is '.$developer->getId());
	}

	#[Route('project/{id}/devs/{id2}', name:'delete_dev_project', methods: ['DELETE'])]
	public function deleteDevFromProject(EntityManagerInterface $entityManager, int $id, int $id2): Response
	{
		$project = $entityManager->getRepository(Project::Class)->find($id);
        if (!$project) {
			return new Response('Project with id '.$id.' not found',404);
        }

		$developer = $entityManager->getRepository(Developer::Class)->find($id2);
		if (!$developer) {
			return new Response('Developer with id '.$id.' not found',404);
		}

		$project->removeTeam($developer);

		$entityManager->persist($project);
		$entityManager->flush();

		return new Response('Removed developer from project with id '.$project->getId().'. Dev id is '.$developer->getId());
	}
}
