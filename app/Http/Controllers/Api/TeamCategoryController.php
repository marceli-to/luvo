<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\TeamCategory;
use Illuminate\Http\Request;

class TeamCategoryController extends Controller
{
  public function __construct(TeamCategory $teamCategory)
  {
    $this->teamCategory = $teamCategory;
  }

  /**
   * Get a list of team categories
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  {
    return new DataCollection($this->teamCategory->get());
  }

  /**
   * Get a single team for a given team or authenticated team
   * 
   * @param TeamCategory $teamCategory
   * @return \Illuminate\Http\Response
   */
  public function find(TeamCategory $teamCategory)
  {
    $teamCategory = $this->teamCategory->findOrFail($teamCategory->id);
    return response()->json($teamCategory);
  }
}
