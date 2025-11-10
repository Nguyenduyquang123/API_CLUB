<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class ClubInviteMail extends Mailable
{
    public $club;
    public $inviteLink;

    public function __construct($club)
    {
        $this->club = $club;
        // Sử dụng URL thủ công thay vì named route
        $this->inviteLink = url('/accept-invite?code=' . $club->invite_code);
    }

    public function build()
    {
        return $this->view('emails.club-invite')
                    ->subject('Bạn được mời vào câu lạc bộ ' . $this->club->name);
    }
}
