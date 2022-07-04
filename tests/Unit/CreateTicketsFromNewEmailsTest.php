<?php

namespace Tests\Unit;

use App\Jobs\ParseNewEmails;
use App\Requester;
use App\Services\Pop3\FakeMailbox;
use App\Services\Pop3\FakeIncomingMail;
use App\Services\Pop3\Mailbox;
use App\Ticket;
use App\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateTicketsFromNewEmailsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function does_create_tickets_from_new_emails(){
        Notification::fake();
        $fakePop = new FakeMailbox();
        $fakePop->messages = [
            new FakeIncomingMail(["name" => "Bruce Wayne", "email" => "bruce@wayne.com"], "I'm batman", "Why so serious"),
            new FakeIncomingMail(["name" => "Jack Sparrow", "email" => "jack@sparrow.com"], "The black pearl", "I'm so lost.."),
        ];
        app()->instance(Mailbox::class, $fakePop);

        dispatch( new ParseNewEmails() );

        $this->assertEquals(2, Ticket::count());
        tap(Ticket::first(), function($ticket){
            $this->assertEquals("Bruce Wayne", $ticket->requester->name);
            $this->assertEquals("bruce@wayne.com", $ticket->requester->email);
            $this->assertEquals("I'm batman", $ticket->title);
            $this->assertEquals("Why so serious", $ticket->body);
        });
    }

    /** @test */
    public function does_create_comments_for_new_comment_emails(){
        Notification::fake();
        $fakePop = new FakeMailbox();
        $agent                              = factory(User::class)     ->create(["email" => "tony@stark.com"]);
        $ticketWithCommentRequester         = factory(Requester::class)->create(["email" => "peter@parker.com"]);
        $ticketThatWillGetTheCommentByMail  = factory(Ticket::class)   ->create(["id" => 18, "requester_id" => $ticketWithCommentRequester ]);
        $fakePop->messages = [
            new FakeIncomingMail(["name" => "Jack Sparrow", "email" => "tony@stark.com"], "Reply to of ticket", $ticketReplyBody    ),
            new FakeIncomingMail(["name" => "Peter Parker", "email" => "peter@parker.com"], "Reply to of ticket 2", $ticketReplyBody    ),
        ];

        app()->instance(Mailbox::class, $fakePop);

        dispatch( new ParseNewEmails() );

        $this->assertEquals(1, Ticket::count());

        $this->assertCount(2, $ticketThatWillGetTheCommentByMail->comments);
        tap($ticketThatWillGetTheCommentByMail->comments->first(), function($comment){
            $this->assertEquals("The email reply", $comment->body);
            $this->assertEquals("tony@stark.com", $comment->user->email);
        });

        tap($ticketThatWillGetTheCommentByMail->comments->last(), function($comment){
            $this->assertEquals("The email reply", $comment->body);
            $this->assertNull($comment->user);
        });
    }

    /** @test */
    public function a_comment_from_requester_email_changes_status_to_open(){
        Notification::fake();
        $fakePop = new FakeMailbox();
        $agent                              = factory(User::class)     ->create(["email" => "tony@stark.com"]);
        $ticketWithCommentRequester         = factory(Requester::class)->create(["email" => "peter@parker.com"]);
        $ticketThatWillGetTheCommentByMail  = factory(Ticket::class)   ->create(["id" => 18, "requester_id" => $ticketWithCommentRequester, "status" => Ticket::STATUS_SOLVED ]);

        $this->assertEquals(Ticket::STATUS_SOLVED, $ticketThatWillGetTheCommentByMail->fresh()->status);
        $fakePop->messages = [
            new FakeIncomingMail(["name" => "Peter Parker", "email" => "peter@parker.com"], "Reply to of ticket 2", $ticketReplyBody    ),
        ];

        app()->instance(Mailbox::class, $fakePop);

        dispatch( new ParseNewEmails() );

        $this->assertEquals(1, Ticket::count());
        $this->assertEquals(Ticket::STATUS_OPEN, $ticketThatWillGetTheCommentByMail->fresh()->status);
    }
}
