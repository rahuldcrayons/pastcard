<?php

namespace App\Jobs;

use FFMpeg\FFMpeg;
use Carbon\Carbon;
use App\Models\Upload;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Video\VideoFilters;
use Illuminate\Support\Facades\Storage;
use Log;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class VideoCompression implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $video;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Upload $upload)
    {
        $this->video = $upload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $old_file = $this->video->file_name;
            // create a video format...
            $converted_name = $this->getCleanFileName($old_file);

            // open the uploaded video from the right disk...
            FFMpeg::fromDisk('public')
                ->open($old_file)
                ->export()
                ->resize(640, 480)
                ->toDisk('public')
                ->inFormat(new X264)
                ->save($converted_name);

            $size = Storage::disk('public')->size($converted_name);
            // update the database so we know the convertion is done!
            $this->video->update([
                'file_name' => $converted_name,
                'file_size' => $size,
                'processed' => true,
                'processed_at' => Carbon::now()
            ]);
            $size = Storage::disk('public')->delete($old_file);

        } catch (EncodingException $e) {
            Log::info($e->getCommand());
            Log::info($e->getErrorOutput());
        }
    }

    private function getCleanFileName($filename) {
        return 'uploads/all/' . bin2hex(random_bytes(30)) . '.mp4';
    }
}
