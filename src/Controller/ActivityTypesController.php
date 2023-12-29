<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ActivityTypeRepository;

class ActivityTypesController extends AbstractController
{
    private $repository;

    public function __construct(ActivityTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Route('/activity-types', name: 'app_activity_types', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $activityTypes = $this->repository->findAll();
        $data = [];

        foreach ($activityTypes as $activityType) {
            $data[] = [
                'id' => $activityType->getId(),
                'name' => $activityType->getName(),
                'requiredMonitors' => $activityType->getRequiredMonitors(),
            ];
        }

        return $this->json($data);
    }
}
