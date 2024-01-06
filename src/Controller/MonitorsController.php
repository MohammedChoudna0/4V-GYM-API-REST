<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MonitorRepository;
use App\Entity\Monitor;

class MonitorsController extends AbstractController
{
    private $repository;

    public function __construct(MonitorRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Route('/monitors', methods: ['GET'])]
    public function getMonitors(): JsonResponse
    {
        $monitors = $this->repository->findAll();
        $data = [];

        foreach ($monitors as $monitor) {
            $data[] = [
                'id' => $monitor->getId(),
                'name' => $monitor->getName(),
                'email' => $monitor->getEmail(),
                'phone' => $monitor->getPhone(),
                'photo' => $monitor->getPhoto(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/monitors', methods: ['POST'])]
    public function createMonitor(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['email'])) {
            return $this->json(['message' => 'Required fields are missing'], 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['message' => 'The email is not valid'], 400);
        }
        
        if (isset($data['phone']) && !is_numeric($data['phone'])) {
            return $this->json(['message' => 'The phone number must be numeric'], 400);
        }
        


        $monitor = new Monitor();
        $monitor->setName($data['name']);
        $monitor->setEmail($data['email']);
        $monitor->setPhone($data['phone']);
        $monitor->setPhoto($data['photo']);
        

        $em->persist($monitor);
        $em->flush();
        //Why not  return $this->json($monitor); implements \JsonSerializable
        return $this->json([
            'id' => $monitor->getId(),
            'name' => $monitor->getName(),
            'email' => $monitor->getEmail(),
            'phone' => $monitor->getPhone(),
            'photo' => $monitor->getPhoto(),
        ]);    }

    #[Route('/monitors/{id}', methods: ['PUT'])]
    public function updateMonitor(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $monitor = $this->repository->find($id);
        $monitor->setName($data['name']);
        $monitor->setEmail($data['email']);
        $monitor->setPhone($data['phone']);
        $monitor->setPhoto($data['photo']);

        $em->flush();

        return $this->json([
            'id' => $monitor->getId(),
            'name' => $monitor->getName(),
            'email' => $monitor->getEmail(),
            'phone' => $monitor->getPhone(),
            'photo' => $monitor->getPhoto(),
        ]);
    }

    #[Route('/monitors/{id}', methods: ['DELETE'])]
    public function deleteMonitor(int $id, EntityManagerInterface $em): JsonResponse
    {
        $monitor = $this->repository->find($id);

        $em->remove($monitor);
        $em->flush();

        return $this->json(['message' => 'Monitor deleted']);
    }
}
