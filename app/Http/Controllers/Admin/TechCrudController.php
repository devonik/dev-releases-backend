<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TechRequest;
use App\Models\Tech;
use App\Repositories\TechRepository;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

/**
 * Class TechCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TechCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * The tech repository implementation.
     *
     * @var TechRepository
     */
    protected $techRepository;

    public function setup()
    {
        $this->crud->setModel('App\Models\Tech');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/tech');
        $this->crud->setEntityNameStrings('tech', 'techs');
        $this->crud->addButtonFromView('line', 'getLatestRelease', 'tech_latest_release', 'end');

        $this->techRepository = new TechRepository();
    }

    protected function setupListOperation()
    {
        $hero_image_column_definition = [
            'label' => "Hero image",
            'name' => "hero_image",
            'type' => 'image'
        ];
        $this->crud->addColumn(['name' => 'id', 'type' => 'text', 'label' => 'Id']);
        $this->crud->addColumn(['name' => 'github_release_id', 'type' => 'text', 'label' => 'Github release id']);
        $this->crud->addColumn(['name' => 'latest_tag', 'type' => 'text', 'label' => 'Latest tag']);
        $this->crud->addColumn(['name' => 'title', 'type' => 'text', 'label' => 'Title']);
        $this->crud->addColumn($hero_image_column_definition);
        $this->crud->addColumn(['name' => 'github_owner', 'type' => 'text', 'label' => 'Github owner']);
        $this->crud->addColumn(['name' => 'github_repo', 'type' => 'text', 'label' => 'Github repo']);
    }

    protected function setupCreateOperation()
    {
        $hero_image_column_definition = [
            'label' => "Hero image",
            'name' => "hero_image",
            'type' => 'image',
            'upload' => true,
            'crop' => false, // set to true to allow cropping, false to disable
            'aspect_ratio' => 2, // Landscape = 2
        ];

        $this->crud->setValidation(TechRequest::class);

        $this->crud->addField(['name' => 'title', 'type' => 'text', 'label' => 'Title']);
        $this->crud->addField($hero_image_column_definition);
        $this->crud->addField(['name' => 'github_owner', 'type' => 'text', 'label' => 'Github owner']);
        $this->crud->addField(['name' => 'github_repo', 'type' => 'text', 'label' => 'Github repo']);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function getLatestRelease($id){
        $tech = Tech::query()->where("id", $id)->first();
        if($tech){
            $this->techRepository->updateTechWithGithubApiRequest($tech);
        }


        return redirect('/admin/tech');
    }
}
