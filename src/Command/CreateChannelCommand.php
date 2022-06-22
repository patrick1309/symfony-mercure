<?php

namespace App\Command;

use App\Entity\Channel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'channel:create',
    description: 'Create a new channel',
)]
class CreateChannelCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, public string $name = 'channel:create')
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Channel name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $channel = $this->em->getRepository(Channel::class)->findOneBy([
            'name' => $name
        ]);

        if ($channel) {
            $io->error('Channel already exists');
            return Command::FAILURE;
        }

        $channel = (new Channel)
            ->setName($name);
        $this->em->persist($channel);
        $this->em->flush();

        $io->success("Channel $name successfully created.");

        return Command::SUCCESS;
    }
}
