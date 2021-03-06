<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Kreait\Firebase\Factory;

class Tech extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'techs';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    //Avatar Image upload
    public function setHeroImageAttribute($value)
    {
        error_log("starting setHeroImageAttribute in model tech");
        $attribute_name = "hero_image";
        $disk = config('backpack.base.root_disk_name'); // or use your own disk, defined in config/filesystems.php
        $destination_path = "public/uploads/tech"; // path relative to the disk above

        // if the image was erased
        if ($value==null) {
            // delete the image from disk
            Storage::disk($disk)->delete($this->{$attribute_name});

            // set null in the database column
            $this->attributes[$attribute_name] = null;
        }

        // if a base64 was sent, store it in the db
        if (starts_with($value, 'data:image'))
        {
            error_log("The image is a base 64 string");
            // 0. Make the image
            $image = Image::make($value)->encode('png', 90);
            // 1. Generate a filename.
            $filename = md5($value.time()).'.png';
            switch (env('FILESYSTEM_DRIVER')):
                case 'firebase.storage':
                    $uploadOptions = [
                        'name' => $this->title.".png",
                        'predefinedAcl' => 'publicRead'
                    ];

                    $storage = (new Factory())
                        ->createStorage();
                    $uploadedFile = $storage->getBucket()->upload($image, $uploadOptions);
                    $this->attributes[$attribute_name] = $uploadedFile->info()['mediaLink'];
                    break;
                default:
                    //If we the backpack filesystem is set on local (not in firebase)
                    // 2. Store the image on disk.
                    Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());
                    // 3. Save the public path to the database
                    // but first, remove "public/" from the path, since we're pointing to it from the root folder
                    // that way, what gets saved in the database is the user-accesible URL
                    $public_destination_path = Str::replaceFirst('public', url('/'), $destination_path);

                    $this->attributes[$attribute_name] = $public_destination_path.'/'.$filename;
                    break;
            endswitch;
        }
    }
}
