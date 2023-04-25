<?php

namespace App\Console\Commands;

use App\Models\Design;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class LoadPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carga los PDF al sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = Storage::disk('local')->allFiles('print');

        foreach ($files as $file) {
            $this->saveDesign($file);
        }
    }

    protected function saveDesign($file)
    {
        $name = $this->fixName($file);

        $design = $this->findDesign($name);
        $design->name = $name;
        $design->price = 50;
        $design->media = 0;
        $design->minutes = 0;
        $design->created_by  = 1;
        $design->status  = Design::STATUS_ACTIVE;
        $design->save();

        Storage::move($file, 'public/design/'.$design->id.'.pdf');
        $design->media = Storage::disk('public')->url('design/'.$design->id.'.pdf');
        $design->save();
    }

    protected function fixName($name)
    {
        $name = str_replace('print/', '', $name);
        $name = str_replace('/', ' ', $name);
        $name = str_replace('_', ' ', $name);
        $name = str_replace('.pdf', '', $name);

        return $name;
    }

    protected function findDesign($name)
    {
        $design = Design::where('name', $name)->first();

        if ($design) {
            return $design;
        }

        return new Design();
    }
}
