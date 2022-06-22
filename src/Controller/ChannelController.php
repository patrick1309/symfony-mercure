<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Repository\ChannelRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\Discovery;
use Symfony\Component\Routing\Annotation\Route;

class ChannelController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ChannelRepository $channelRepository): Response
    {
        $channels = $channelRepository->findAll();
        return $this->render('channel/index.html.twig', [
            'channels' => $channels ?? [],
        ]);
    }

    #[Route('/chat/{id}', name: 'chat')]
    public function chat(
        Request $request,
        Channel $channel,
        MessageRepository $messageRepository,
        Discovery $discovery,
        Authorization $authorization
    ): Response
    {
        $messages = $messageRepository->findBy(['channel' => $channel], ['createdAt' => 'ASC']);
        $authorization->setCookie($request, ['http://localhost:8000/channel/'.$channel->getId()]);
        $discovery->addLink($request); 
        
        return $this->render('channel/chat.html.twig', [
            'channel' => $channel,
            'messages' => $messages
        ]);
    }
}
