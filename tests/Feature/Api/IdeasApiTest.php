<?php

namespace Tests\Feature;

use App\Authenticatable\Admin;
use App\Idea;
use App\Notifications\IdeaCreated;
use App\Requester;
use App\User;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IdeasApiTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithExceptionHandling;

    public function setUp() : void{
        parent::setUp(); // TODO: Change the autogenerated stub
        Notification::fake();
    }

    private function validParams($overrides = []){
        return array_merge([
            "requester" => [
                "name"  => "johndoe",
                "email" => "john@doe.com"
            ],
            "title"         => "My super idea!",
            "body"          => "Why not making this and that",
            "repository"    => "revo-pos/revo-back",
        ], $overrides);
    }

    /** @test */
    public function can_create_an_idea(){
        $admin      = factory(Admin::class)->create();
        $nonAdmin   = factory(User::class)->create(["admin" => 0]);

        $response = $this->post('api/ideas',[
            "requester" => [
                "name"  => "johndoe",
                "email" => "john@doe.com"
            ],
            "title"         => "My super idea!",
            "body"          => "Why not making this and that",
            "repository"    => "revo-pos/revo-back",
        ],["token" => 'the-api-token']);

        $response->assertStatus( Response::HTTP_CREATED );
        $response->assertJson(["data" => ["id" => 1]]);
        $this->assertEquals(1, Idea::count());
        tap( Idea::first(), function($idea) use ($admin) {
            tap( Requester::first(), function($requester) use ($idea){
                $this->assertEquals($requester->name, "johndoe");
                $this->assertEquals($requester->email, "john@doe.com");
                $this->assertEquals( $idea->requester_id, $requester->id);
            });
            $this->assertEquals ( $idea->title, "My super idea!");
            $this->assertEquals ( $idea->repository, "revo-pos/revo-back");
            $this->assertEquals ( $idea->body, "Why not making this and that");
            $this->assertEquals( Idea::STATUS_NEW, $idea->status);

            Notification::assertSentTo(
                [$admin],
                IdeaCreated::class,
                function ($notification, $channels) use ($idea) {
                    return $notification->idea->id === $idea->id;
                }
            );
        });
    }

    /** @test */
    public function requester_is_required(){
        $response = $this->post('api/ideas',$this->validParams([
            "requester" => "",
        ]),["token" => 'the-api-token']);
        $response->assertStatus( Response::HTTP_UNPROCESSABLE_ENTITY );
        $response->assertJsonStructure([
            "error"
        ]);
        $this->assertEquals(0, Idea::count() );
    }

    /** @test */
    public function title_is_required(){
        $response = $this->post('api/ideas',$this->validParams([
            "title" => "",
        ]),["token" => 'the-api-token']);
        $response->assertStatus( Response::HTTP_UNPROCESSABLE_ENTITY );
        $response->assertJsonStructure([
            "error"
        ]);
        $this->assertEquals(0, Idea::count() );
    }

    /** @test */
    public function repository_must_be_valid(){
        $response = $this->post('api/ideas',$this->validParams([
            "repository" => "Invalid repo"
        ]),["token" => 'the-api-token']);
        $response->assertStatus( Response::HTTP_UNPROCESSABLE_ENTITY );
        $response->assertJsonStructure([
            "error"
        ]);
        $this->assertEquals(0, Idea::count() );
    }

    /** @test */
    public function repository_is_not_required(){
        $response = $this->post('api/ideas',$this->validParams([
            "repository" => null
        ]),["token" => 'the-api-token']);
        $response->assertStatus( Response::HTTP_CREATED );
        $this->assertEquals(1, Idea::count() );
    }

    /** @test */
    public function can_create_a_ticket_without_requester_email(){
        Notification::fake();

        $response = $this->post('api/ideas',[
            "requester" => [
                "name"  => "johndoe",
            ],
            "title"         => "App is not working",
            "body"          => "I can't log in into the application",
        ],["token" => 'the-api-token']);

        $response->assertStatus( Response::HTTP_CREATED );
        $response->assertJson(["data" => ["id" => 1]]);
    }

    /** @test */
    public function creating_a_ticket_of_a_requester_without_email_does_not_use_another_requester_without_email(){
        Notification::fake();

        factory(Requester::class)->create(["name" => "First requester", "email" => null]);
        $response = $this->post('api/ideas',[
            "requester" => [
                "name"  => "Second Requester",
            ],
            "title"         => "App is not working",
            "body"          => "I can't log in into the application",
        ],["token" => 'the-api-token']);

        $response->assertStatus( Response::HTTP_CREATED );
        $response->assertJson(["data" => ["id" => 1]]);
        tap(Idea::first(), function($ticket){
            $this->assertEquals("Second Requester", $ticket->requester->name);
            $this->assertNull($ticket->requester->email);
        });
    }

    /** @test */
    public function can_get_ideas_tickets(){
        $requester = factory(Requester::class)->create(["name" => "requesterName" ]);
        factory(Idea::class,3)->create(["requester_id" => $requester->id]);
        factory(Idea::class,2)->create(["requester_id" => $requester->id, "status" => Idea::STATUS_CLOSED]);
        factory(Idea::class,2)->create();

        $response = $this->get("api/ideas?requester=requesterName",["token" => 'the-api-token']);

        $response->assertJsonStructure([
            "data" => [
                "*" => [ "title", "status", "created_at", "updated_at" ]
            ]
        ]);

        $responseJson = json_decode( $response->content() );
        $this->assertCount(5, $responseJson->data);
        $this->assertEquals(1, $responseJson->data[0]->id);
    }
}
