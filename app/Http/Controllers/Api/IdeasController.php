<?php

namespace App\Http\Controllers\Api;

use App\Idea;
use App\Requester;
use App\Rules\ValidRepository;
use App\Settings;
use Illuminate\Http\Response;

class IdeasController extends ApiController
{
    public function index()
    {
        $requester = Requester::whereName(request('requester'))->orWhere('email', '=', request('requester'))->firstOrFail();

        return $this->respond($requester->ideas);
    }

    public function store()
    {
        $this->validate(request(), [
            'requester'  => 'required|array',
            'title'      => 'required|min:3',
            'repository' => new ValidRepository,
        ]);
        $idea = Idea::createAndNotify(
            request('requester'),
            request('title'),
            request('body'),
            request('repository')
        );
        $this->notifyDefault($idea);

        return $this->respond(['id' => $idea->id], Response::HTTP_CREATED);
    }

    private function notifyDefault($ticket)
    {
        $setting = Settings::first();
        if ($setting && $setting->slack_webhook_url) {
            $setting->notify(new IdeaCreated($ticket));
        }
    }
}
