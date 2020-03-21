<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\TechAppRequest;
use App\Models\Tech;
use App\Repositories\TechRepository;
use Illuminate\Http\Request;

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

    public function getByIds($ids){
        error_log($ids);
        return Tech::query()->whereIn('id', explode(',', $ids))->get();
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

    public function addImageToTech(Request $request){
        error_log("start addImageToTech");
        $request->validate([
            'techId' => ['required', 'integer'],
            'image'  => ['required', 'string']
        ]);
        $tech = Tech::query()->where('id', $request->input('techId'))->first();
        if($tech){
            $tech->setHeroImageAttribute($request->input('image'));
            $tech->save();
        }
        error_log("return tech: ".$tech);
        return $tech;
    }
}
