<?php

namespace App\Http\Controllers\System;

use App\Entities\EmailEvent;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MailgunController extends Controller
{
    /**
     * Parameters from Mailgun to exclude from the event data field.
     *
     * @var array
     */
    protected $excludeData = ['Message-Id', 'message-id', 'event', 'recipient', 'timestamp', 'token', 'signature'];
    protected EntityManagerInterface $entityManager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->middleware('auth.basic.once');
        $this->entityManager = $entityManager;
    }

    /**
     * Record an email event.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function event(Request $request)
    {
        if (!$this->verifyMailgun($request)) {
            return Response::make('Not Acceptable.', 406);
        }

        $emailEvent = new EmailEvent();
        $emailEvent->setBroker('mailgun');
        $emailEvent->setMessageId($request->input('Message-Id') ?: $request->input('message-id'));
        $emailEvent->setName($request->input('event'));
        $emailEvent->setRecipient($request->input('recipient'));
        $emailEvent->setData(array_diff_key($request->all(), array_flip($this->excludeData)));
        $emailEvent->setTriggeredAt(Carbon::createFromTimestamp($request->input('timestamp')));
        $this->entityManager->persist($emailEvent);
        $this->entityManager->flush();

        return response('');
    }

    /**
     * Verify the request is genuine.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function verifyMailgun(Request $request)
    {
        $data = $request->input('timestamp').$request->input('token');
        $signature = hash_hmac('sha256', $data, config('services.mailgun.secret'));

        return $signature === $request->input('signature');
    }
}
