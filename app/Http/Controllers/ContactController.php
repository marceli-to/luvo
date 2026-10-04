<?php
namespace App\Http\Controllers;
use App\Models\Contact;
use App\Models\Team;
use App\Http\Controllers\BaseController;

class ContactController extends BaseController
{
  protected $viewPath = 'web.pages.contact.';

  public function __construct(Contact $contact, Team $team)
  {
    parent::__construct();
    $this->contact = $contact;
    $this->team = $team;
  }

  /**
   * Page: 'Contact'
   *
   * @return \Illuminate\Http\Response
   */

  public function index()
  { 
    $data['teams'] = [
      'luks' => $this->team->published()->where('slug', 'luks')->with('members')->first(),
      'vogt' => $this->team->published()->where('slug', 'vogt')->with('members')->first()
    ];

    $data['contact'] = $this->contact->published()->with('publishedImages')->orderBy('id')->firstOrFail();
    return view($this->viewPath . 'index', ['data' => $data]);
  }
}
