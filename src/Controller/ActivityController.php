<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ActivityRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Activity;
use App\Entity\Monitor;
use App\Entity\ActivityType;
use DateTime;

class ActivityController extends AbstractController
{

    private $repository;

    public function __construct(ActivityRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Route('/activities', name: 'app_activity', methods: ['GET'])]     
    public function getActivities(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $repository = $em->getRepository(Activity::class);

        $date_start = $request->query->get('date_start');
        if ($date_start) {
            $date_start = DateTime::createFromFormat('d-m-Y', $date_start);
            $date_end = clone $date_start;
            $date_end->modify('+1 day');
    
            $activities = $repository->createQueryBuilder('a')
                ->where('a.date_start >= :date_start')
                ->andWhere('a.date_start < :date_end')
                ->setParameter('date_start', $date_start)
                ->setParameter('date_end', $date_end)
                ->getQuery()
                ->getResult();
            
        } else {
            $activities = $repository->findAll();
        }

        $data = [];
        foreach ($activities as $activity) {
            $monitors = array_map(function ($monitor) {
                return [
                    'id' => $monitor->getId(),
                    'name' => $monitor->getName(),
                    'email' => $monitor->getEmail(),
                    'phone' => $monitor->getPhone(),
                    'photo' => $monitor->getPhoto()
                ];
            }, $activity->getMonitors()->toArray());

            $data[] = [
                'id' => $activity->getId(),
                'activity_type' => [
                    'id' => $activity->getActivityType()->getId(),
                    'name' => $activity->getActivityType()->getName(),
                    'number-monitors' => count($monitors)
                ],
                'monitors' => $monitors,
                'date_start' => $activity->getDateStart()->format('c'),
                'date_end' => $activity->getDateEnd()->format('c'),
            ];
        }

        return $this->json($data);
    }

    #[Route('/activities', methods: ['POST'])]
    public function createActivity(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        $activityTypeRepository = $em->getRepository(ActivityType::class);
        $monitorRepository = $em->getRepository(Monitor::class);
    
        $activityType = $activityTypeRepository->find($data['activity_type_id']);
        if (!$activityType) {
            return $this->json(['code' => 21, 'description' => 'Activity type not found'], 400);
        }
        //We accept more than the required monitors, but not less
        if (count($data['monitors_id']) < $activityType->getRequiredMonitors()) {
            return $this->json(['code' => 21, 'description' => 'Not enough monitors for this activity type'], 400);
        }
    
        $monitors = $monitorRepository->findBy(['id' => $data['monitors_id']]);
        if (count($monitors) !== count($data['monitors_id'])) {
            return $this->json(['code' => 21, 'description' => 'Some monitors not found'], 400);
        }
    
        $dateStart = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $data['date_start']);
        $dateEnd = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $data['date_end']);
    
        $allowedStartTimes = ['09:00', '13:30', '17:30'];
        $duration = $dateStart->diff($dateEnd);
        $totalMinutes = $duration->days * 24 * 60;
        $totalMinutes += $duration->h * 60;
        $totalMinutes += $duration->i;
        
        if (!in_array($dateStart->format('H:i'), $allowedStartTimes) || $totalMinutes != 90) {
            return $this->json(['code' => 21, 'description' => 'Invalid start time or duration'], 400);
        }
        
        $activity = new Activity();
        $activity->setActivityType($activityType);
        foreach ($monitors as $monitor) {
            $activity->addMonitor($monitor);
        }
        $activity->setDateStart($dateStart);
        $activity->setDateEnd($dateEnd);
    
        $em->persist($activity);
        $em->flush();
    
        $activityData = $this->transformActivity($activity);
    
        return $this->json($activityData, 200);
    }
    
    #[Route('/activities/{activityId}', name: 'app_activity_update', methods: ['PUT'])]
    public function updateActivity(int $activityId, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $repository = $em->getRepository(Activity::class);
        $activity = $repository->find($activityId);
    
        if (!$activity) {
            return $this->json(['message' => 'Activity not found'], 404);
        }
    
        $data = json_decode($request->getContent(), true);
    
        $activityTypeRepository = $em->getRepository(ActivityType::class);
        $activityType = $activityTypeRepository->find($data['activity_type_id']);
    
        if ($activityType) {
            $activity->setActivityType($activityType);
        }
    
        //We accept more than the required monitors, but not less
        if (count($data['monitors_id']) < $activity->getActivityType()->getRequiredMonitors()) {
            return $this->json(['code' => 21, 'description' => 'Not enough monitors for this activity type'], 400);
        }
    
        $monitorRepository = $em->getRepository(Monitor::class);
        $monitors = $monitorRepository->findBy(['id' => $data['monitors_id']]);
        if (count($monitors) !== count($data['monitors_id'])) {
            return $this->json(['code' => 21, 'description' => 'Some monitors not found'], 400);
        }
    
        if (isset($data['date_start'])) {
            $dateStart = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $data['date_start']);
            if (!$dateStart) {
                return $this->json(['code' => 21, 'description' => 'Invalid start date format'], 400);
            }
            $activity->setDateStart($dateStart);
        }
    
        if (isset($data['date_end'])) {
            $dateEnd = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $data['date_end']);
            if (!$dateEnd) {
                return $this->json(['code' => 21, 'description' => 'Invalid end date format'], 400);
            }
            $activity->setDateEnd($dateEnd);
        }
    
        // Check if the activity starts at 09:00, 13:30, or 17:30 and lasts for 90 minutes
        $allowedStartTimes = ['09:00', '13:30', '17:30'];
        $duration = $dateStart->diff($dateEnd);
        $totalMinutes = $duration->days * 24 * 60;
        $totalMinutes += $duration->h * 60;
        $totalMinutes += $duration->i;
    
        if (!in_array($dateStart->format('H:i'), $allowedStartTimes) || $totalMinutes != 90) {
            return $this->json(['code' => 21, 'description' => 'Invalid start time or duration'], 400);
        }
        foreach ($activity->getMonitors() as $monitor) {
            $activity->removeMonitor($monitor);
        }
        
        foreach ($monitors as $monitor) {
            $activity->addMonitor($monitor);
        }
    
        $em->flush();
    
        $activityData = $this->transformActivity($activity);
    
        return $this->json($activityData);
    }
    

    #[Route('/activities/{activityId}', name: 'app_activity_delete', methods: ['DELETE'])]
    public function deleteActivity(int $activityId, EntityManagerInterface $em): JsonResponse
    {
        $repository = $em->getRepository(Activity::class);
        $activity = $repository->find($activityId);

        if (!$activity) {
            return $this->json(['message' => 'Activity not found'], 404);
        }

        $em->remove($activity);
        $em->flush();

        return $this->json(['message' => 'Activity deleted successfully']);
    }
    private function transformActivity(Activity $activity): array
{
    $monitors = array_map(function ($monitor) {
        return [
            'id' => $monitor->getId(),
            'name' => $monitor->getName(),
            'email' => $monitor->getEmail(),
            'phone' => $monitor->getPhone(),
            'photo' => $monitor->getPhoto()
        ];
    }, $activity->getMonitors()->toArray());

    return [
        'id' => $activity->getId(),
        'activity_type' => [
            'id' => $activity->getActivityType()->getId(),
            'name' => $activity->getActivityType()->getName(),
            'number-monitors' => count($monitors)
        ],
        'monitors' => $monitors,
        'date_start' => $activity->getDateStart()->format('c'),
        'date_end' => $activity->getDateEnd()->format('c'),
    ];
}



}
