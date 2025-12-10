<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use Response;
use Auth;
use Storage;
use Image;
use Carbon\Carbon;
use Log;
use App\Jobs\VideoCompression;

class AizUploadController extends Controller
{


    public function index(Request $request){


        $all_uploads = (auth()->user()->user_type == 'seller') ? Upload::where('user_id',auth()->user()->id) : Upload::query();
        $search = null;
        $sort_by = null;

        if ($request->search != null) {
            $search = $request->search;
            $all_uploads->where('file_original_name', 'like', '%'.$request->search.'%');
        }

        $sort_by = $request->sort;
        switch ($request->sort) {
            case 'newest':
                $all_uploads->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $all_uploads->orderBy('created_at', 'asc');
                break;
            case 'smallest':
                $all_uploads->orderBy('file_size', 'asc');
                break;
            case 'largest':
                $all_uploads->orderBy('file_size', 'desc');
                break;
            default:
                $all_uploads->orderBy('created_at', 'desc');
                break;
        }

        $all_uploads = $all_uploads->paginate(60)->appends(request()->query());


        return (auth()->user()->user_type == 'seller')
            ? view('frontend.user.seller.uploads.index', compact('all_uploads', 'search', 'sort_by'))
            : view('backend.uploaded_files.index', compact('all_uploads', 'search', 'sort_by'));
    }

    public function create(){
        return (auth()->user()->user_type == 'seller')
            ? view('frontend.user.seller.uploads.create')
            : view('backend.uploaded_files.create');
    }


    public function show_uploader(Request $request){
        return view('uploader.aiz-uploader');
    }
    public function upload(Request $request){
        $type = array(
            "jpg"=>"image",
            "jpeg"=>"image",
            "png"=>"image",
            "svg"=>"image",
            "webp"=>"image",
            "gif"=>"image",
            "mp4"=>"video",
            "mpg"=>"video",
            "mpeg"=>"video",
            "webm"=>"video",
            "ogg"=>"video",
            "avi"=>"video",
            "mov"=>"video",
            "flv"=>"video",
            "swf"=>"video",
            "mkv"=>"video",
            "wmv"=>"video",
            "wma"=>"audio",
            "aac"=>"audio",
            "wav"=>"audio",
            "mp3"=>"audio",
            "zip"=>"archive",
            "rar"=>"archive",
            "7z"=>"archive",
            "doc"=>"document",
            "txt"=>"document",
            "docx"=>"document",
            "pdf"=>"document",
            "csv"=>"document",
            "xml"=>"document",
            "ods"=>"document",
            "xlr"=>"document",
            "xls"=>"document",
            "xlsx"=>"document"
        );

        if($request->hasFile('aiz_file')){
            $upload = new Upload;
            $processed = false;
            $extension = strtolower($request->file('aiz_file')->getClientOriginalExtension());

            if(isset($type[$extension])){
                $upload->file_original_name = null;
                $arr = explode('.', $request->file('aiz_file')->getClientOriginalName());
                for($i=0; $i < count($arr)-1; $i++){
                    if($i == 0){
                        $upload->file_original_name .= $arr[$i];
                    }
                    else{
                        $upload->file_original_name .= ".".$arr[$i];
                    }
                }

                $path = $request->file('aiz_file')->store('uploads/all', 'local');
                $size = $request->file('aiz_file')->getSize();

                // Return MIME type ala mimetype extension
                $finfo = finfo_open(FILEINFO_MIME_TYPE);

                // Get the MIME type of the file
                $file_mime = finfo_file($finfo, base_path('public/').$path);

                if($type[$extension] == 'image' && get_setting('disable_image_optimization') != 1){
                    try {
                        $img = Image::make($request->file('aiz_file')->getRealPath())->encode($extension, 60);
                        $height = $img->height();
                        $width = $img->width();
						$newwidth = $width;
						$newheight = $height;
                        if($width > $height && $width > 1500){
                            $img->resize(1500, null, function ($constraint) {
                                $constraint->aspectRatio();
                            });
							$substractwidth = $width - 1500;
							$ratioper = (1500 / $width) * 100;
							$newwidth = 1500;
							$newheight = ($height * $ratioper) / 100;
                        }elseif ($height > 1500) {
                            $img->resize(null, 800, function ($constraint) {
                                $constraint->aspectRatio();
                            });
							$substractheight = $height - 800;
							$ratioper = (800 / $height) * 100;
							$newwidth = ($width * $ratioper) / 100;
							$newheight = 800;
                        }
						$center_width = $newwidth - 270;
						$center_height = $newheight - 60;
						
                        $img->text(Carbon::now()->year.' '.env('APP_NAME').' - All Rights Reserved', $center_width, $center_height, function($font) {
                            $font->file(base_path('public/').'assets/fonts/BalooBhaijaan2-Regular.ttf');
                            $font->size(30);
                            $font->color([255, 255, 255, 0.7]);
                            $font->align('center');
                            $font->valign('top');
                        });
                        $img->save(base_path('public/').$path);
                        clearstatcache();
                        $size = $img->filesize();
                        $processed = true;

                    } catch (\Exception $e) {
                        //dd($e);
                    }
                }

                if (env('FILESYSTEM_DRIVER') == 's3') {
                    Storage::disk('s3')->put(
                        $path,
                        file_get_contents(base_path('public/').$path),
                        [
                            'visibility' => 'public',
                            'ContentType' =>  $extension == 'svg' ? 'image/svg+xml' : $file_mime
                        ]
                    );
                    if($arr[0] != 'updates') {
                        unlink(base_path('public/').$path);
                    }
                }

                $upload->extension = $extension;
                $upload->file_name = $path;
                $upload->user_id = Auth::user()->id;
                $upload->type = $type[$upload->extension];
                $upload->file_size = $size;
                $upload->processed = $processed;
                $upload->processed_at = ($processed) ? Carbon::now() : NULL;
                $upload->save();
			
                if($type[$extension] == 'video' && get_setting('disable_image_optimization') != 1){
                    try {
                        //VideoCompression::dispatch($upload);
                    } catch (\Exception $e) {
                        Log::info($e);
                    }
                }

            }
            return $upload->id;
        }
    }

