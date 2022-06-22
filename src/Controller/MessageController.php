<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function sendMessage(
        Request $request,
        EntityManagerInterface $em,
        ChannelRepository $channelRepository,
        SerializerInterface $serializer,
        MessageBusInterface $bus
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (empty($content = $data['content'])) {
            throw new AccessDeniedHttpException('No content sent');
        }

        $channel = $channelRepository->find($data['channel']);
        if (!$channel) {
            throw new AccessDeniedHttpException("No channel with id {$data['channel']}");
        }

        // save message
        $message = (new Message)
            ->setAuthor($this->getUser())
            ->setContent($content)
            ->setChannel($channel);
        $em->persist($message);
        $em->flush();

        // serialize
        $jsonMessage = $serializer->serialize($message, 'json', [
            'groups' => ['message'] // On serialize la réponse avant de la renvoyer
        ]);

        // send to mercure hub
        $update = new Update(
            sprintf('http://localhost:8000/channel/%s', $channel->getId()),
            $jsonMessage,
            true
        );
        $bus->dispatch($update);

        return new JsonResponse( // Enfin, on retourne la réponse
            $jsonMessage,
            Response::HTTP_OK,
            [],
            true
        );
    }
}
