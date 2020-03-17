<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\TechAppRequest;
use App\Models\Tech;
use App\Repositories\TechRepository;

class TechController extends Controller
{
    /**
     * The user repository instance.
     */
    protected $techRepository;

    /**
     * Create a new controller instance.
     *
     * @param  TechRepository $techRepository
     * @return void
     */
    public function __construct(TechRepository $techRepository)
    {
        $this->techRepository = $techRepository;
    }

    public function getAll(){
        return Tech::all();
    }
    public function add(TechAppRequest $request){
        //If validation fails it occurs an error and send json response automatically
        $request->validated();

        //If its all valid we save the entry
        return $this->techRepository->insertTechByAppAction(
            $request->input('ownerName'),
            $request->input('repoName')
        );
    }
}
