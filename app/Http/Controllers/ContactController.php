<?php
namespace App\Http\Controllers;
use App\Models\Contact;
use App\Models\TeamMember;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class ContactController extends BaseController
{
  protected $viewPath = 'web.pages.contact.';

  public function __construct(Contact $contact, TeamMember $teamMember)
  {
    parent::__construct();
    $this->contact = $contact;
    $this->teamMember = $teamMember;
  }

  /**
   * Page: 'Contact'
   *
   * @return \Illuminate\Http\Response
   */

  public function index()
  { 
    
    $data['team_members'] = $this->teamMember->with('images', 'team.category')->orderBy('order')->get();
    $data['contact'] = $this->contact->with('publishedImages')->get()->first();
    
    return view($this->viewPath . 'index', ['data' => $data]);
  }
}
