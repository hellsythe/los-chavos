<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\Subservice;
use App\Models\Garment;
use App\Models\Typography;
use Illuminate\Console\Command;
use Sdkconsultoria\Core\Models\Role;
use Spatie\Permission\Models\Permission;
class Start extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Llena las configuraciones basicas';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->createServices();
        $this->createGarment();
        $this->createTypography();
        $this->createRoles();
        $this->deleteRoles();
    }

    private function createServices(){

        $bordado = Service::where('name', 'Bordado')->first();
        if(!$bordado)
        {
            $bordado = new Service();
            $bordado->name = 'Bordado';
            $bordado->status = Service::STATUS_ACTIVE;
            $bordado->save();
        }

        $this->createSubservice($bordado);

        $estampado = Service::where('name', 'Estampado')->first();
        if(!$estampado)
        {
            $estampado = new Service();
            $estampado->name = 'Estampado';
            $estampado->status = Service::STATUS_ACTIVE;
            $estampado->save();
        }

        $this->createSubservice($estampado);
    }

    private function createSubservice($service)
    {
        $ponchado_existente = Subservice::where('name', 'Ponchado existente')->first();
        if(!$ponchado_existente)
        {
            $ponchado_existente = new Subservice();
            $ponchado_existente->name = 'Ponchado existente';
            $ponchado_existente->service_id = $service->id;
            $ponchado_existente->status = Subservice::STATUS_ACTIVE;
            $ponchado_existente->save();
        }

        $personalizado = Subservice::where('name', 'Personalizado')->first();
        if(!$personalizado)
        {
            $personalizado = new Subservice();
            $personalizado->name = 'Personalizado';
            $personalizado->service_id = $service->id;
            $personalizado->status = Subservice::STATUS_ACTIVE;
            $personalizado->save();
        }

        $ponchado_modificado = Subservice::where('name', 'Ponchado modificado')->first();
        if(!$ponchado_modificado)
        {
            $ponchado_modificado = new Subservice();
            $ponchado_modificado->name = 'Ponchado modificado';
            $ponchado_modificado->service_id = $service->id;
            $ponchado_modificado->status = Subservice::STATUS_ACTIVE;
            $ponchado_modificado->save();
        }

        $ponchado_nuevo = Subservice::where('name', 'Ponchado nuevo')->first();
        if(!$ponchado_nuevo)
        {
            $ponchado_nuevo = new Subservice();
            $ponchado_nuevo->name = 'Ponchado nuevo';
            $ponchado_nuevo->service_id = $service->id;
            $ponchado_nuevo->status = Subservice::STATUS_ACTIVE;
            $ponchado_nuevo->save();
        }

    }

    private function createGarment()
    {
        $model = Garment::where('name', 'Playera')->first();
        if(!$model)
        {
            $model = new Garment();
            $model->name = 'Playera';
            $model->status = Garment::STATUS_ACTIVE;
            $model->save();
        }

        $model = Garment::where('name', 'Camisa')->first();
        if(!$model)
        {
            $model = new Garment();
            $model->name = 'Camisa';
            $model->status = Garment::STATUS_ACTIVE;
            $model->save();
        }

        $model = Garment::where('name', 'Gorra/cachucha')->first();
        if(!$model)
        {
            $model = new Garment();
            $model->name = 'Gorra/cachucha';
            $model->status = Garment::STATUS_ACTIVE;
            $model->save();
        }


        $model = Garment::where('name', 'Tela/Parche')->first();
        if(!$model)
        {
            $model = new Garment();
            $model->name = 'Tela/Parche';
            $model->status = Garment::STATUS_ACTIVE;
            $model->save();
        }
    }

    private function createTypography()
    {
        $models = [
            'Letra Barbacoa',
            'Letra Model',
            'Letra Platano frito',
            'Letra Chilaquiles',
            'Letra Volovan',
            'Letra Carne Asada',
            'Letra Taquito',
            'Letra Pico de Gallo',
            'Letra Albóndiga',
            'Letra Pozole',
            'Letra Al Pastor',
            'Letra Chanfaina',
            'Letra Carnitas',
            'Letra Mojarra',
            'Letra Garnacha',
            'Letra Picada',
            'Letra Empanada',
            'Letra Mollete',
            'Letra Tlayuda',
            'Letra Guacamole',
            'Letra Enfrijolada',
            'Letra Mondongo',
        ];

        foreach ($models as $model) {
            $this->createModelByName(Typography::class, $model);
        }
    }

    private function createModelByName($class, $name)
    {
        $model = $class::where('name', $name)->first();
        if(!$model)
        {
            $model = new $class();
            $model->name = $name;
            $model->status = $class::STATUS_ACTIVE;
            $model->save();
        }
    }

    private function deleteRoles()
    {
        Role::where('name', 'user')->delete();
        Role::where('name', 'admin')->delete();
    }

    private function createRoles()
    {
        $role = Role::firstOrCreate(['name' => 'Punto de venta', 'status' => Role::STATUS_ACTIVE]);
        $role = Role::firstOrCreate(['name' => 'Bordador', 'status' => Role::STATUS_ACTIVE]);
    }

    private function createPermissions()
    {
        $permission = Permission::create(['name' => 'Corte de caja']);
    }
}
