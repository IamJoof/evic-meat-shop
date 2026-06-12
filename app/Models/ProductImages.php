<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImages extends Model
{
    //
    protected $table = 'products';

    protected $fillable = [
        'product_id',
        'image_path',
        'is_primary'
    ];


    // Model Helpers
    public function storeImage(Request $request)
    {
        $directory = 'public';

        if ($request->hasFile('image_path')) {
            if ($this->checkImageExistOnDirectory($request['image_path'], $directory)) {
                return 'Image Already Exists';
            }
            return Storage::disk($directory)->putFile('product', $request['image_path']);
        }
    }

    public function checkImageExistOnDirectory(string $image_path, string $directory): bool
    {
        return Storage::disk($directory)->exists($image_path);
    }
}
