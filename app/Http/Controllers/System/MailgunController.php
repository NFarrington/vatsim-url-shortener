<?php

namespace App\Http\Controllers\System;

use App\Models\EmailEvent;
use Carbon\Carbon;
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth.basic.once');
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

        $entry = [
            'broker' => 'mailgun',
            'message_id' => $request->input('Message-Id') ?: $request->input('message-id'),
            'name' => $request->input('event'),
            'recipient' => $request->input('recipient'),
            'data' => array_diff_key($request->all(), array_flip($this->excludeData)),
            'triggered_at' => Carbon::createFromTimestamp($request->input('timestamp')),
        ];

        EmailEvent::create($entry);

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
