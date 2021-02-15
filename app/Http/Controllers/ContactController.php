<?php
namespace App\Http\Controllers;
use App\Models\Contact;
use App\Models\Team;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

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
      'luks' => $this->team->bySlug('luks')->with('members')->get()->first(),
      'vogt' => $this->team->bySlug('vogt')->with('members')->get()->first()
    ];

    $data['contact'] = $this->contact->with('publishedImages')->get()->first();
    return view($this->viewPath . 'index', ['data' => $data]);
  }
}