    public function get_uploaded_files(Request $request)
    {
        $uploads = Upload::where('user_id', Auth::user()->id);
        if ($request->search != null) {
            $uploads->where('file_original_name', 'like', '%'.$request->search.'%');
        }
        if ($request->sort != null) {
            switch ($request->sort) {
                case 'newest':
                    $uploads->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $uploads->orderBy('created_at', 'asc');
                    break;
                case 'smallest':
                    $uploads->orderBy('file_size', 'asc');
                    break;
                case 'largest':
                    $uploads->orderBy('file_size', 'desc');
                    break;
                default:
                    $uploads->orderBy('created_at', 'desc');
                    break;
            }
        }

        $paginated = $uploads->paginate(60)->appends(request()->query());

        // Drop any broken records that don't even have a stored file path, then
        // ensure metadata is filled so the JS uploader never sees null values
        // that would render as "null.null" or "NaN undefined" in the UI.
        $cleaned = $paginated->getCollection()
            ->filter(function ($upload) {
                return !empty($upload->file_name);
            })
            ->values()
            ->transform(function ($upload) {
                if (empty($upload->file_original_name) && !empty($upload->file_name)) {
                    $upload->file_original_name = pathinfo($upload->file_name, PATHINFO_FILENAME);
                }
                if (empty($upload->extension) && !empty($upload->file_name)) {
                    $upload->extension = pathinfo($upload->file_name, PATHINFO_EXTENSION);
                }
                if (empty($upload->file_size) || $upload->file_size <= 0) {
                    $fullPath = public_path($upload->file_name);
                    if (file_exists($fullPath)) {
                        $upload->file_size = filesize($fullPath);
                    }
                }
                return $upload;
            });

        $paginated->setCollection($cleaned);

        return response()->json($paginated);
    }

    public function destroy(Request $request,$id)
    {
        $upload = Upload::findOrFail($id);

        if(auth()->user()->user_type == 'seller' && $upload->user_id != auth()->user()->id){
            flash(translate("You don't have permission for deleting this!"))->error();
            return back();
        }
        try{
            if(env('FILESYSTEM_DRIVER') == 's3'){
                Storage::disk('s3')->delete($upload->file_name);
                if (file_exists(public_path().'/'.$upload->file_name)) {
                    unlink(public_path().'/'.$upload->file_name);
                }
            }
            else{
                unlink(public_path().'/'.$upload->file_name);
            }
            $upload->delete();
            flash(translate('File deleted successfully'))->success();
        }
        catch(\Exception $e){
            $upload->delete();
            flash(translate('File deleted successfully'))->success();
        }
        return back();
    }

    public function get_preview_files(Request $request){
        $ids = explode(',', $request->ids);
        $files = Upload::whereIn('id', $ids)->get();

        // Normalize metadata for preview as well so thumbnails and labels are always sane.
        $files = $files
            ->filter(function ($upload) {
                return !empty($upload->file_name);
            })
            ->values()
            ->transform(function ($upload) {
                if (empty($upload->file_original_name) && !empty($upload->file_name)) {
                    $upload->file_original_name = pathinfo($upload->file_name, PATHINFO_FILENAME);
                }
                if (empty($upload->extension) && !empty($upload->file_name)) {
                    $upload->extension = pathinfo($upload->file_name, PATHINFO_EXTENSION);
                }
                if (empty($upload->file_size) || $upload->file_size <= 0) {
                    $fullPath = public_path($upload->file_name);
                    if (file_exists($fullPath)) {
                        $upload->file_size = filesize($fullPath);
                    }
                }
                return $upload;
            });

        return $files;
    }

    //Download project attachment
    public function attachment_download($id)
    {
        $project_attachment = Upload::find($id);
        try{
           $file_path = public_path($project_attachment->file_name);
            return Response::download($file_path);
        }catch(\Exception $e){
            flash(translate('File does not exist!'))->error();
            return back();
        }

    }
    //Download project attachment
    public function file_info(Request $request)
    {
        $file = Upload::findOrFail($request['id']);

        return (auth()->user()->user_type == 'seller')
            ? view('frontend.user.seller.uploads.info',compact('file'))
            : view('backend.uploaded_files.info',compact('file'));
    }

}
