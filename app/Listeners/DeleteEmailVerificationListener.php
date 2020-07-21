<?php

namespace App\Listeners;

use App\Events\EmailVerifiedEvent;
use Doctrine\ORM\EntityManagerInterface;

class DeleteEmailVerificationListener
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function handle(EmailVerifiedEvent $event)
    {
        if ($verification = $event->user->getEmailVerification()) {
            $this->em->remove($verification);
            $this->em->flush();
        }
    }
}
