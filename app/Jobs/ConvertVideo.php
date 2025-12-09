<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use FFMpeg\Format\Video\WebM;
class ConvertVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    
    protected $videoPath;
    protected $outputPath;

    public function __construct($videoPath, $outputPath)
    {
        $this->videoPath = $videoPath;
        $this->outputPath = $outputPath;
    }

    public function handle()
    { 
		exec("ffmpeg -i $this->videoPath -ss 00:00:00 -t 00:00:10 -async 1 $this->outputPath");
		//exec("ffmpeg -i $this->videoPath -vf 'scale=trunc(iw/10)*2:trunc(ih/10)*2' -s 640x480 -crf 28 -pix_fmt yuv420p -strict -2 $this->outputPath");  
		//exec("ffmpeg  -i $this->videoPath -vf 'scale=trunc(iw/10)*2:trunc(ih/10)*2' -ar 11025 -ab 32 -f flv -s 640x480 $this->outputPath 2>&1");  
        //$ffmpeg = FFMpeg::create(); 
        //$video = $ffmpeg->open($this->videoPath);
        //$format = new X264();
		//$format->setAudioCodec("aac");
		//$format->setAdditionalParameters(explode(' ', '-pix_fmt yuv420p -b:v 500k')); 
        //$video->save($format, $this->outputPath);
    }
}
