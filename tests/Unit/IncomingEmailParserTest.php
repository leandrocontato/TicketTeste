<?php

namespace Tests\Unit;

use App\Requester;
use App\Services\Pop3\FakeIncomingMail;
use App\Services\Pop3\IncomingMailCommentParser;
use App\Ticket;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomingEmailParserTest extends TestCase{

    use RefreshDatabase;

    /** @test */
    public function can_detect_replies_as_comments() {
        $newTicketBody = "This is a new ticket body";

        $parser1 = new IncomingMailCommentParser(new FakeIncomingMail(["name" => "Bruce Wayne", "email" => "bruce@wayne.com"], "I'm batman", $newTicketBody));
        $parser2 = new IncomingMailCommentParser(new FakeIncomingMail(["name" => "Jack Sparrow", "email" => "jack@sparrow.com"], "The black pearl", $ticketReplyBody));

        $this->assertFalse($parser1->isAComment());
        $this->assertTrue($parser2->isAComment());
            $parser2->getCommentBody());
        $this->assertEquals(18, $parser2->getTicketId());
    }

    /** @test */
    public function comment_parser_returns_null_user_if_is_the_requester() {
        $user = factory(User::class)->create(["email" => "james@bond.com"]);
        $requester = factory(Requester::class)->create(["email" => "james@bond.com"]);
        $ticket = factory(Ticket::class)->create(["requester_id" => $requester->id]);
        $parser1 = new IncomingMailCommentParser(new FakeIncomingMail(["name" => "Bruce Wayne", "email" => "james@bond.com"], "I'm batman", "##- Please type your reply above this line -## ticket-id:1."));

        $this->assertNull($parser1->getUser($ticket));
    }

    /** @test */
    public function comment_parser_returns_the_user_if_it_is_not_the_requester() {
        $user = factory(User::class)->create(["email" => "bruce@wayne.com"]);
        $requester = factory(Requester::class)->create(["email" => "james@bond.com"]);
        $ticket = factory(Ticket::class)->create(["requester_id" => $requester->id]);
        $parser1 = new IncomingMailCommentParser(new FakeIncomingMail(["name" => "Bruce Wayne", "email" => "bruce@wayne.com"], "I'm batman", "##- Please type your reply above this line -## ticket-id:1."));

        $this->assertEquals("bruce@wayne.com", $parser1->getUser($ticket)->email);
    }

    /** @test */
    public function comment_parser_returns_null_if_it_is_an_unknown_email() {
        $requester = factory(Requester::class)->create(["email" => "james@bond.com"]);
        $ticket = factory(Ticket::class)->create(["requester_id" => $requester->id]);
        $parser1 = new IncomingMailCommentParser(new FakeIncomingMail(["name" => "Bruce Wayne", "email" => "unkown@bond.com"], "I'm batman", "##- Please type your reply above this line -## ticket-id:1."));

        $this->assertNull($parser1->getUser($ticket));
    }
}
